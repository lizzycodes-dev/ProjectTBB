<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Menu_Items;
use App\Models\Option_Values;
use App\Models\Order_Item;
use App\Models\Order_Item_Options;
use App\Models\Kitchen_Order_Item;
use App\Models\Payment;
use App\Services\InventoryService;
use App\Models\User;
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request, InventoryService $inventoryService)
    {
        $cashier = auth()->user();

        return DB::transaction(function () use ($request, $inventoryService, $cashier) {
            $validated = $request->validate([
                'cashier_id' => 'nullable|exists:users,id',
                'order_type' => 'required|string|max:50',
                'items' => 'required|array|min:1',

                'items.*.menu_item_id' => [
                    'required',
                    Rule::exists('menu_items', 'id')->where('is_active', true),
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
            $subtotal = 0;
            // Confirm each selected option belongs to the menu item's option groups.
            foreach ($validated['items'] as $itemIndex => $item) {
                $menuItem = Menu_Items::with('optionGroups.optionValues')
                    ->findOrFail($item['menu_item_id']);

                $allowedOptions = $menuItem->optionGroups
                    ->flatMap(function ($group) {
                        return $group->optionValues
                            ->where('is_active', true)
                            ->pluck('id');
                    });

                $selectedOptions = $item['options'] ?? [];

                foreach ($selectedOptions as $optionId) {
                    if (!$allowedOptions->contains(
                        fn($allowedId) => (int) $allowedId === (int) $optionId
                    )) {
                        throw ValidationException::withMessages([
                            "items.$itemIndex.options" =>
                            "One of the selected options is invalid for {$menuItem->name}.",
                        ]);
                    }
                }

                // Prevent selecting more than one value from the same option group.
                $selectedGroups = [];

                foreach ($selectedOptions as $optionId) {
                    $option = Option_Values::findOrFail($optionId);
                    $groupId = $option->option_group_id;

                    if (in_array($groupId, $selectedGroups, true)) {
                        throw ValidationException::withMessages([
                            "items.$itemIndex.options" =>
                            "Choose only one value for each option group on {$menuItem->name}.",
                        ]);
                    }

                    $selectedGroups[] = $groupId;
                }
            }

            foreach ($validated['items'] as $item) {

                $menuItem = Menu_Items::findOrFail(
                    $item['menu_item_id']
                );

                $itemPrice = $menuItem->base_price;

                if (!empty($item['options'])) {
                    foreach ($item['options'] as $optionId) {

                        $option = Option_Values::findOrFail($optionId);

                        $itemPrice += $option->price_adjustment;
                    }
                }

                $subtotal += $itemPrice * $item['quantity'];
            }

            $discountAmount = 0;

            if ($validated['discount_type'] === 'Senior/PWD') {

                $discountAmount = round($subtotal * 0.20, 2);
            }

            $totalAmount = $subtotal - $discountAmount;
            if (
                $validated['payment_method'] === 'Cash' &&
                $validated['amount_received'] < $totalAmount
            ) {
                return response()->json([
                    'message' => 'Payment amount is insufficient.',
                ], 422);
            }

            if ($validated['payment_method'] === 'GCash') {
                $validated['amount_received'] = $totalAmount;
            }

            $validated['subtotal'] = $subtotal;
            $validated['discount_amount'] = $discountAmount;
            $validated['total_amount'] = $totalAmount;

            $validated['order_number'] = 'ORD-' . str_pad(
                Order::count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            $validated['status'] = 'Pending';
            $validated['ordered_at'] = now();
            $validated['completed_at'] = null;

            $order = Order::create([
                'cashier_id' => $cashier->id,
                'order_number' => $validated['order_number'],
                'order_type' => $validated['order_type'],
                'status' => $validated['status'],
                'subtotal' => $validated['subtotal'],
                'discount_amount' => $validated['discount_amount'],
                'discount_type' => $validated['discount_type'],
                'total_amount' => $validated['total_amount'],
                'ordered_at' => $validated['ordered_at'],
                'completed_at' => $validated['completed_at'],
            ]);
            foreach ($validated['items'] as $item) {

                $menuItem = Menu_Items::findOrFail(
                    $item['menu_item_id']
                );

                $itemPrice = $menuItem->base_price;

                if (!empty($item['options'])) {
                    foreach ($item['options'] as $optionId) {

                        $option = Option_Values::findOrFail($optionId);

                        $itemPrice += $option->price_adjustment;
                    }
                }

                $orderItem = Order_Item::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $itemPrice,
                    'subtotal' => $itemPrice * $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);

                if (!empty($item['options'])) {

                    foreach ($item['options'] as $optionId) {

                        $option = Option_Values::findOrFail($optionId);

                        Order_Item_Options::create([
                            'order_item_id' => $orderItem->id,
                            'option_value_id' => $option->id,
                            'price_adjustment' => $option->price_adjustment,
                        ]);
                    }
                }
                Kitchen_Order_Item::create([
                    'order_item_id' => $orderItem->id,
                    'prepared_by' => null,
                    'status' => 'Pending',
                    'started_at' => null,
                    'completed_at' => null,
                ]);
            }
            $changeAmount = $validated['amount_received'] - $totalAmount;

            Payment::create([
                'order_id' => $order->id,
                'received_by' => $cashier->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $totalAmount,
                'amount_received' => $validated['amount_received'],
                'change_amount' => $changeAmount,
                'reference_number' => $validated['reference_number'] ?? null,
                'proof_path' => $validated['proof_path'] ?? null,
                'paid_at' => now(),
            ]);

            $inventoryService->deductForOrder(
                $order->load('orderItems.menuItem.recipeItems', 'orderItems.options'),
                $cashier
            );

            // Daily queue number: #101 for the first order of the day, then 102, 103...
            $queueNumber = 100 + Order::whereDate('ordered_at', now()->toDateString())
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