<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $bottle = Unit::where('abbreviation', 'btl')->first();
        $sack = Unit::where('abbreviation', 'sack')->first();

        // Kitchen Area - Prepped Food
        $kitchenItems = [
            'Pork Sisig', 'Chicken Sisig', 'Pork Sisig NS', 'Chicken Sisig NS',
            'Binagoongan', 'C-Teriyaki', 'Fish Fillet', 'Bangus', 'Pork Adobo',
            'Chicken Adobo', 'Chicken Franks', 'Pork/Chicken Tocino', 'Corn Beef',
            'Pork/Chicken Ham', 'Egg', 'Baked Mac', 'Mozzarella', 'Fries',
            'Burger Patty', 'Pork Chop', 'Bihon', 'C/K for Bihon', 'Mixed Vegetables',
            'Pasta', 'Chicken Carbonara', 'Carbonara/Spaghetti Sauce', 'Nachos Chips/Beef',
            'Cheese/Quickmelt', 'Gravy', 'Teriyaki/PorkChop Sauce', 'Coke/Royal/Sprite',
        ];

        foreach ($kitchenItems as $name) {
            Inventory_Item::updateOrCreate(
                ['name' => $name],
                [
                    'unit_id' => null,
                    'inventory_type' => 'Prepped Food',
                    'is_active' => true,
                ]
            );
        }

        // Bar Area - Ingredients and beverage supplies
        $barItems = [
            'Vanilla', 'Caramel', 'Dark Chocolate', 'Matcha', 'Graham',
            'Crushed Oreo', 'Cookies & Cream', 'Strawberry', 'Mango', 'Ube',
            'Red Velvet', 'Lemon Ice Tea', 'Sauce - Caramel', 'Sauce - Chocolate',
            'Sauce - Condensed Milk', 'Puree - Strawberry', 'Puree - Blueberry', 'Puree - Mango',
        ];

        $bottledItems = [
            'Sauce - Caramel', 'Sauce - Chocolate', 'Sauce - Condensed Milk',
            'Puree - Strawberry', 'Puree - Blueberry', 'Puree - Mango',
        ];

        foreach ($barItems as $name) {
            $unitId = in_array($name, $bottledItems, true) ? $bottle?->id : $sack?->id;

            Inventory_Item::updateOrCreate(
                ['name' => $name],
                [
                    'unit_id' => $unitId,
                    'inventory_type' => 'Ingredient',
                    'is_active' => true,
                ]
            );
        }

        // MISSING CODE ADDED HERE: Menu Items (Drinks)
        $menuItems = [
            'Americano', 'Cafe Latte', 'Cappuccino', 'Mochaccino',
            'Spanish Latte', 'Matcha Espresso', 'Caramel Macchiato',
            'Hazelnut Latte', 'Dark Chocolate', 'Matcha Latte',
        ];

        foreach ($menuItems as $name) {
            Inventory_Item::updateOrCreate(
                ['name' => $name],
                [
                    'menu_name' => $name,
                    'unit_id' => null,
                    'inventory_type' => 'Menu Item',
                    'is_active' => true,
                ]
            );
        }
    }
}