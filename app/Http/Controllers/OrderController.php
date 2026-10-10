<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Inventory_Item;
use App\Models\Option_Values;
use App\Models\Order_Item;
use App\Models\Order_Item_Options;
use App\Models\Kitchen_Order_Item;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cashier_id' => ['nullable', 'exists:users,id'],
            'order_type' => ['required', 'string'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => [
                'required',
                'integer',
                'exists:inventory_items,id',
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string'],
            'items.*.options' => ['nullable', 'array'],
            'items.*.options.*' => [
                'integer',
                'exists:option_values,id',
            ],

            'discount_type' => [
                'nullable',
                'in:None,Senior,PWD',
            ],
            'payment_method' => [
                'required',
                'in:Cash,GCash',
            ],
            'amount_tendered' => ['nullable', 'numeric', 'min:0'],
            'reference_number' => ['required_if:payment_method,GCash', 'nullable', 'string', 'max:50'],
        ]);

        /*
     * Kitchen menu items that should deduct from prepared-food stock.
     * Left side  = sellable POS item name (inventory_items.name for the menu item).
     * Right side = matching inventory item name in Kitchen Area.
     *
     * Drinks (Coffee, Non Coffee, Frappe, Smoothies, Boba, Oreo, Fizzy,
     * Buy 1 Take 1, Soda Fruit Jelly, Fruit Juice Pitcher) are NOT here
     * on purpose. Their sale is recorded in order_items only.
     */
        $kitchenStockMap = [
            // Rice Meals
            'Porkchop with Sauce'                           => 'Pork Chop',
            'Chicken Teriyaki'                              => 'C-Teriyaki',
            'Chicken Ala King'                              => 'Chicken Ala King',
            '2 pcs Burger Steak'                            => 'Burger Patty',
            'Tocino with Egg'                               => 'Pork/Chicken Tocino',
            'Chorizo with Egg'                              => 'Chicken Franks',
            'Cornbeef with Egg'                             => 'Corn Beef',
            'Fish Fillet (Rice Meal)'                       => 'Fish Fillet',
            'Deep Fried Bangus'                             => 'Bangus',
            'Ham and Egg'                                   => 'Pork/Chicken Ham',
            'Chicken Hotdog with Egg'                       => 'Chicken Franks',
            'Pork Sisig (Rice Meal)'                        => 'Pork Sisig',
            'Chicken Sisig (Rice Meal)'                     => 'Chicken Sisig',

            // Rice Toppings
            'Fried Siomai with Egg'                         => 'Fried Siomai',
            'Pork Binagoongan'                              => 'Binagoongan',
            'Chicken Adobo'                                 => 'Chicken Adobo',
            'Pork Adobo'                                    => 'Pork Adobo',

            // Snack Meals
            'Clubhouse Sandwich'                            => 'Pork/Chicken Ham',
            'Beef Cheese Burger with Fries'                 => 'Burger Patty',
            'Double Patty Chicken Cheese Burger with Fries' => 'Burger Patty',
            'Chicken Hotdog Sandwich'                       => 'Chicken Franks',
            'Chicken Carbonara with Coke'                   => 'Chicken Carbonara',
            'Ham Carbonara with Coke'                       => 'Chicken Carbonara',
            'Bihon Guisado'                                 => 'Bihon',
            'Bake Mac'                                      => 'Baked Mac',
            'Beef Cheese Nachos'                            => 'Nachos Chips/Beef',
            'Mozzarella Cheese Stick'                       => 'Mozzarella',
            'French Fries'                                  => 'Fries',
        ];

        return DB::transaction(function () use ($request, $validated, $kitchenStockMap) {

            /*
         * Load the selected menu items and their option groups.
         */
            $menuItemIds = collect($validated['items'])
                ->pluck('inventory_item_id')
                ->unique()
                ->values();

            $menuItems = Inventory_Item::query()
                ->whereIn('id', $menuItemIds)
                ->where('is_active', true)
                ->with(['optionGroups.optionValues'])
                ->get()
                ->keyBy('id');

            /*
         * Validate that the submitted option values belong to the
         * option groups assigned to the selected menu item.
         */
            foreach ($validated['items'] as $itemData) {
                $menuItem = $menuItems->get($itemData['inventory_item_id']);

                if (! $menuItem) {
                    throw ValidationException::withMessages([
                        'items' => 'One of the selected menu items is unavailable.',
                    ]);
                }

                $selectedOptionIds = collect($itemData['options'] ?? [])
                    ->map(fn($id) => (int) $id)
                    ->values();

                $allowedOptionIds = $menuItem->optionGroups
                    ->flatMap(fn($optionGroup) => $optionGroup->optionValues->pluck('id'))
                    ->map(fn($id) => (int) $id)
                    ->values();

                if ($selectedOptionIds->diff($allowedOptionIds)->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => 'An invalid option was selected for a menu item.',
                    ]);
                }

                $selectedOptions = Option_Values::query()
                    ->whereIn('id', $selectedOptionIds)
                    ->get();

                $duplicateGroup = $selectedOptions
                    ->groupBy('option_group_id')
                    ->contains(fn($options) => $options->count() > 1);

                if ($duplicateGroup) {
                    throw ValidationException::withMessages([
                        'items' => 'Please select no more than one option from each option group.',
                    ]);
                }
            }

            /*
         * Get the Kitchen Area location once.
         */
            $kitchenLocation = \App\Models\Inventory_Locations::query()
                ->where('name', 'Kitchen Area')
                ->first();

            if (! $kitchenLocation) {
                throw ValidationException::withMessages([
                    'items' => 'Kitchen Area was not found in inventory locations.',
                ]);
            }

            /*
         * Cache the Kitchen Area stock items by name so we don't hit the DB
         * repeatedly. Only active items in Kitchen Area are considered.
         */
            $kitchenStockByName = Inventory_Item::query()
                ->where('inventory_location_id', $kitchenLocation->id)
                ->where('is_active', true)
                ->pluck('id', 'name');

            /*
         * Calculate the order subtotal using inventory_items.price
         * plus any selected option price adjustments.
         */
            $subtotal = 0;

            foreach ($validated['items'] as $itemData) {
                $menuItem = $menuItems->get($itemData['inventory_item_id']);
                $quantity = (int) $itemData['quantity'];

                $optionTotal = Option_Values::query()
                    ->whereIn('id', $itemData['options'] ?? [])
                    ->sum('price_adjustment');

                $subtotal += ((float) $menuItem->price + (float) $optionTotal) * $quantity;
            }

            $discountType = $validated['discount_type'] ?? 'None';
            $discountAmount = 0;

            if (in_array($discountType, ['Senior', 'PWD'], true)) {
                $discountAmount = $subtotal * 0.20;
            }

            $totalAmount = max(0, $subtotal - $discountAmount);

            /*
         * Queue number = today's running number, restarts every day.
         */
            $queueNumber = ((int) \App\Models\Order::whereDate('queue_date', today())
                ->lockForUpdate()
                ->max('queue_number')) + 1;

            $order = \App\Models\Order::create([
                'cashier_id'      => $validated['cashier_id'] ?? auth()->id(),
                'order_number'    => 'ORD-' . now()->format('Ymd') . '-' .
                    str_pad($queueNumber, 4, '0', STR_PAD_LEFT),
                'queue_date'      => today(),
                'queue_number'    => $queueNumber,
                'order_type'      => $validated['order_type'],
                'status'          => 'Pending',
                'subtotal'        => $subtotal,
                'discount_type'   => $discountType,
                'discount_amount' => $discountAmount,
                'total_amount'    => $totalAmount,
                'notes'           => $validated['notes'] ?? null,
            ]);

            $stockUpdates = [];

            /*
         * Save order lines, options, kitchen tickets.
         * Deduct Kitchen Area prepared-food stock only.
         */
            foreach ($validated['items'] as $itemData) {
                $inventoryItem = Inventory_Item::findOrFail($itemData['inventory_item_id']);
                $quantity = (int) $itemData['quantity'];

                $selectedOptions = Option_Values::query()
                    ->whereIn('id', $itemData['options'] ?? [])
                    ->get();

                $optionTotal = (float) $selectedOptions->sum('price_adjustment');
                $unitPrice   = (float) $inventoryItem->price + $optionTotal;
                $lineTotal   = $unitPrice * $quantity;

                $orderItem = Order_Item::create([
                    'order_id'          => $order->id,
                    'inventory_item_id' => $inventoryItem->id,
                    'quantity'          => $quantity,
                    'unit_price'        => $unitPrice,
                    'subtotal'          => $lineTotal,
                    'notes'             => $itemData['notes'] ?? null,
                ]);

                foreach ($selectedOptions as $optionValue) {
                    DB::table('order_item_options')->insert([
                        'order_item_id'   => $orderItem->id,
                        'option_value_id' => $optionValue->id,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }

                /*
             * Stock-out: only prepped food items in the mapping are deducted.
             * Drinks (Coffee, Non Coffee, Frappe, etc.) are NOT in the map
             * and therefore will not create a stock_out row.
             */
                $stockItemName = $kitchenStockMap[$inventoryItem->name] ?? null;

                if ($stockItemName !== null) {
                    $stockItemId = $kitchenStockByName[$stockItemName] ?? null;

                    if (! $stockItemId) {
                        throw ValidationException::withMessages([
                            'items' => "Kitchen stock item '{$stockItemName}' is missing or inactive.",
                        ]);
                    }

                    $stockItem = Inventory_Item::lockForUpdate()->find($stockItemId);

                    $totalStockIn = (float) \App\Models\StockIn::query()
                        ->where('inventory_item_id', $stockItem->id)
                        ->sum('quantity');

                    $totalStockOut = (float) \App\Models\StockOut::query()
                        ->where('inventory_item_id', $stockItem->id)
                        ->sum('quantity');

                    $availableStock = $totalStockIn - $totalStockOut;

                    if ($availableStock < $quantity) {
                        throw ValidationException::withMessages([
                            'items' => "Not enough prepared stock for {$inventoryItem->name}. Available: {$availableStock}. Requested: {$quantity}.",
                        ]);
                    }

                    \App\Models\StockOut::create([
                        'inventory_item_id' => $stockItem->id,
                        'quantity'          => $quantity,
                        'recorded_by'       => $validated['cashier_id'] ?? auth()->id(),
                        'reason'            => 'Sold via POS: ' . $order->order_number,
                        'recorded_at'       => now(),
                        'remarks'           => 'Order item: ' . $inventoryItem->name,
                    ]);

                    $remainingStock = $availableStock - $quantity;

                    $stockUpdates[] = [
                        'menu_item_id' => $inventoryItem->id,
                        'stock'        => $remainingStock,
                    ];
                }

                /*
             * Kitchen ticket for this order line.
             */
                Kitchen_Order_Item::create([
                    'order_id'      => $order->id,
                    'order_item_id' => $orderItem->id,
                    'status'        => 'Pending',
                ]);
            }

            /*
         * Record payment after all order items and stock-outs pass validation.
         */
            Payment::create([
                'order_id'         => $order->id,
                'payment_method'   => $validated['payment_method'],
                'amount'           => $totalAmount,
                'received_by'      => auth()->id(),
                'amount_received'  => $validated['amount_tendered'] ?? $totalAmount,
                'change_amount'    => max(
                    0,
                    (float) ($validated['amount_tendered'] ?? $totalAmount) - $totalAmount
                ),
                'reference_number' => $validated['payment_method'] === 'GCash'
                    ? ($validated['reference_number'] ?? null)
                    : null,
                'paid_at'          => now(),
            ]);

            return response()->json([
                'message'       => 'Order placed successfully.',
                'order_id'      => $order->id,
                'order'         => [
                    'id'           => $order->id,
                    'order_number' => $order->order_number,
                ],
                'queue_number'  => $queueNumber,
                'stock_updates' => $stockUpdates,
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Complete a ready order.
     */
    public function completeOrder(Order $order)
    {
        if ($order->status !== 'Ready') {
            return redirect('/kitchen')
                ->with('error', 'Only ready orders can be completed.');
        }

        $order->update([
            'status' => 'Completed',
            'completed_at' => now(),
        ]);

        return redirect('/kitchen')
            ->with('success', 'Order completed successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
