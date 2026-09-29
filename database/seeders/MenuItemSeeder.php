<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        // menu_name => [category name, base price]
        // TODO: replace category names and prices with your real ones.
        $items = [
            'Americano'         => ['Coffee', 0],
            'Cafe Latte'        => ['Coffee', 0],
            'Cappuccino'        => ['Coffee', 0],
            'Mochaccino'        => ['Coffee', 0],
            'Spanish Latte'     => ['Coffee', 0],
            'Matcha Espresso'   => ['Coffee', 0],
            'Caramel Macchiato' => ['Coffee', 0],
            'Hazelnut Latte'    => ['Coffee', 0],
            'Dark Chocolate'    => ['Non-Coffee', 0],
            'Matcha Latte'      => ['Non-Coffee', 0],
        ];

        foreach ($items as $menuName => [$categoryName, $price]) {
            $categoryId = DB::table('categories')->where('name', $categoryName)->value('id');

            if (! $categoryId) {
                throw new \RuntimeException(
                    "Category '{$categoryName}' not found. Check CategorySeeder."
                );
            }

            Inventory_Item::updateOrCreate(
                ['name' => 'Menu Item - ' . $menuName],
                [
                    'menu_name'      => $menuName,
                    'category_id'    => $categoryId,
                    'inventory_type' => 'Menu Item',
                    'base_price'     => $price,
                    'is_sellable'    => true,
                    'is_active'      => true,
                ]
            );
        }
    }
}