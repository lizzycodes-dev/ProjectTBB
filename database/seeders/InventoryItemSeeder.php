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
        $kg = Unit::where('abbreviation', 'kg')->first();
        $liter = Unit::where('abbreviation', 'L')->first();
        $piece = Unit::where('abbreviation', 'pc')->first();

        // Kitchen Area - Prepped Food  [name => cost per unit]
        $kitchenItems = [
            'Pork Sisig' => 45,
            'Chicken Sisig' => 42,
            'Pork Sisig NS' => 45,
            'Chicken Sisig NS' => 42,
            'Binagoongan' => 50,
            'C-Teriyaki' => 40,
            'Fish Fillet' => 35,
            'Bangus' => 40,
            'Pork Adobo' => 45,
            'Chicken Adobo' => 40,
            'Chicken Franks' => 12,
            'Pork/Chicken Tocino' => 30,
            'Corn Beef' => 28,
            'Pork/Chicken Ham' => 20,
            'Egg' => 8,
            'Baked Mac' => 45,
            'Mozzarella' => 15,
            'Fries' => 20,
            'Burger Patty' => 22,
            'Pork Chop' => 45,
            'Bihon' => 25,
            'C/K for Bihon' => 25,
            'Mixed Vegetables' => 12,
            'Pasta' => 18,
            'Chicken Carbonara' => 35,
            'Carbonara/Spaghetti Sauce' => 18,
            'Nachos Chips/Beef' => 30,
            'Cheese/Quickmelt' => 15,
            'Gravy' => 8,
            'Teriyaki/PorkChop Sauce' => 8,
            'Coke/Royal/Sprite' => 22,
        ];

        foreach ($kitchenItems as $name => $cost) {
            Inventory_Item::updateOrCreate(
                ['name' => $name],
                [
                    'unit_id' => null,
                    'inventory_type' => 'Prepped Food',
                    'sheet_group' => 'Prepped Food',
                    'cost_per_unit' => $cost,
                    'is_active' => true,
                ]
            );
        }

        // Bar Area - Ingredients and beverage supplies for The Brewing Bar menu
        // [name, sheet group, unit, cost per unit]
        $barItems = [
            // Powders (Frappe, Smoothies, Oreo Milk, Boba, Matcha, Lemon Ice Tea ...)
            ['Vanilla', 'Powder', $sack, 350],
            ['Caramel', 'Powder', $sack, 350],
            ['Dark Chocolate', 'Powder', $sack, 380],
            ['Matcha', 'Powder', $sack, 650],
            ['Graham', 'Powder', $sack, 220],
            ['Crushed Oreo', 'Powder', $sack, 300],
            ['Cookies & Cream', 'Powder', $sack, 320],
            ['Strawberry', 'Powder', $sack, 350],
            ['Mango', 'Powder', $sack, 350],
            ['Ube', 'Powder', $sack, 380],
            ['Red Velvet', 'Powder', $sack, 360],
            ['Lemon Ice Tea', 'Powder', $sack, 280],

            // Sauces
            ['Sauce - Caramel', 'Sauce', $bottle, 180],
            ['Sauce - Chocolate', 'Sauce', $bottle, 180],
            ['Sauce - Condensed Milk', 'Sauce', $bottle, 150],

            // Purees
            ['Puree - Strawberry', 'Puree', $bottle, 260],
            ['Puree - Blueberry', 'Puree', $bottle, 260],
            ['Puree - Mango', 'Puree', $bottle, 260],

            // Syrups (Fizzy Coolers, Soda Fruit Jelly, Fruit Juice Pitchers)
            ['Syrup - Lychee', 'Syrup', $bottle, 240],
            ['Syrup - Peach', 'Syrup', $bottle, 240],
            ['Syrup - Cucumber', 'Syrup', $bottle, 240],
            ['Syrup - Blue Lemonade', 'Syrup', $bottle, 240],
            ['Syrup - Orange', 'Syrup', $bottle, 240],

            // Coffee and dairy (The Brewing Bar coffee / non-coffee list)
            ['Coffee Beans', 'Other', $kg, 650],
            ['Fresh Milk', 'Other', $liter, 95],
            ['Ice Cream', 'Other', $liter, 180],
            ['Popping Boba - Mango', 'Other', $kg, 260],
            ['Popping Boba - Strawberry', 'Other', $kg, 260],
            ['Coffee Jelly', 'Other', $kg, 140],
            ['Soda Water', 'Other', $piece, 25],
        ];

        foreach ($barItems as [$name, $group, $unit, $cost]) {
            Inventory_Item::updateOrCreate(
                ['name' => $name],
                [
                    'unit_id' => $unit?->id,
                    'inventory_type' => 'Ingredient',
                    'sheet_group' => $group,
                    'cost_per_unit' => $cost,
                    'is_active' => true,
                ]
            );
        }
    }
}
