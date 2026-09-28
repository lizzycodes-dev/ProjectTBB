<?php

namespace Database\Seeders;

use App\Models\Menu_Items;
use App\Models\Option_Groups;
use App\Models\Menu_Item_Option_Groups;
use Illuminate\Database\Seeder;
use RuntimeException;

class MenuItemOptionGroupsSeeder extends Seeder
{
    public function run(): void
    {
        $temperature = Option_Groups::where('name', 'Temperature')->first();

        if (! $temperature) {
            throw new RuntimeException(
                'Temperature option group is missing. Run OptionGroupsSeeder first.'
            );
        }

        // Only drinks with separate Hot and Iced prices on the menu.
        $temperatureItems = [
            'Americano',
            'Cafe Latte',
            'Cappuccino',
            'Mochaccino',
            'Spanish Latte',
            'Matcha Espresso',
            'Caramel Macchiato',
            'Hazelnut Latte',
            'Dark Chocolate',
            'Matcha Latte',
        ];

        foreach ($temperatureItems as $itemName) {
            $menuItem = Menu_Items::where('name', $itemName)->first();

            if (! $menuItem) {
                throw new RuntimeException(
                    "Menu item '{$itemName}' is missing. Run MenuItemsSeeder first."
                );
            }

            Menu_Item_Option_Groups::firstOrCreate(
                [
                    'menu_item_id' => $menuItem->id,
                    'option_group_id' => $temperature->id,
                ],
                [
                    'is_required' => true,
                ]
            );
        }
    }
}
