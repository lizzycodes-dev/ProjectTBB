<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->date('queue_date')->nullable()->after('order_number');
            $table->unsignedInteger('queue_number')->nullable()->after('queue_date');
        });

        // Backfill existing orders from their order number
        // (ORD-20261006-0002 -> queue 2) and their ordered_at date.
        DB::table('orders')->orderBy('id')->each(function ($order) {
            DB::table('orders')->where('id', $order->id)->update([
                'queue_date' => date('Y-m-d', strtotime($order->ordered_at)),
                'queue_number' => (int) substr($order->order_number, -4),
            ]);
        });

        // Safety net: one queue number per day, even under concurrent orders.
        Schema::table('orders', function (Blueprint $table) {
            $table->unique(['queue_date', 'queue_number'], 'orders_queue_unique');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_queue_unique');
            $table->dropColumn(['queue_date', 'queue_number']);
        });
    }
};
