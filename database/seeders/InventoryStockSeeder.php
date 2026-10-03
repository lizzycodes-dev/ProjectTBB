<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
use App\Models\Inventory_Locations;
use App\Models\Inventory_Stock;
use Illuminate\Database\Seeder;
use RuntimeException;

class InventoryStockSeeder extends Seeder
{
    public function run(): void
    {
        $kitchen = Inventory_Locations::where('name', 'Kitchen Area')->first();
        $bar = Inventory_Locations::where('name', 'Bar Area')->first();

        if (! $kitchen || ! $bar) {
            throw new RuntimeException(
                'Kitchen Area or Bar Area is missing. Run InventoryLocationsSeeder first.'
            );
        }

        $stockItems = [
            'Kitchen Area' => [
                'Pork Sisig',
                'Chicken Sisig',
                'Pork Sisig NS',
                'Chicken Sisig NS',
                'Binagoongan',
                'C-Teriyaki',
                'Fish Fillet',
                'Bangus',
                'Pork Adobo',
                'Chicken Adobo',
                'Chicken Franks',
                'Pork/Chicken Tocino',
                'Corn Beef',
                'Pork/Chicken Ham',
                'Egg',
                'Baked Mac',
                'Mozzarella',
                'Fries',
                'Burger Patty',
                'Pork Chop',
                'Bihon',
                'C/K for Bihon',
                'Mixed Vegetables',
                'Pasta',
                'Chicken Carbonara',
                'Carbonara/Spaghetti Sauce',
                'Nachos Chips/Beef',
                'Cheese/Quickmelt',
                'Gravy',
                'Teriyaki/PorkChop Sauce',
                'Coke/Royal/Sprite',
            ],
            'Bar Area' => [
                'Vanilla',
                'Caramel',
                'Dark Chocolate',
                'Matcha',
                'Graham',
                'Crushed Oreo',
                'Cookies & Cream',
                'Strawberry',
                'Mango',
                'Ube',
                'Red Velvet',
                'Lemon Ice Tea',
                'Sauce - Caramel',
                'Sauce - Chocolate',
                'Sauce - Condensed Milk',
                'Puree - Strawberry',
                'Puree - Blueberry',
                'Puree - Mango',
                'Syrup - Lychee',
                'Syrup - Peach',
                'Syrup - Cucumber',
                'Syrup - Blue Lemonade',
                'Syrup - Orange',
                'Coffee Beans',
                'Fresh Milk',
                'Ice Cream',
                'Popping Boba - Mango',
                'Popping Boba - Strawberry',
                'Coffee Jelly',
                'Soda Water',
            ],
        ];

        // Starting stock and reorder level for the Bar Area (units follow InventoryItemSeeder).
        $barStart = [
            'Vanilla' => [5, 1], 'Caramel' => [4, 1], 'Dark Chocolate' => [6, 1], 'Matcha' => [3, 1],
            'Graham' => [5, 1], 'Crushed Oreo' => [4, 1], 'Cookies & Cream' => [4, 1],
            'Strawberry' => [1, 1], 'Mango' => [4, 1], 'Ube' => [3, 1], 'Red Velvet' => [3, 1],
            'Lemon Ice Tea' => [4, 1],
            'Sauce - Caramel' => [6, 2], 'Sauce - Chocolate' => [7, 2], 'Sauce - Condensed Milk' => [5, 2],
            'Puree - Strawberry' => [4, 2], 'Puree - Blueberry' => [2, 2], 'Puree - Mango' => [5, 2],
            'Syrup - Lychee' => [4, 2], 'Syrup - Peach' => [4, 2], 'Syrup - Cucumber' => [3, 2],
            'Syrup - Blue Lemonade' => [3, 2], 'Syrup - Orange' => [4, 2],
            'Coffee Beans' => [5, 1], 'Fresh Milk' => [12, 4], 'Ice Cream' => [6, 2],
            'Popping Boba - Mango' => [2, 1], 'Popping Boba - Strawberry' => [2, 1],
            'Coffee Jelly' => [2, 1], 'Soda Water' => [24, 6],
        ];

        $locations = [
            'Kitchen Area' => $kitchen,
            'Bar Area' => $bar,
        ];

        foreach ($stockItems as $locationName => $itemNames) {
            $location = $locations[$locationName];

            foreach ($itemNames as $itemName) {
                $item = Inventory_Item::where('name', $itemName)->first();

                if (! $item) {
                    throw new RuntimeException(
                        "Inventory item '{$itemName}' is missing. Run InventoryItemSeeder first."
                    );
                }

                [$startQty, $reorder] = $locationName === 'Kitchen Area'
                    ? [100, 20]
                    : ($barStart[$itemName] ?? [0, 0]);

                $stockData = [
                    'current_quantity' => $startQty,
                    'reorder_level' => $reorder,
                ];

                Inventory_Stock::updateOrCreate(
                    [
                        'inventory_item_id' => $item->id,
                        'location_id' => $location->id,
                    ],
                    $stockData
                );
            }
        }
    }
}
