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

        Order_Item::create([
            'order_id' => $order->id,
            'menu_item_id' => $porkSisig->id,
            'quantity' => 1,
            'unit_price' => $porkSisig->base_price,
            'subtotal' => $porkSisig->base_price,
            'notes' => null,
        ]);
    }
}
