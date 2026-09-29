<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Davao Coffee Trading Co.',
                'contact_person' => 'Rodrigo Bantilan',
                'phone' => '0917-234-5678',
                'address' => 'Bankerohan Market, Davao City',
                'delivery_frequency' => 'Every 3 weeks',
                'last_delivery_date' => '2026-08-25',
                'notes' => 'Coffee beans and coffee jelly. Call 2 days ahead.',
                'items' => ['Coffee Beans', 'Coffee Jelly'],
            ],
            [
                'name' => 'Mindanao Fresh Dairy Supply',
                'contact_person' => 'Lina Gaborni',
                'phone' => '0922-876-5432',
                'address' => 'Matina, Davao City',
                'delivery_frequency' => 'Weekly',
                'last_delivery_date' => '2026-09-25',
                'notes' => 'Fresh milk, ice cream, eggs and cheese delivered every Monday and Friday.',
                'items' => ['Fresh Milk', 'Ice Cream', 'Egg', 'Mozzarella', 'Cheese/Quickmelt'],
            ],
            [
                'name' => 'Metro Grocery Wholesale',
                'contact_person' => 'Allan Perez',
                'phone' => '0918-111-2233',
                'address' => 'SM Lanang Premier, Davao City',
                'delivery_frequency' => 'Monthly',
                'last_delivery_date' => '2026-09-08',
                'notes' => 'Powders, sauces, purees, syrups and boba. Bulk pricing available.',
                'items' => [
                    'Vanilla', 'Caramel', 'Dark Chocolate', 'Matcha', 'Graham', 'Crushed Oreo',
                    'Cookies & Cream', 'Strawberry', 'Mango', 'Ube', 'Red Velvet', 'Lemon Ice Tea',
                    'Sauce - Caramel', 'Sauce - Chocolate', 'Sauce - Condensed Milk',
                    'Puree - Strawberry', 'Puree - Blueberry', 'Puree - Mango',
                    'Syrup - Lychee', 'Syrup - Peach', 'Syrup - Cucumber', 'Syrup - Blue Lemonade',
                    'Syrup - Orange', 'Popping Boba - Mango', 'Popping Boba - Strawberry', 'Soda Water',
                ],
            ],
            [
                'name' => "Nanay Carmen's Kitchen",
                'contact_person' => 'Carmen Dagatan',
                'phone' => '0905-444-5566',
                'address' => 'New Matina, Davao City',
                'delivery_frequency' => 'Daily (weekdays)',
                'last_delivery_date' => '2026-09-28',
                'notes' => 'Prepped rice meals and toppings, packed per serving.',
                'items' => [
                    'Pork Sisig', 'Chicken Sisig', 'Pork Sisig NS', 'Chicken Sisig NS', 'Binagoongan',
                    'C-Teriyaki', 'Fish Fillet', 'Bangus', 'Pork Adobo', 'Chicken Adobo', 'Baked Mac',
                ],
            ],
            [
                'name' => 'Quickserve Food Distributors',
                'contact_person' => 'Berto Salinas',
                'phone' => '0933-667-7890',
                'address' => 'Toril, Davao City',
                'delivery_frequency' => 'Twice a week',
                'last_delivery_date' => '2026-09-26',
                'notes' => 'Frozen and packaged goods. Confirm orders by SMS.',
                'items' => [
                    'Burger Patty', 'Fries', 'Pork Chop', 'Chicken Franks', 'Corn Beef', 'Pasta',
                    'Pork/Chicken Ham', 'Pork/Chicken Tocino', 'Coke/Royal/Sprite',
                ],
            ],
        ];

        foreach ($suppliers as $data) {
            $items = $data['items'];
            unset($data['items']);

            $supplier = Supplier::updateOrCreate(
                ['name' => $data['name']],
                $data + ['is_active' => true]
            );

            $supplier->inventoryItems()->sync(
                Inventory_Item::whereIn('name', $items)->pluck('id')->all()
            );
        }
    }
}
