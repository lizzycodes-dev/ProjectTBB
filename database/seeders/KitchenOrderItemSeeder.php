<?php

namespace Database\Seeders;

use App\Models\Kitchen_Order_Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class KitchenOrderItemSeeder extends Seeder
{
    /**
     * Kitchen tickets for every seeded order, so past days also show up under
     * Completed Orders in the kitchen (same orders the Finance Report counts).
     */
    public function run(): void
    {
        $cook = User::where('email', 'cook@thebrewingbar.test')->first();

        $orders = Order::with('orderItems')
            ->orderBy('ordered_at')
            ->get();

        foreach ($orders as $order) {
            $isPending = $order->status === 'Pending';

            foreach ($order->orderItems as $orderItem) {
                Kitchen_Order_Item::updateOrCreate(
                    ['order_item_id' => $orderItem->id],
                    [
                        'prepared_by' => $isPending ? null : $cook?->id,
                        'status' => $isPending ? 'Pending' : 'Ready',
                        'started_at' => $isPending ? null : $order->ordered_at->copy()->addMinutes(2),
                        'completed_at' => $isPending ? null : $order->ordered_at->copy()->addMinutes(8),
                    ]
                );
            }
        }
    }
}