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

        $spice = DB::table('option_groups')
            ->where('name', 'Spice Level')
            ->first();

        if (! $spice) {
            throw new RuntimeException(
                'Spice Level option group is missing. Run OptionGroupsSeeder first.'
            );
        }

        // Beverage items that offer both Hot and Iced.
        $temperatureItemNames = [
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
            'Dark Chocolate',
            'Matcha Latte',
            'Strawberry Latte',
        ];

        $temperatureItems = DB::table('inventory_items')
            ->whereIn('name', $temperatureItemNames)
            ->get(['id', 'name']);

        $foundTemperatureNames = $temperatureItems->pluck('name')->all();
        $missingTemperatureNames = array_values(array_diff($temperatureItemNames, $foundTemperatureNames));

        if ($missingTemperatureNames) {
            throw new RuntimeException(
                'These temperature inventory items were not found: ' . implode(', ', $missingTemperatureNames)
            );
        }

        foreach ($temperatureItems as $item) {
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

        // Sisig items that offer Spicy / Non-Spicy.
        $spiceItemNames = [
            'Pork Sisig (Rice Meal)',
            'Chicken Sisig (Rice Meal)',
        ];

        $spiceItems = DB::table('inventory_items')
            ->whereIn('name', $spiceItemNames)
            ->get(['id', 'name']);

        $foundSpiceNames = $spiceItems->pluck('name')->all();
        $missingSpiceNames = array_values(array_diff($spiceItemNames, $foundSpiceNames));

        if ($missingSpiceNames) {
            throw new RuntimeException(
                'These spice inventory items were not found: ' . implode(', ', $missingSpiceNames)
            );
        }

        foreach ($spiceItems as $item) {
            DB::table('inventory_item_option_groups')->updateOrInsert(
                [
                    'inventory_item_id' => $item->id,
                    'option_group_id' => $spice->id,
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
