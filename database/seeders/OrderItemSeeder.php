<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
use App\Models\Order;
use App\Models\Order_Item;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $order = Order::where('order_number', 'ORD-0001')->first();
        $porkSisig = Inventory_Item::where('name', 'Pork Sisig')->first();

        if ($order && $porkSisig) {
            $unitPrice = $porkSisig->base_price ?? 150.00;

            Order_Item::create([
                'order_id' => $order->id,
                'menu_item_id' => $porkSisig->id,
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'subtotal' => $unitPrice * 1,
                'notes' => null,
            ]);
        }
    }
}