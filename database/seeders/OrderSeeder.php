<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Roughly 50 orders spread across the last 60 days,
     * plus 50 more for today (mostly completed).
     *
     * Totals are filled in by OrderItemSeeder once the lines exist.
     */
    public function run(): void
    {
        mt_srand(20261004);

        $cashier = User::where('email', 'cashier@thebrewingbar.test')->first()
            ?? User::where('email', 'manager@thebrewingbar.test')->first();

        // ~1 order per day for the past 60 days → ~50 orders
        $ordersPerPastDay = [1, 1, 1, 1, 1];

        $orderTypes = ['Dine-in', 'Takeout'];

        for ($daysAgo = 60; $daysAgo >= 0; $daysAgo--) {
            $date    = today()->subDays($daysAgo);
            $isToday = $daysAgo === 0;

            // 50 today, ~1 per past day
            $count = $isToday ? 50 : $ordersPerPastDay[$daysAgo % 5];

            for ($i = 1; $i <= $count; $i++) {
                $orderNumber = 'ORD-' . $date->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);

                if (Order::where('order_number', $orderNumber)->exists()) {
                    continue;
                }

                // 50 orders today: ~15 min apart so they fit within a business day
                $gap = $isToday ? 15 : 150;

                $orderedAt = $date->copy()
                    ->setTime(7, 30)
                    ->addMinutes((($i - 1) * $gap) + mt_rand(0, 20));

                if (! $isToday) {
                    // All past orders are completed
                    $status = 'Completed';
                } elseif ($i <= 40) {
                    // 40 of today's 50 are completed
                    $status = 'Completed';
                } elseif ($i <= 46) {
                    // Next 6 are ready for pickup
                    $status = 'Ready';
                } else {
                    // Last 4 are still pending
                    $status = 'Pending';
                }

                $discountType = 'None';

                if (mt_rand(1, 100) <= 12) {
                    $discountType = mt_rand(0, 1) === 0 ? 'Senior' : 'PWD';
                }

                $order = new Order([
                    'cashier_id'      => $cashier?->id ?? 1,
                    'order_number'    => $orderNumber,
                    'queue_date'      => $date->toDateString(),
                    'queue_number'    => $i,
                    'order_type'      => $orderTypes[mt_rand(0, 1)],
                    'status'          => $status,
                    'subtotal'        => 0,
                    'discount_amount' => 0,
                    'discount_type'   => $discountType,
                    'total_amount'    => 0,
                    'ordered_at'      => $orderedAt,
                    'completed_at'    => $status === 'Completed'
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
