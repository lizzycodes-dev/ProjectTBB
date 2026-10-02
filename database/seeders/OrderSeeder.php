<?php

namespace Database\Seeders;

use App\Models\Inventory_Item; // Fix 1: Replace Menu_Items with Inventory_Item
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $cashier = User::where('email', 'cashier@thebrewingbar.test')->first();
        $porkSisig = Inventory_Item::where('name', 'Pork Sisig')->first(); // Fix 2: Use Inventory_Item with correct casing

        $price = $porkSisig->base_price ?? 150.00;

        Order::create([
            'cashier_id' => $cashier?->id ?? 1,
            'order_number' => 'ORD-0001',
            'order_type' => 'Dine-in',
            'status' => 'Pending',
            'subtotal' => $price,
            'discount_amount' => 0,
            'total_amount' => $price,
            'ordered_at' => now(),
        ]);
    }
}