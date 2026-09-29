<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory_Item;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = DB::table('categories')
            ->pluck('id', 'name');

        $locationIds = DB::table('inventory_locations')
            ->pluck('id', 'name');

        $getCategoryId = function (string $name) use ($categoryIds): int {
            if (!isset($categoryIds[$name])) {
                throw new \RuntimeException("Category not found: {$name}");
            }

            return $categoryIds[$name];
        };

        $getLocationId = function (string $name) use ($locationIds): int {
            if (!isset($locationIds[$name])) {
                throw new \RuntimeException("Inventory location not found: {$name}");
            }

            return $locationIds[$name];
        };

        $seedItems = function (
            array $items,
            string $category,
            string $location
        ) use ($getCategoryId, $getLocationId): void {
            $categoryId = $getCategoryId($category);
            $locationId = $getLocationId($location);

            foreach ($items as $item) {
                Inventory_Item::updateOrCreate(
                    [
                        'name' => $item['name'],
                        'inventory_location_id' => $locationId,
                    ],
                    [
                        'category_id' => $categoryId,
                        'price' => $item['price'] ?? 0,
                        'description' => $item['description'] ?? '',
                        'is_active' => true,
                    ]
                );
            }
        };

        DB::transaction(function () use ($seedItems): void {
            /*
            |--------------------------------------------------------------------------
            | KITCHEN AREA — PREPPED FOOD STOCK
            |--------------------------------------------------------------------------
            | These are stock records, so overlapping names are marked "(Prepped)".
            */

            $seedItems([
                ['name' => 'Pork Sisig'],
                ['name' => 'Chicken Sisig'],
                ['name' => 'Pork Sisig NS'],
                ['name' => 'Chicken Sisig NS'],
                ['name' => 'Binagoongan'],
                ['name' => 'C-Teriyaki'],
                ['name' => 'Fish Fillet'],
                ['name' => 'Bangus'],
                ['name' => 'Pork Adobo'],
                ['name' => 'Chicken Adobo'],
                ['name' => 'Chicken Franks'],
                ['name' => 'Pork/Chicken Tocino'],
                ['name' => 'Corn Beef'],
                ['name' => 'Pork/Chicken Ham'],
                ['name' => 'Egg'],
                ['name' => 'Baked Mac'],
                ['name' => 'Mozzarella'],
                ['name' => 'Fries'],
                ['name' => 'Burger Patty'],
                ['name' => 'Pork Chop'],
                ['name' => 'Bihon'],
                ['name' => 'C/K for Bihon'],
                ['name' => 'Mixed Vegetables'],
                ['name' => 'Pasta'],
                ['name' => 'Chicken Carbonara'],
                ['name' => 'Carbonara/Spaghetti Sauce'],
                ['name' => 'Nachos Chips/Beef'],
                ['name' => 'Cheese/Quickmelt'],
                ['name' => 'Gravy'],
                ['name' => 'Teriyaki/PorkChop Sauce'],
                ['name' => 'Coke/Royal/Sprite'],
            ], 'Food', 'Kitchen Area');

            /*
            |--------------------------------------------------------------------------
            | BAR AREA — INGREDIENTS AND SUPPLIES
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Vanilla'],
                ['name' => 'Caramel'],
                ['name' => 'Dark Chocolate Sauce'],
                ['name' => 'Matcha'],
                ['name' => 'Graham'],
                ['name' => 'Crushed Oreo'],
                ['name' => 'Cookies & Cream'],
                ['name' => 'Strawberry'],
                ['name' => 'Mango'],
                ['name' => 'Ube'],
                ['name' => 'Red Velvet'],
                ['name' => 'Lemon Ice Tea'],
            ], 'Ingredient', 'Bar Area');

            $seedItems([
                ['name' => 'Sauce - Caramel'],
                ['name' => 'Sauce - Chocolate'],
                ['name' => 'Sauce - Condensed Milk'],
            ], 'Sauce', 'Bar Area');

            $seedItems([
                ['name' => 'Puree - Strawberry'],
                ['name' => 'Puree - Blueberry'],
                ['name' => 'Puree - Mango'],
            ], 'Puree', 'Bar Area');

            /*
            |--------------------------------------------------------------------------
            | BAR AREA — SELLABLE COFFEE MENU
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Americano', 'price' => 60, 'description' => 'Hot or iced coffee.'],
                ['name' => 'Cafe Latte', 'price' => 75, 'description' => 'Hot or iced coffee.'],
                ['name' => 'Cappuccino', 'price' => 75, 'description' => 'Hot or iced coffee.'],
                ['name' => 'Mochaccino', 'price' => 85, 'description' => 'Hot or iced coffee.'],
                ['name' => 'Spanish Latte', 'price' => 85, 'description' => 'Hot or iced coffee.'],
                ['name' => 'Matcha Espresso', 'price' => 89, 'description' => 'Coffee beverage with matcha.'],
                ['name' => 'Caramel Macchiato', 'price' => 85, 'description' => 'Coffee beverage with caramel.'],
                ['name' => 'Hazelnut Latte', 'price' => 85, 'description' => 'Coffee beverage with hazelnut.'],
                ['name' => 'Coffee Jelly', 'price' => 99, 'description' => 'Iced coffee beverage.'],
                ['name' => 'Choco Almond Toffee', 'price' => 95, 'description' => 'Iced coffee beverage.'],
            ], 'Coffee', 'Bar Area');

            /*
            |--------------------------------------------------------------------------
            | BAR AREA — SELLABLE NON-COFFEE MENU
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Dark Chocolate', 'price' => 75, 'description' => 'Hot or iced chocolate beverage.'],
                ['name' => 'Matcha Latte', 'price' => 85, 'description' => 'Hot or iced matcha beverage.'],
                ['name' => 'Strawberry Latte', 'price' => 89, 'description' => 'Iced strawberry beverage.'],
                ['name' => 'Affogato', 'price' => 99, 'description' => 'Cold dessert beverage.'],
                ['name' => 'Cookies & Cream Frappe', 'price' => 120, 'description' => 'Frappe with ice cream on top.'],
                ['name' => 'Nutty Caramel Frappe', 'price' => 120, 'description' => 'Frappe with ice cream on top.'],
                ['name' => 'Choco Java Chips Frappe', 'price' => 120, 'description' => 'Frappe with ice cream on top.'],
                ['name' => 'Red Velvet Frappe', 'price' => 120, 'description' => 'Frappe with ice cream on top.'],
                ['name' => 'Mango Graham Smoothie', 'price' => 120, 'description' => 'Smoothie with ice cream on top.'],
                ['name' => 'Strawberry Smoothie', 'price' => 120, 'description' => 'Smoothie with ice cream on top.'],
                ['name' => 'Blueberry Smoothie', 'price' => 120, 'description' => 'Smoothie with ice cream on top.'],
                ['name' => 'Ube/Taro Smoothie', 'price' => 120, 'description' => 'Smoothie with ice cream on top.'],
                ['name' => 'Mango Boba', 'price' => 69, 'description' => 'Boba beverage.'],
                ['name' => 'Strawberry Boba', 'price' => 69, 'description' => 'Boba beverage.'],
                ['name' => 'Choco Boba', 'price' => 69, 'description' => 'Boba beverage.'],
                ['name' => 'Matcha Boba', 'price' => 69, 'description' => 'Boba beverage.'],
                ['name' => 'Matchaberry Boba', 'price' => 79, 'description' => 'Boba beverage.'],
                ['name' => 'Chocoberry Boba', 'price' => 79, 'description' => 'Boba beverage.'],
                ['name' => 'Oreo Matcha', 'price' => 109, 'description' => 'Large size.'],
                ['name' => 'Oreo Berry', 'price' => 109, 'description' => 'Large size.'],
                ['name' => 'Oreo Choco', 'price' => 109, 'description' => 'Large size.'],
                ['name' => 'Oreo Ube/Taro', 'price' => 109, 'description' => 'Large size.'],
            ], 'Non Coffee', 'Bar Area');

            /*
            |--------------------------------------------------------------------------
            | BAR AREA — SELLABLE JUICE / FIZZ MENU
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Berry Fizz', 'price' => 109, 'description' => 'Large size; price to confirm.'],
                ['name' => 'Cucumber Fizz', 'price' => 109, 'description' => 'Large size; price to confirm.'],
                ['name' => 'Lychee Fizz', 'price' => 109, 'description' => 'Large size; price to confirm.'],
                ['name' => 'Peach Fizz', 'price' => 109, 'description' => 'Large size; price to confirm.'],
            ], 'Juice', 'Bar Area');

            /*
            |--------------------------------------------------------------------------
            | KITCHEN AREA — SELLABLE FOOD MENU
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Porkchop with Sauce', 'price' => 110, 'description' => 'Rice meal.'],
                ['name' => 'Chicken Teriyaki', 'price' => 99, 'description' => 'Rice meal.'],
                ['name' => 'Chicken Ala King', 'price' => 99, 'description' => 'Rice meal.'],
                ['name' => '2 pcs Burger Steak', 'price' => 99, 'description' => 'Rice meal.'],
                ['name' => 'Tocino with Egg', 'price' => 89, 'description' => 'Rice meal.'],
                ['name' => 'Chorizo with Egg', 'price' => 89, 'description' => 'Rice meal.'],
                ['name' => 'Cornbeef with Egg', 'price' => 75, 'description' => 'Rice meal.'],
                ['name' => 'Fish Fillet', 'price' => 75, 'description' => 'Rice meal.'],
                ['name' => 'Deep Fried Bangus', 'price' => 85, 'description' => 'Rice meal.'],
                ['name' => 'Ham and Egg', 'price' => 65, 'description' => 'Rice meal.'],
                ['name' => 'Chicken Hotdog with Egg', 'price' => 65, 'description' => 'Rice meal.'],
                ['name' => 'Pork Sisig', 'price' => 75, 'description' => 'Spicy or non-spicy; choose variation at checkout.'],
                ['name' => 'Chicken Sisig', 'price' => 75, 'description' => 'Spicy or non-spicy; choose variation at checkout.'],
                ['name' => 'Fried Siomai with Egg', 'price' => 70, 'description' => 'Rice topping.'],
                ['name' => 'Chicken Adobo', 'price' => 75, 'description' => 'Rice topping.'],
                ['name' => 'Pork Adobo', 'price' => 75, 'description' => 'Rice topping.'],
                ['name' => 'Clubhouse Sandwich', 'price' => 130, 'description' => 'Pork or chicken ham.'],
                ['name' => 'Beef Cheese Burger with Fries', 'price' => 129, 'description' => 'Snack meal.'],
                ['name' => 'Double Patty Chicken Cheese Burger with Fries', 'price' => 109, 'description' => 'Snack meal.'],
                ['name' => 'Chicken Hotdog Sandwich', 'price' => 59, 'description' => 'Snack meal.'],
                ['name' => 'Chicken Carbonara with Coke', 'price' => 99, 'description' => 'Snack meal.'],
                ['name' => 'Ham Carbonara with Coke', 'price' => 99, 'description' => 'Snack meal.'],
                ['name' => 'Bihon Guisado', 'price' => 95, 'description' => 'Snack meal.'],
                ['name' => 'Bake Mac', 'price' => 99, 'description' => 'Snack meal.'],
                ['name' => 'Beef Cheese Nachos', 'price' => 99, 'description' => 'Snack meal.'],
                ['name' => 'Mozzarella Cheese Stick', 'price' => 79, 'description' => 'Snack meal.'],
                ['name' => 'French Fries', 'price' => 65, 'description' => 'Snack meal.'],
            ], 'Food', 'Kitchen Area');
        });
    }
}
