<?php

namespace Database\Seeders;

use App\Models\Menu_Items;
use App\Models\Option_Values;
use App\Models\Order;
use App\Models\Order_Item;
use App\Models\Order_Item_Options;
use Illuminate\Database\Seeder;

class OrderItemOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $order = Order::where(
            'order_number',
            'ORD-0001'
        )->first();

        $latte = Menu_Items::where(
            'name',
            'Latte'
        )->first();

        $vanilla = Option_Values::where(
            'name',
            'Vanilla'
        )->first();

        $orderItem = Order_Item::create([
            'order_id' => $order->id,
            'menu_item_id' => $latte->id,
            'quantity' => 1,
            'unit_price' => $latte->base_price,
            'subtotal' => $latte->base_price,
            'notes' => null,
        ]);

        Order_Item_Options::create([
            'order_item_id' => $orderItem->id,
            'option_value_id' => $vanilla->id,
            'price_adjustment' => $vanilla->price_adjustment,
        ]);
    }
}
