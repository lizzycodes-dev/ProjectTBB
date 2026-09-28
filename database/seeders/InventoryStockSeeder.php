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
            ],
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

                Inventory_Stock::firstOrCreate(
                    [
                        'inventory_item_id' => $item->id,
                        'location_id' => $location->id,
                    ],
                    [
                        'current_quantity' => 0,
                        'reorder_level' => 0,
                    ]
                );
            }
        }
    }
}
