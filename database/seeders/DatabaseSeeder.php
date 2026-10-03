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
            RoleSeeder::class,
            CategorySeeder::class,
            UnitSeeder::class,
            InventoryLocationsSeeder::class,
            OptionGroupsSeeder::class,
            OptionValuesSeeder::class,
            InventoryItemSeeder::class,
            InventoryStockSeeder::class,
            SupplierSeeder::class,
            MenuItemSeeder::class,
            //RecipeItemsSeeder::class,
            MenuItemOptionGroupsSeeder::class,
            //MenuOptionRecipeAdjustmentsSeeder::class,
            UserSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            KitchenOrderItemSeeder::class,
            PaymentSeeder::class,
            InventoryTransactionsSeeder::class,
            //OrderItemOptionsSeeder::class,
        ]);
    }
    
}