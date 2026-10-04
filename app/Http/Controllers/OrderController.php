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
        ]);

        /*
     * Kitchen menu items that should deduct from prepared-food stock.
     * The left side is the sellable POS item name.
     * The right side is the matching inventory item name in Kitchen Area.
     *
     * Make sure these names match your inventory_items table exactly.
     */
        $kitchenStockMap = [
            'Pork Sisig' => 'Pork Sisig',
            'Chicken Sisig' => 'Chicken Sisig',
            'Pork Sisig NS' => 'Pork Sisig NS',
            'Chicken Sisig NS' => 'Chicken Sisig NS',
            'Binagoongan' => 'Binagoongan',
            'Chicken Teriyaki' => 'C-Teriyaki',
            'Fish Fillet' => 'Fish Fillet',
            'Deep Fried Bangus' => 'Bangus',
            'Chicken Adobo' => 'Chicken Adobo',
            'Pork Adobo' => 'Pork Adobo',
            'Chicken Franks' => 'Chicken Franks',
            'Pork/Chicken Tocino' => 'Pork/Chicken Tocino',
            'Corn Beef' => 'Corn Beef',
            'Pork/Chicken Ham' => 'Pork/Chicken Ham',
            'Egg' => 'Egg',
            'Baked Mac' => 'Baked Mac',
            'Mozzarella Cheese Stick' => 'Mozzarella',
            'French Fries' => 'Fries',
            'Burger' => 'Burger Patty',
            'Porkchop with Sauce' => 'Pork Chop',
            'Bihon Guisado' => 'Bihon',
            'Chicken Carbonara with Coke' => 'Chicken Carbonara',
            'Nachos' => 'Nachos Chips/Beef',
        ];

        return \Illuminate\Support\Facades\DB::transaction(function () use (
            $request,
            $validated,
            $kitchenStockMap
        ) {
            /*
         * Load the selected menu items and their option groups.
         * optionGroups is a hasMany relationship to the pivot model,
         * then optionGroup is the related option group.
         */
            $menuItemIds = collect($validated['items'])
                ->pluck('inventory_item_id')
                ->unique()
                ->values();

            $menuItems = \App\Models\Inventory_Item::query()
                ->whereIn('id', $menuItemIds)
                ->where('is_active', true)
                ->with([
                    'optionGroups.optionGroup.optionValues',
                ])
                ->get()
                ->keyBy('id');

            /*
         * Validate that the submitted option values belong to the
         * option groups assigned to the selected menu item.
         */
            foreach ($validated['items'] as $itemData) {
                $menuItem = $menuItems->get($itemData['inventory_item_id']);

                if (!$menuItem) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => 'One of the selected menu items is unavailable.',
                    ]);
                }

                $selectedOptionIds = collect($itemData['options'] ?? [])
                    ->map(fn($id) => (int) $id)
                    ->values();

                $allowedOptionIds = $menuItem->optionGroups
                    ->flatMap(function ($itemOptionGroup) {
                        return $itemOptionGroup->optionGroup?->optionValues
                            ->pluck('id') ?? collect();
                    })
                    ->map(fn($id) => (int) $id)
                    ->values();

                if ($selectedOptionIds->diff($allowedOptionIds)->isNotEmpty()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => 'An invalid option was selected for a menu item.',
                    ]);
                }

                /*
             * Prevent selecting more than one option from the same group.
             */
                $selectedOptions = \App\Models\Option_Values::query()
                    ->whereIn('id', $selectedOptionIds)
                    ->get();

                $duplicateGroup = $selectedOptions
                    ->groupBy('option_group_id')
                    ->contains(fn($options) => $options->count() > 1);

                if ($duplicateGroup) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => 'Please select no more than one option from each option group.',
                    ]);
                }
            }

            /*
         * Get the Kitchen Area location once.
         * No Bar Area ingredient stock is deducted by this order method.
         */
            $kitchenLocation = \App\Models\Inventory_Locations::query()
                ->where('name', 'Kitchen Area')
                ->first();

            if (!$kitchenLocation) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'items' => 'Kitchen Area was not found in inventory locations.',
                ]);
            }

            /*
         * Calculate the order subtotal using the current inventory_items.price
         * field, plus any selected option price adjustments.
         */
            $subtotal = 0;

            foreach ($validated['items'] as $itemData) {
                $menuItem = $menuItems->get($itemData['inventory_item_id']);
                $quantity = (int) $itemData['quantity'];

                $optionTotal = \App\Models\Option_Values::query()
                    ->whereIn('id', $itemData['options'] ?? [])
                    ->sum('price_adjustment');

                $subtotal += (
                    (float) $menuItem->price + (float) $optionTotal
                ) * $quantity;
            }

            $discountType = $validated['discount_type'] ?? 'None';
            $discountAmount = 0;

            /*
         * Keep the discount calculation consistent with your current policy.
         * This applies 20% for Senior/PWD when selected.
         */
            if (in_array($discountType, ['Senior', 'PWD'], true)) {
                $discountAmount = $subtotal * 0.20;
            }

            $totalAmount = max(0, $subtotal - $discountAmount);

            /*
         * Create the order.
         * If your orders table uses different column names, keep those
         * existing names from your current Order model/migration.
         */
            $order = \App\Models\Order::create([
                'cashier_id' => $validated['cashier_id'] ?? auth()->id(),
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' .
                    str_pad(
                        ((int) \App\Models\Order::whereDate('created_at', today())->count()) + 1,
                        4,
                        '0',
                        STR_PAD_LEFT
                    ),
                'order_type' => $validated['order_type'],
                'status' => 'Pending',
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
            ]);
            $stockUpdates = [];

            /*
         * Save order lines and their selected options.
         * Deduct only mapped Kitchen Area prepared-food stock.
         */
            foreach ($validated['items'] as $itemData) {
                $inventoryItem = Inventory_Item::findOrFail(
                    $itemData['inventory_item_id']
                );
                $quantity = (int) $itemData['quantity'];

                $selectedOptions = \App\Models\Option_Values::query()
                    ->whereIn('id', $itemData['options'] ?? [])
                    ->get();

                $optionTotal = (float) $selectedOptions->sum('price_adjustment');
                $unitPrice = (float) $inventoryItem->price + $optionTotal;
                $lineTotal = $unitPrice * $quantity;

                $orderItem = \App\Models\Order_Item::create([
                    'order_id' => $order->id,
                    'inventory_item_id' => $inventoryItem->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineTotal,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                /*
             * Save option selections if your order_item_options table exists.
             * Remove this block if your project stores options differently.
             */
                foreach ($selectedOptions as $optionValue) {
                    \Illuminate\Support\Facades\DB::table('order_item_options')->insert([
                        'order_item_id' => $orderItem->id,
                        'option_value_id' => $optionValue->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                /*
             * Stock-out: only food items in the mapping are deducted.
             * The matching inventory record must be active and in Kitchen Area.
             */
                $stockItemName = $kitchenStockMap[$menuItem->name] ?? null;

                if ($stockItemName !== null) {
                    $stockItem = \App\Models\Inventory_Item::query()
                        ->where('name', $stockItemName)
                        ->where('inventory_location_id', $kitchenLocation->id)
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    if (!$stockItem) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'items' => "Kitchen stock item '{$stockItemName}' was not found.",
                        ]);
                    }

                    $totalStockIn = (float) \App\Models\StockIn::query()
                        ->where('inventory_item_id', $stockItem->id)
                        ->sum('quantity');

                    $totalStockOut = (float) \App\Models\StockOut::query()
                        ->where('inventory_item_id', $stockItem->id)
                        ->sum('quantity');

                    $availableStock = $totalStockIn - $totalStockOut;

                    if ($availableStock < $quantity) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'items' => "Not enough prepared stock for {$menuItem->name}. Available: {$availableStock}. Requested: {$quantity}.",
                        ]);
                    }

                    \App\Models\StockOut::create([
                        'inventory_item_id' => $stockItem->id,
                        'quantity' => $quantity,
                        'recorded_by' => $validated['cashier_id'] ?? auth()->id(),
                        'reason' => 'Sold via POS: ' . $order->order_number,
                        'recorded_at' => now(),
                        'remarks' => 'Order item: ' . $menuItem->name,
                    ]);

                    $remainingStock = $availableStock - $quantity;

                    $stockUpdates[] = [
                        'menu_item_id' => $menuItem->id,
                        'stock' => $remainingStock,
                    ];
                }

                /*
             * Create the kitchen ticket for this order line.
             * Keep these field names aligned with your Kitchen_Order_Item model.
             */
                \App\Models\Kitchen_Order_Item::create([
                    'order_id' => $order->id,
                    'order_item_id' => $orderItem->id,
                    'status' => 'Pending',
                ]);
            }

            /*
         * Record payment after all order items and stock-outs pass validation.
         * Since this is inside the transaction, any error rolls back the order,
         * order items, kitchen tickets, payment, and stock-outs together.
         */
            \App\Models\Payment::create([
                'order_id' => $order->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $totalAmount,
                'received_by' => auth()->id(),
                'amount_tendered' => $validated['amount_tendered'] ?? $totalAmount,
                'change' => max(
                    0,
                    (float) ($validated['amount_tendered'] ?? $totalAmount) - $totalAmount
                ),
                'status' => 'Paid',
                'paid_at' => now(),
            ]);

            return response()->json([
                'message' => 'Order placed successfully.',
                'order_id' => $order->id,
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
