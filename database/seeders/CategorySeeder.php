<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $categories = [
            ['name' => 'Coffee', 'description' => 'Coffee-based menu drinks (hot/cold).'],
            ['name' => 'Non Coffee', 'description' => 'Non-coffee beverages (hot/cold).'],
            ['name' => 'Frappe Ice Cream on Top', 'description' => 'Frappe drinks with ice cream on top.'],
            ['name' => 'Smoothies Ice Cream on Top', 'description' => 'Smoothies with ice cream on top.'],
            ['name' => 'Popping Boba Pearls', 'description' => 'Boba drinks with popping pearls.'],
            ['name' => 'Oreo Milk Series', 'description' => 'Oreo milk series, large size.'],
            ['name' => 'Fizzy Coolers', 'description' => 'Fizzy cooler drinks, large size.'],
            ['name' => 'Buy 1 Take 1 Smoothies', 'description' => 'Buy 1 Take 1 smoothies promo.'],
            ['name' => 'Soda Fruit Jelly Buy 1 Take 1', 'description' => 'Buy 1 Take 1 soda fruit jelly promo.'],
            ['name' => 'Fruit Juice Pitcher', 'description' => 'Fruit juice by the pitcher.'],
            ['name' => 'Rice Meals', 'description' => 'Rice meals with viand.'],
            ['name' => 'Rice Toppings', 'description' => 'Rice topping meals.'],
            ['name' => 'Snack Meals', 'description' => 'Sandwiches, pasta, fries, and snack items.'],

            // Keep these for your internal stock items so the existing
            // InventoryItemSeeder stock section still works.
            ['name' => 'Food', 'description' => 'Prepped food and kitchen stock items.'],
            ['name' => 'Ingredient', 'description' => 'Ingredients used to prepare menu items.'],
            ['name' => 'Puree', 'description' => 'Fruit and other puree stock.'],
            ['name' => 'Sauce', 'description' => 'Sauces and toppings used in preparation.'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
