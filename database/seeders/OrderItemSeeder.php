<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use App\Models\Inventory_Item;
use App\Models\Order;
use App\Models\Order_Item;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    /**
     * Adds 1-3 lines to every order that has none, then fills in the
     * order's subtotal, discount (20% Senior/PWD, same as POS) and total.
     */
    public function run(): void
    {
        mt_srand(20261005);

        $menuItems = Inventory_Item::where('is_active', true)
            ->where('price', '>', 0)
            ->get()
            ->keyBy('name');

        $popular = [
            'Americano',
            'Cafe Latte',
            'Cappuccino',
            'Spanish Latte',
            'Caramel Macchiato',
            'Cookies & Cream Frappe',
            'Mango Boba',
            'Matcha Latte',
            'Pork Sisig',
            'Chicken Sisig',
            'Chicken Teriyaki',
            'Tocino with Egg',
            'Porkchop with Sauce',
            'French Fries',
            'Beef Cheese Nachos',
        ];

        $pool = [];

        foreach ($menuItems as $name => $menuItem) {
            $weight = in_array($name, $popular, true) ? 5 : 1;

            for ($w = 0; $w < $weight; $w++) {
                $pool[] = $menuItem;
            }
        }

        if (empty($pool)) {
            return;
        }

        $orders = Order::whereDoesntHave('orderItems')
            ->orderBy('ordered_at')
            ->get();

        foreach ($orders as $order) {
            $roll      = mt_rand(1, 10);
            $lineCount = $roll <= 4 ? 1 : ($roll <= 8 ? 2 : 3);
            $lineCount = min($lineCount, $menuItems->count());

            $usedIds  = [];
            $subtotal = 0;

            while (count($usedIds) < $lineCount) {
                $menuItem = $pool[array_rand($pool)];

                if (in_array($menuItem->id, $usedIds, true)) {
                    continue;
                }

                $usedIds[] = $menuItem->id;

                $quantity  = mt_rand(1, 10) <= 7 ? 1 : 2;
                $unitPrice = (float) $menuItem->price;
                $lineTotal = $unitPrice * $quantity;

                $orderItem = new Order_Item([
                    'order_id'          => $order->id,
                    'inventory_item_id' => $menuItem->id,
                    'quantity'          => $quantity,
                    'unit_price'        => $unitPrice,
                    'subtotal'          => $lineTotal,
                    'notes'             => null,
                ]);

                $orderItem->created_at = $order->ordered_at;
                $orderItem->updated_at = $order->ordered_at;
                $orderItem->save();

                $subtotal += $lineTotal;
            }

            $discountAmount = in_array($order->discount_type, ['Senior', 'PWD'], true)
                ? round($subtotal * 0.20, 2)
                : 0;

            // Set ordered_at explicitly so MySQL/MariaDB doesn't reset it to now().
            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'subtotal'        => $subtotal,
                    'discount_amount' => $discountAmount,
                    'total_amount'    => max(0, $subtotal - $discountAmount),
                    'ordered_at'      => $order->ordered_at->toDateTimeString(),
                ]);
        }
    }
}
