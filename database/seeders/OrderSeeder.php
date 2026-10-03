<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $cashier = User::where('email', 'cashier@thebrewingbar.test')->first();
        $porkSisig = Inventory_Item::where('name', 'Pork Sisig')->first();

        // Use the item's price, or default to 75.00 if it is blank
        $price = $porkSisig->base_price ?? 75.00;

        Order::create([
            'cashier_id' => $cashier->id,
            'order_number' => 'ORD-0001',
            'order_type' => 'Dine-in',
            'status' => 'Pending',
            'subtotal' => $price,
            'discount_amount' => 0,
            'total_amount' => $price,
            'ordered_at' => now(),
            'completed_at' => null,
        ]);
    }
}