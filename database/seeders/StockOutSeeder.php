<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
use App\Models\Order;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockOutSeeder extends Seeder
{
    /**
     * 1. Deducts prepared-food stock for today's POS sales.
     * 2. Adds wastage so a few items land at or below the low-stock
     *    threshold (5) and show up in the Dashboard alerts.
     */
    public function run(): void
    {
        if (StockOut::exists()) {
            return;
        }

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

        $stockItemIds = Inventory_Item::where('inventory_type', 'prepped')
            ->pluck('id', 'name');

        $orders = Order::with('orderItems.InventoryItem')
            ->whereDate('ordered_at', today())
            ->orderBy('ordered_at')
            ->get();

        foreach ($orders as $order) {
            foreach ($order->orderItems as $orderItem) {
                $menuName = $orderItem->InventoryItem->name ?? null;
                $stockName = $kitchenStockMap[$menuName] ?? null;

                if ($stockName === null || ! isset($stockItemIds[$stockName])) {
                    continue;
                }

                StockOut::create([
                    'inventory_item_id' => $stockItemIds[$stockName],
                    'quantity' => $orderItem->quantity,
                    'recorded_by' => $order->cashier_id,
                    'reason' => 'Sold via POS: ' . $order->order_number,
                    'recorded_at' => $order->ordered_at,
                    'remarks' => 'Order item: ' . $menuName,
                ]);
            }
        }

        // Item name => quantity that should remain on hand.
        $remainingTargets = [
            'Egg' => 4,
            'Pork Chop' => 3,
            'Burger Patty' => 5,
            'Mozzarella' => 0,
        ];

        $manager = User::where('email', 'manager@thebrewingbar.test')->first();
        $recordedBy = $manager?->id ?? User::query()->value('id');

        foreach ($remainingTargets as $name => $remaining) {
            $itemId = $stockItemIds[$name] ?? null;

            if ($itemId === null) {
                continue;
            }

            $available = (float) StockIn::where('inventory_item_id', $itemId)->sum('quantity')
                - (float) StockOut::where('inventory_item_id', $itemId)->sum('quantity');

            $quantity = $available - $remaining;

            if ($quantity <= 0) {
                continue;
            }

            StockOut::create([
                'inventory_item_id' => $itemId,
                'quantity' => $quantity,
                'recorded_by' => $recordedBy,
                'reason' => 'Wastage',
                'recorded_at' => now(),
                'remarks' => 'End-of-batch discard',
            ]);
        }
    }
}