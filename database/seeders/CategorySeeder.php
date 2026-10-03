<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();


        $categories = [
            ['name' => 'Food', 'description' => 'Prepared food menu and kitchen stock items.'],
            ['name' => 'Coffee', 'description' => 'Coffee-based menu drinks.'],
            ['name' => 'Non Coffee', 'description' => 'Non-coffee menu drinks.'],
            ['name' => 'Ingredient', 'description' => 'Ingredients used to prepare menu items.'],
            ['name' => 'Puree', 'description' => 'Fruit and other puree stock.'],
            ['name' => 'Sauce', 'description' => 'Sauces and toppings used in preparation.'],
            ['name' => 'Juice', 'description' => 'Juice items and juice-related stock.'],
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
