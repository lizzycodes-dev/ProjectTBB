<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Item;
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
        $cashier = auth()->user();

        return DB::transaction(function () use ($request, $cashier) {
            $validated = $request->validate([
                'cashier_id' => 'nullable|exists:users,id',
                'order_type' => 'required|string|max:50',
                'items' => 'required|array|min:1',

                'items.*.menu_item_id' => [
                    'required',
                    Rule::exists('inventory_items', 'id')
                        ->where('is_active', true)
                        ->where('is_sellable', true),
                ],
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.notes' => 'nullable|string',

                'items.*.options' => 'nullable|array',
                'items.*.options.*' => [
                    'integer',
                    'distinct',
                    'exists:option_values,id',
                ],

                'discount_type' => 'required|in:None,Senior/PWD',

                'payment_method' => 'required|in:Cash,GCash',
                'amount_received' => 'required_if:payment_method,Cash|numeric|min:0',
                'reference_number' => [
                    'required_if:payment_method,GCash',
                    'nullable',
                    'digits:4',
                ],
                'proof_path' => 'nullable|string|max:255',
            ]);

            /*
             * Validate that selected options belong to the selected menu item,
             * and that no more than one value is selected from each option group.
             */
            foreach ($validated['items'] as $itemIndex => $item) {
                $menuItem = Item::with('optionGroups.optionValues')
                    ->findOrFail($item['menu_item_id']);

                $allowedOptions = $menuItem->optionGroups
                    ->flatMap(function ($group) {
                        return $group->optionValues
                            ->where('is_active', true)
                            ->pluck('id');
                    });

                $selectedOptions = $item['options'] ?? [];

                foreach ($selectedOptions as $optionId) {
                    if (! $allowedOptions->contains(
                        fn($allowedId) => (int) $allowedId === (int) $optionId
                    )) {
                        throw ValidationException::withMessages([
                            "items.$itemIndex.options" =>
                            "One of the selected options is invalid for {$menuItem->name}.",
                        ]);
                    }
                }

                $selectedGroups = [];

                foreach ($selectedOptions as $optionId) {
                    $option = Option_Values::findOrFail($optionId);
                    $groupId = (int) $option->option_group_id;

                    if (in_array($groupId, $selectedGroups, true)) {
                        throw ValidationException::withMessages([
                            "items.$itemIndex.options" =>
                            "Choose only one value for each option group on {$menuItem->name}.",
                        ]);
                    }

                    $selectedGroups[] = $groupId;
                }
            }

            /*
             * Calculate order subtotal, including option price adjustments.
             */
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $menuItem = Item::findOrFail($item['menu_item_id']);
                $itemPrice = (float) $menuItem->base_price;

                foreach ($item['options'] ?? [] as $optionId) {
                    $option = Option_Values::findOrFail($optionId);
                    $itemPrice += (float) $option->price_adjustment;
                }

                $subtotal += $itemPrice * $item['quantity'];
            }

            /*
             * Calculate discount and total.
             */
            $discountAmount = 0;

            if ($validated['discount_type'] === 'Senior/PWD') {
                $discountAmount = round($subtotal * 0.20, 2);
            }

            $totalAmount = $subtotal - $discountAmount;

            /*
             * Validate cash payment.
             * For GCash, amount received is set to the order total.
             */
            if (
                $validated['payment_method'] === 'Cash' &&
                (float) $validated['amount_received'] < $totalAmount
            ) {
                throw ValidationException::withMessages([
                    'amount_received' => 'Payment amount is insufficient.',
                ]);
            }

            if ($validated['payment_method'] === 'GCash') {
                $validated['amount_received'] = $totalAmount;
            }

            /*
             * Generate order number.
             */
            $orderNumber = 'ORD-' . str_pad(
                Order::count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            /*
             * Create the order.
             */
            $order = Order::create([
                'cashier_id' => $cashier->id,
                'order_number' => $orderNumber,
                'order_type' => $validated['order_type'],
                'status' => 'Pending',
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_type' => $validated['discount_type'],
                'total_amount' => $totalAmount,
                'ordered_at' => now(),
                'completed_at' => null,
            ]);

            /*
             * Map sellable menu names to their corresponding prepared-food
             * inventory names in Kitchen Area.
             *
             * Menu items not included here will not generate stock-out rows
             * until their stock mapping and deduction quantity are decided.
             */
            $kitchenStockMap = [
                'Pork Sisig' => 'Pork Sisig (Prepped)',
                'Chicken Sisig' => 'Chicken Sisig (Prepped)',
                'Porkchop with Sauce' => 'Pork Chop (Prepped)',
                'Chicken Teriyaki' => 'C-Teriyaki',
                'Fish Fillet' => 'Fish Fillet (Prepped)',
                'Deep Fried Bangus' => 'Bangus (Prepped)',
                'Chicken Adobo' => 'Chicken Adobo (Prepped)',
                'Pork Adobo' => 'Pork Adobo (Prepped)',
                'Chicken Carbonara with Coke' => 'Chicken Carbonara',
                'Bihon Guisado' => 'Bihon (Prepped)',
                'Bake Mac' => 'Baked Mac (Prepped)',
                'Mozzarella Cheese Stick' => 'Mozzarella (Prepped)',
                'French Fries' => 'Fries (Prepped)',
            ];

            /*
             * Create order items, record stock-outs for mapped prepared foods,
             * save chosen options, and create kitchen tickets.
             */
            foreach ($validated['items'] as $item) {
                $menuItem = Item::findOrFail($item['menu_item_id']);

                $itemPrice = (float) $menuItem->base_price;

                foreach ($item['options'] ?? [] as $optionId) {
                    $option = Option_Values::findOrFail($optionId);
                    $itemPrice += (float) $option->price_adjustment;
                }

                $quantity = (int) $item['quantity'];

                $orderItem = Order_Item::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'unit_price' => $itemPrice,
                    'subtotal' => $itemPrice * $quantity,
                    'notes' => $item['notes'] ?? null,
                ]);

                /*
                 * Record stock-out for mapped Kitchen Area prepared-food items.
                 */
                $menuName = $menuItem->name;
                $stockItemName = $kitchenStockMap[$menuName] ?? null;

                if ($stockItemName !== null) {
                    $kitchenLocation = DB::table('inventory_locations')
                        ->where('name', 'Kitchen Area')
                        ->first();

                    if (! $kitchenLocation) {
                        throw ValidationException::withMessages([
                            'items' => 'Kitchen Area location was not found.',
                        ]);
                    }

                    $stockItem = DB::table('inventory_items')
                        ->where('name', $stockItemName)
                        ->where('inventory_location_id', $kitchenLocation->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $stockItem) {
                        throw ValidationException::withMessages([
                            'items' =>
                            "Kitchen stock item '{$stockItemName}' was not found.",
                        ]);
                    }

                    /*
                     * Current available quantity = total stock-in - total stock-out.
                     */
                    $stockInTotal = DB::table('stock_ins')
                        ->where('inventory_item_id', $stockItem->id)
                        ->sum('quantity');

                    $stockOutTotal = DB::table('stock_outs')
                        ->where('inventory_item_id', $stockItem->id)
                        ->sum('quantity');

                    $availableQuantity =
                        (float) $stockInTotal - (float) $stockOutTotal;

                    if ($availableQuantity < $quantity) {
                        throw ValidationException::withMessages([
                            'items' =>
                            "Not enough {$stockItemName} in Kitchen Area. "
                                . "Available: {$availableQuantity}.",
                        ]);
                    }

                    DB::table('stock_outs')->insert([
                        'inventory_item_id' => $stockItem->id,
                        'quantity' => $quantity,
                        'recorded_by' => $cashier->id,
                        'reason' => "Sold via POS: {$order->order_number}",
                        'recorded_at' => now(),
                        'remarks' => "Order item: {$menuName}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                /*
                 * Save selected option values for this order item.
                 */
                foreach ($item['options'] ?? [] as $optionId) {
                    $option = Option_Values::findOrFail($optionId);

                    Order_Item_Options::create([
                        'order_item_id' => $orderItem->id,
                        'option_value_id' => $option->id,
                        'price_adjustment' => $option->price_adjustment,
                    ]);
                }

                /*
                 * Create kitchen ticket for the order item.
                 */
                Kitchen_Order_Item::create([
                    'order_item_id' => $orderItem->id,
                    'prepared_by' => null,
                    'status' => 'Pending',
                    'started_at' => null,
                    'completed_at' => null,
                ]);
            }

            /*
             * Record payment.
             */
            $amountReceived = (float) $validated['amount_received'];
            $changeAmount = $amountReceived - $totalAmount;

            Payment::create([
                'order_id' => $order->id,
                'received_by' => $cashier->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $totalAmount,
                'amount_received' => $amountReceived,
                'change_amount' => $changeAmount,
                'reference_number' => $validated['reference_number'] ?? null,
                'proof_path' => $validated['proof_path'] ?? null,
                'paid_at' => now(),
            ]);

            /*
             * Daily queue number: #101 for the first order of the day,
             * then #102, #103, and so on.
             */
            $queueNumber = 100 + Order::whereDate(
                'ordered_at',
                now()->toDateString()
            )
                ->where('id', '<=', $order->id)
                ->count();

            return response()->json([
                'message' => 'Order created successfully.',
                'order' => $order,
                'queue_number' => $queueNumber,
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
