<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /** 
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            #RoleSeeder::class,
            InventoryLocationsSeeder::class,
            CategorySeeder::class,
            #UnitSeeder::class,
            OptionGroupsSeeder::class,
            OptionValuesSeeder::class,
            InventoryItemSeeder::class,
            InventoryItemOptionGroupsSeeder::class,
            #UserSeeder::class,
            #OrderSeeder::class,
            #OrderItemSeeder::class,
            #KitchenOrderItemSeeder::class,
            #PaymentSeeder::class,
        ]);
    }
}
