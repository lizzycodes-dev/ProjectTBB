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
            string $location,
            string $inventoryType
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
                        'inventory_type' => $inventoryType,
                        'is_active' => true,
                    ]
                );
            }
        };

        DB::transaction(function () use ($seedItems): void {

            /*
            |--------------------------------------------------------------------------
            | KITCHEN AREA — PREPPED FOOD STOCK (internal stock, not menu)
            |--------------------------------------------------------------------------
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
            ], 'Food', 'Kitchen Area', 'prepped');

            /*
            |--------------------------------------------------------------------------
            | BAR AREA — INGREDIENTS (internal stock)
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
            ], 'Ingredient', 'Bar Area', 'physical');

            /*
            |--------------------------------------------------------------------------
            | BAR AREA — SAUCES (internal stock)
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Sauce - Caramel'],
                ['name' => 'Sauce - Chocolate'],
                ['name' => 'Sauce - Condensed Milk'],
            ], 'Sauce', 'Bar Area', 'physical');

            /*
            |--------------------------------------------------------------------------
            | BAR AREA — PUREES (internal stock)
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Puree - Strawberry'],
                ['name' => 'Puree - Blueberry'],
                ['name' => 'Puree - Mango'],
            ], 'Puree', 'Bar Area', 'physical');

            /*
            |--------------------------------------------------------------------------
            | COFFEE — HOT / COLD PRICES
            | Hot price is base. Iced adds ₱10 via Temperature option.
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Americano', 'price' => 60, 'description' => 'Hot ₱60 / Iced ₱70.'],
                ['name' => 'Cafe Latte', 'price' => 75, 'description' => 'Hot ₱75 / Iced ₱85.'],
                ['name' => 'Cappuccino', 'price' => 75, 'description' => 'Hot ₱75 / Iced ₱85.'],
                ['name' => 'Mochaccino', 'price' => 85, 'description' => 'Hot ₱85 / Iced ₱95.'],
                ['name' => 'Spanish Latte', 'price' => 85, 'description' => 'Hot ₱85 / Iced ₱95.'],
                ['name' => 'Matcha Espresso', 'price' => 89, 'description' => 'Hot ₱89 / Iced ₱99.'],
                ['name' => 'Caramel Macchiato', 'price' => 85, 'description' => 'Hot ₱85 / Iced ₱95.'],
                ['name' => 'Hazelnut Latte', 'price' => 85, 'description' => 'Hot ₱85 / Iced ₱95.'],
                ['name' => 'Coffee Jelly', 'price' => 99, 'description' => 'Iced only. ₱99.'],
                ['name' => 'Choco Almond Toffee', 'price' => 95, 'description' => 'Iced only. ₱95.'],
            ], 'Coffee', 'Bar Area', 'physical');

            /*
            |--------------------------------------------------------------------------
            | NON COFFEE — HOT / COLD PRICES
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Dark Chocolate', 'price' => 75, 'description' => 'Hot ₱75 / Iced ₱85.'],
                ['name' => 'Matcha Latte', 'price' => 85, 'description' => 'Hot ₱85 / Iced ₱95.'],
                ['name' => 'Strawberry Latte', 'price' => 89, 'description' => 'Iced only. ₱89.'],
                ['name' => 'Affogato', 'price' => 99, 'description' => 'Cold dessert beverage. ₱99.'],
            ], 'Non Coffee', 'Bar Area', 'physical');

            /*
            |--------------------------------------------------------------------------
            | FRAPPE ICE CREAM ON TOP
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Cookies & Cream Frappe', 'price' => 120, 'description' => 'Frappe with ice cream on top.'],
                ['name' => 'Nutty Caramel Frappe', 'price' => 120, 'description' => 'Frappe with ice cream on top.'],
                ['name' => 'Choco Java Chips Frappe', 'price' => 120, 'description' => 'Frappe with ice cream on top.'],
                ['name' => 'Red Velvet Frappe', 'price' => 120, 'description' => 'Frappe with ice cream on top.'],
            ], 'Frappe Ice Cream on Top', 'Bar Area', 'physical');

            /*
            |--------------------------------------------------------------------------
            | SMOOTHIES ICE CREAM ON TOP
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Mango Graham Smoothie', 'price' => 120, 'description' => 'Smoothie with ice cream on top.'],
                ['name' => 'Strawberry Smoothie', 'price' => 120, 'description' => 'Smoothie with ice cream on top.'],
                ['name' => 'Blueberry Smoothie', 'price' => 120, 'description' => 'Smoothie with ice cream on top.'],
                ['name' => 'Ube/Taro Smoothie', 'price' => 120, 'description' => 'Smoothie with ice cream on top.'],
            ], 'Smoothies Ice Cream on Top', 'Bar Area', 'physical');

            /*
            |--------------------------------------------------------------------------
            | POPPING BOBA PEARLS
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Mango Boba', 'price' => 69, 'description' => 'Popping boba pearls.'],
                ['name' => 'Strawberry Boba', 'price' => 69, 'description' => 'Popping boba pearls.'],
                ['name' => 'Choco Boba', 'price' => 69, 'description' => 'Popping boba pearls.'],
                ['name' => 'Matcha Boba', 'price' => 69, 'description' => 'Popping boba pearls.'],
                ['name' => 'Matchaberry Boba', 'price' => 79, 'description' => 'Popping boba pearls.'],
                ['name' => 'Chocoberry Boba', 'price' => 79, 'description' => 'Popping boba pearls.'],
            ], 'Popping Boba Pearls', 'Bar Area', 'physical');

            /*
            |--------------------------------------------------------------------------
            | OREO MILK SERIES — LARGE SIZE @ 109
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Oreo Matcha', 'price' => 109, 'description' => 'Oreo milk series. Large size.'],
                ['name' => 'Oreo Berry', 'price' => 109, 'description' => 'Oreo milk series. Large size.'],
                ['name' => 'Oreo Choco', 'price' => 109, 'description' => 'Oreo milk series. Large size.'],
                ['name' => 'Oreo Ube/Taro', 'price' => 109, 'description' => 'Oreo milk series. Large size.'],
            ], 'Oreo Milk Series', 'Bar Area', 'physical');

            /*
            |--------------------------------------------------------------------------
            | FIZZY COOLERS — LARGE SIZE @ 109
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Berry Fizz', 'price' => 109, 'description' => 'Fizzy cooler. Large size.'],
                ['name' => 'Cucumber Fizz', 'price' => 109, 'description' => 'Fizzy cooler. Large size.'],
                ['name' => 'Lychee Fizz', 'price' => 109, 'description' => 'Fizzy cooler. Large size.'],
                ['name' => 'Peach Fizz', 'price' => 109, 'description' => 'Fizzy cooler. Large size.'],
            ], 'Fizzy Coolers', 'Bar Area', 'physical');

            /*
            |--------------------------------------------------------------------------
            | BUY 1 TAKE 1 SMOOTHIES @ 139
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Strawberry (B1T1)', 'price' => 139, 'description' => 'Buy 1 Take 1 smoothie.'],
                ['name' => 'Mango (B1T1)', 'price' => 139, 'description' => 'Buy 1 Take 1 smoothie.'],
                ['name' => 'Dark Choco (B1T1)', 'price' => 139, 'description' => 'Buy 1 Take 1 smoothie.'],
                ['name' => 'Cookies & Cream (B1T1)', 'price' => 139, 'description' => 'Buy 1 Take 1 smoothie.'],
                ['name' => 'Ube/Taro (B1T1)', 'price' => 139, 'description' => 'Buy 1 Take 1 smoothie.'],
                ['name' => 'Red Velvet (B1T1)', 'price' => 139, 'description' => 'Buy 1 Take 1 smoothie.'],
                ['name' => 'Caramel (B1T1)', 'price' => 139, 'description' => 'Buy 1 Take 1 smoothie.'],
            ], 'Buy 1 Take 1 Smoothies', 'Bar Area', 'physical');

            /*
            |--------------------------------------------------------------------------
            | SODA FRUIT JELLY BUY 1 TAKE 1 @ 129
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Blue Lagoon', 'price' => 129, 'description' => 'Soda fruit jelly. Buy 1 Take 1.'],
                ['name' => 'Red Sunset', 'price' => 129, 'description' => 'Soda fruit jelly. Buy 1 Take 1.'],
                ['name' => 'Green de Mint', 'price' => 129, 'description' => 'Soda fruit jelly. Buy 1 Take 1.'],
            ], 'Soda Fruit Jelly Buy 1 Take 1', 'Bar Area', 'physical');

            /*
            |--------------------------------------------------------------------------
            | FRUIT JUICE PITCHER @ 110
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Lemon Ice Tea', 'price' => 110, 'description' => 'Fruit juice pitcher.'],
                ['name' => 'Blue Lemonade', 'price' => 110, 'description' => 'Fruit juice pitcher.'],
                ['name' => 'Pink Lychee', 'price' => 110, 'description' => 'Fruit juice pitcher.'],
                ['name' => 'Cucumber Lemonade', 'price' => 110, 'description' => 'Fruit juice pitcher.'],
                ['name' => 'Orange', 'price' => 110, 'description' => 'Fruit juice pitcher.'],
            ], 'Fruit Juice Pitcher', 'Bar Area', 'physical');

            /*
            |--------------------------------------------------------------------------
            | RICE MEALS
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
                ['name' => 'Fish Fillet (Rice Meal)', 'price' => 75, 'description' => 'Rice meal.'],
                ['name' => 'Deep Fried Bangus', 'price' => 85, 'description' => 'Rice meal.'],
                ['name' => 'Ham and Egg', 'price' => 65, 'description' => 'Rice meal.'],
                ['name' => 'Chicken Hotdog with Egg', 'price' => 65, 'description' => 'Rice meal.'],
                ['name' => 'Pork Sisig (Rice Meal)', 'price' => 75, 'description' => 'Spicy or non-spicy. Choose variation at checkout.'],
                ['name' => 'Chicken Sisig (Rice Meal)', 'price' => 75, 'description' => 'Spicy or non-spicy. Choose variation at checkout.'],
            ], 'Rice Meals', 'Kitchen Area', 'prepped');

            /*
            |--------------------------------------------------------------------------
            | RICE TOPPINGS
            |--------------------------------------------------------------------------
            */

            $seedItems([
                ['name' => 'Fried Siomai with Egg', 'price' => 70, 'description' => 'Rice topping.'],
                ['name' => 'Pork Binagoongan', 'price' => 85, 'description' => 'Rice topping.'],
                ['name' => 'Chicken Adobo', 'price' => 75, 'description' => 'Rice topping.'],
                ['name' => 'Pork Adobo', 'price' => 75, 'description' => 'Rice topping.'],
            ], 'Rice Toppings', 'Kitchen Area', 'prepped');

            /*
            |--------------------------------------------------------------------------
            | SNACK MEALS
            |--------------------------------------------------------------------------
            */

            $seedItems([
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
            ], 'Snack Meals', 'Kitchen Area', 'prepped');
        });
    }
}
