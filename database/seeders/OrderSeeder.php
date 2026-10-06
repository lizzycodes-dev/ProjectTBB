<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Last 60 days of orders plus a busier "today".
     * Totals are filled in by OrderItemSeeder once the lines exist.
     */
    public function run(): void
    {
        mt_srand(20261004);

        $cashier = User::where('email', 'cashier@thebrewingbar.test')->first()
            ?? User::where('email', 'manager@thebrewingbar.test')->first();

        $ordersPerPastDay = [1, 2, 1, 3, 2];
        $orderTypes = ['Dine-in', 'Takeout'];

        for ($daysAgo = 60; $daysAgo >= 0; $daysAgo--) {
            $date = today()->subDays($daysAgo);
            $isToday = $daysAgo === 0;

            $count = $isToday ? 10 : $ordersPerPastDay[$daysAgo % 5];

            for ($i = 1; $i <= $count; $i++) {
                $orderNumber = 'ORD-' . $date->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);

                if (Order::where('order_number', $orderNumber)->exists()) {
                    continue;
                }

                $gap = $isToday ? 45 : 150;

                $orderedAt = $date->copy()
                    ->setTime(7, 30)
                    ->addMinutes((($i - 1) * $gap) + mt_rand(0, 20));

                if (! $isToday || $i <= 6) {
                    $status = 'Completed';
                } elseif ($i <= 8) {
                    $status = 'Ready';
                } else {
                    $status = 'Pending';
                }

                $discountType = 'None';

                if (mt_rand(1, 100) <= 12) {
                    $discountType = mt_rand(0, 1) === 0 ? 'Senior' : 'PWD';
                }

                $order = new Order([
                    'cashier_id' => $cashier?->id ?? 1,
                    'order_number' => $orderNumber,
                    'queue_date' => $date->toDateString(),
                    'queue_number' => $i,
                    'order_type' => $orderTypes[mt_rand(0, 1)],
                    'status' => $status,
                    'subtotal' => 0,
                    'discount_amount' => 0,
                    'discount_type' => $discountType,
                    'total_amount' => 0,
                    'ordered_at' => $orderedAt,
                    'completed_at' => $status === 'Completed'
                        ? $orderedAt->copy()->addMinutes(mt_rand(8, 20))
                        : null,
                ]);

                $order->created_at = $orderedAt;
                $order->updated_at = $orderedAt;
                $order->save();
            }
        }
    }
}