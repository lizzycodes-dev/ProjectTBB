<?php

namespace App\Http\Controllers;

use App\Models\Kitchen_Order_Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KitchenOrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kitchenOrders = Kitchen_Order_Item::with([
            'orderItem.order',
            'orderItem.menuItem',
            'orderItem.options.optionValue',
            'preparedBy',
        ])
            ->whereIn('status', ['Pending', 'Preparing', 'Ready'])
            ->whereHas('orderItem.order', function ($query) {
                $query->where('status', '!=', 'Completed');
            })
            ->orderBy('created_at')
            ->get();

        $orders = $kitchenOrders->groupBy(
            fn($kitchenOrder) => $kitchenOrder->orderItem->order_id
        );

        $completedOrders = Kitchen_Order_Item::with([
            'orderItem.order',
            'orderItem.menuItem',
            'orderItem.options.optionValue',
            'preparedBy',
        ])
            ->whereHas('orderItem.order', function ($query) {
                $query->where('status', 'Completed');
            })
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy(
                fn($kitchenOrder) => $kitchenOrder->orderItem->order_id
            );

        return view(
            'kitchen.index',
            compact('orders', 'completedOrders')
        );
    }

    public function start(Kitchen_Order_Item $kitchenOrder)
    {
        $kitchenOrder->update([
            'status' => 'Preparing',
            'prepared_by' => Auth::id(),
            'started_at' => now(),
        ]);

        return redirect('/kitchen');
    }

    public function complete(Kitchen_Order_Item $kitchenOrder)
    {
        $kitchenOrder->update([
            'status' => 'Ready',
            'completed_at' => now(),
        ]);

        $order = $kitchenOrder->orderItem->order;

        $allReady = $order->orderItems()
            ->whereHas('kitchenOrderItem', function ($query) {
                $query->where('status', '!=', 'Ready');
            })
            ->doesntExist();

        if ($allReady) {
            $order->update([
                'status' => 'Ready',
            ]);
        }

        return redirect('/kitchen');
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Kitchen_Order_Item $kitchen_Order_Item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kitchen_Order_Item $kitchen_Order_Item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kitchen_Order_Item $kitchen_Order_Item)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kitchen_Order_Item $kitchen_Order_Item)
    {
        //
    }
}
