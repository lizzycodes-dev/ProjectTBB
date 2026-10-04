<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * One payment per order, roughly 65% Cash / 35% GCash.
     */
    public function run(): void
    {
        mt_srand(20261006);

        $orders = Order::doesntHave('payment')
            ->orderBy('ordered_at')
            ->get();

        foreach ($orders as $order) {
            $total = (float) $order->total_amount;

            if (mt_rand(1, 100) <= 35) {
                $method = 'GCash';
                $amountReceived = $total;
                $reference = mt_rand(1000, 9999) . mt_rand(100000, 999999) . mt_rand(100, 999);
            } else {
                $method = 'Cash';
                $roundTo = [10, 50, 100][mt_rand(0, 2)];
                $amountReceived = ceil($total / $roundTo) * $roundTo;
                $reference = null;
            }

            $payment = new Payment([
                'order_id' => $order->id,
                'received_by' => $order->cashier_id,
                'payment_method' => $method,
                'amount' => $total,
                'amount_received' => $amountReceived,
                'change_amount' => round($amountReceived - $total, 2),
                'reference_number' => $reference,
                'proof_path' => null,
                'paid_at' => $order->ordered_at,
            ]);

            $payment->created_at = $order->ordered_at;
            $payment->updated_at = $order->ordered_at;
            $payment->save();
        }
    }
}