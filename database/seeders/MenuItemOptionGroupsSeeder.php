<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
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

        // Drinks with separate Hot and Iced prices.
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
            $menuItem = Inventory_Item::where('menu_name', $itemName)
                ->where('inventory_type', 'Menu Item')
                ->first();

            if (! $menuItem) {
                throw new RuntimeException(
                    "Transferred menu item '{$itemName}' is missing. Check InventoryItemSeeder."
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
