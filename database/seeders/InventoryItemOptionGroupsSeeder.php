<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;
use Illuminate\Support\Facades\DB;

class InventoryItemOptionGroupsSeeder extends Seeder
{
    public function run(): void
    {
        $temperature = DB::table('option_groups')
            ->where('name', 'Temperature')
            ->first();

        if (! $temperature) {
            throw new RuntimeException(
                'Temperature option group is missing. Run OptionGroupsSeeder first.'
            );
        }

        // Beverage items that offer both Hot and Iced.
        $itemNames = [
            // Coffee
            'Americano',
            'Cafe Latte',
            'Cappuccino',
            'Mochaccino',
            'Spanish Latte',
            'Matcha Espresso',
            'Caramel Macchiato',
            'Hazelnut Latte',
            'Coffee Jelly',
            'Choco Almond Toffee',

            // Non-coffee drinks that can be served hot or iced
            'Dark Chocolate',
            'Matcha Latte',
            'Strawberry Latte',
        ];

        $items = DB::table('inventory_items')
            ->whereIn('name', $itemNames)
            ->get(['id', 'name']);

        $foundNames = $items->pluck('name')->all();
        $missingNames = array_values(array_diff($itemNames, $foundNames));

        if ($missingNames) {
            throw new RuntimeException(
                'These inventory items were not found: ' . implode(', ', $missingNames)
            );
        }

        foreach ($items as $item) {
            DB::table('inventory_item_option_groups')->updateOrInsert(
                [
                    'inventory_item_id' => $item->id,
                    'option_group_id' => $temperature->id,
                ],
                [
                    'is_required' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
