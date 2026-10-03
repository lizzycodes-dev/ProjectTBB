<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu_Items;
use Illuminate\Database\Seeder;
use RuntimeException;

class MenuItemsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');

        foreach (['Coffee', 'Non-Coffee', 'Food'] as $required) {
            if (! $categories->has($required)) {
                throw new RuntimeException(
                    "Missing menu category: {$required}. Run CategorySeeder first."
                );
            }
        }

        $menuItems = [
            // Coffee
            ['Coffee', 'Americano', 60, 'Hot or iced coffee.'],
            ['Coffee', 'Cafe Latte', 75, 'Hot or iced coffee.'],
            ['Coffee', 'Cappuccino', 75, 'Hot or iced coffee.'],
            ['Coffee', 'Mochaccino', 85, 'Hot or iced coffee.'],
            ['Coffee', 'Spanish Latte', 85, 'Hot or iced coffee.'],
            ['Coffee', 'Matcha Espresso', 89, 'Coffee beverage with matcha.'],
            ['Coffee', 'Caramel Macchiato', 85, 'Coffee beverage with caramel.'],
            ['Coffee', 'Hazelnut Latte', 85, 'Coffee beverage with hazelnut.'],
            ['Coffee', 'Coffee Jelly', 99, 'Iced coffee beverage.'],
            ['Coffee', 'Choco Almond Toffee', 95, 'Iced coffee beverage.'],

            // Non-Coffee
            ['Non-Coffee', 'Dark Chocolate', 75, 'Hot or iced chocolate beverage.'],
            ['Non-Coffee', 'Matcha Latte', 85, 'Hot or iced matcha beverage.'],
            ['Non-Coffee', 'Strawberry Latte', 89, 'Iced strawberry beverage.'],
            ['Non-Coffee', 'Affogato', 99, 'Cold dessert beverage.'],

            // Frappe
            ['Non-Coffee', 'Cookies & Cream Frappe', 120, 'Frappe with ice cream on top.'],
            ['Non-Coffee', 'Nutty Caramel Frappe', 120, 'Frappe with ice cream on top.'],
            ['Non-Coffee', 'Choco Java Chips Frappe', 120, 'Frappe with ice cream on top.'],
            ['Non-Coffee', 'Red Velvet Frappe', 120, 'Frappe with ice cream on top.'],

            // Smoothies
            ['Non-Coffee', 'Mango Graham Smoothie', 120, 'Smoothie with ice cream on top.'],
            ['Non-Coffee', 'Strawberry Smoothie', 120, 'Smoothie with ice cream on top.'],
            ['Non-Coffee', 'Blueberry Smoothie', 120, 'Smoothie with ice cream on top.'],
            ['Non-Coffee', 'Ube/Taro Smoothie', 120, 'Smoothie with ice cream on top.'],

            // Boba Pearls
            ['Non-Coffee', 'Mango Boba', 69, 'Boba beverage.'],
            ['Non-Coffee', 'Strawberry Boba', 69, 'Boba beverage.'],
            ['Non-Coffee', 'Choco Boba', 69, 'Boba beverage.'],
            ['Non-Coffee', 'Matcha Boba', 69, 'Boba beverage.'],
            ['Non-Coffee', 'Matchaberry Boba', 79, 'Boba beverage.'],
            ['Non-Coffee', 'Chocoberry Boba', 79, 'Boba beverage.'],

            // Oreo Milk Series
            ['Non-Coffee', 'Oreo Matcha', 109, 'Large size.'],
            ['Non-Coffee', 'Oreo Berry', 109, 'Large size.'],
            ['Non-Coffee', 'Oreo Choco', 109, 'Large size.'],
            ['Non-Coffee', 'Oreo Ube/Taro', 109, 'Large size.'],

            // Fizzy Fillers
            ['Non-Coffee', 'Berry Fizz', 109, 'Large size; price to confirm.'],
            ['Non-Coffee', 'Cucumber Fizz', 109, 'Large size; price to confirm.'],
            ['Non-Coffee', 'Lychee Fizz', 109, 'Large size; price to confirm.'],
            ['Non-Coffee', 'Peach Fizz', 109, 'Large size; price to confirm.'],

            // Rice Meals
            ['Food', 'Porkchop with Sauce', 110, 'Rice meal.'],
            ['Food', 'Chicken Teriyaki', 99, 'Rice meal.'],
            ['Food', 'Chicken Ala King', 99, 'Rice meal.'],
            ['Food', '2 pcs Burger Steak', 99, 'Rice meal.'],
            ['Food', 'Tocino with Egg', 89, 'Rice meal.'],
            ['Food', 'Chorizo with Egg', 89, 'Rice meal.'],
            ['Food', 'Cornbeef with Egg', 75, 'Rice meal.'],
            ['Food', 'Fish Fillet', 75, 'Rice meal.'],
            ['Food', 'Deep Fried Bangus', 85, 'Rice meal.'],
            ['Food', 'Ham and Egg', 65, 'Rice meal.'],
            ['Food', 'Chicken Hotdog with Egg', 65, 'Rice meal.'],
            ['Food', 'Pork Sisig', 75, 'Spicy or non-spicy; choose variation at checkout.'],
            ['Food', 'Chicken Sisig', 75, 'Spicy or non-spicy; choose variation at checkout.'],

            // Rice Toppings
            ['Food', 'Fried Siomai with Egg', 70, 'Rice topping.'],
            ['Food', 'Chicken Adobo', 75, 'Rice topping.'],
            ['Food', 'Pork Adobo', 75, 'Rice topping.'],

            // Snack Meals
            ['Food', 'Clubhouse Sandwich', 130, 'Pork or chicken ham.'],
            ['Food', 'Beef Cheese Burger with Fries', 129, 'Snack meal.'],
            ['Food', 'Double Patty Chicken Cheese Burger with Fries', 109, 'Snack meal.'],
            ['Food', 'Chicken Hotdog Sandwich', 59, 'Snack meal.'],
            ['Food', 'Chicken Carbonara with Coke', 99, 'Snack meal.'],
            ['Food', 'Ham Carbonara with Coke', 99, 'Snack meal.'],
            ['Food', 'Bihon Guisado', 95, 'Snack meal.'],
            ['Food', 'Bake Mac', 99, 'Snack meal.'],
            ['Food', 'Beef Cheese Nachos', 99, 'Snack meal.'],
            ['Food', 'Mozzarella Cheese Stick', 79, 'Snack meal.'],
            ['Food', 'French Fries', 65, 'Snack meal.'],
        ];

        foreach ($menuItems as [$categoryName, $name, $price, $description]) {
            Menu_Items::updateOrCreate(
                ['name' => $name],
                [
                    'category_id' => $categories[$categoryName],
                    'base_price' => $price,
                    'description' => $description,
                    'is_active' => true,
                ]
            );
        }
    }
}
