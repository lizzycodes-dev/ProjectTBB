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

        $addons = DB::table('option_groups')
            ->where('name', 'Add-ons')
            ->first();

        if (! $addons) {
            throw new RuntimeException(
                'Add-ons option group is missing. Run OptionGroupsSeeder first.'
            );
        }

        // -------------------------------------------------------------
        // Temperature — drinks that can be Hot or Iced
        // -------------------------------------------------------------
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

        $missingTemperatureNames = array_values(array_diff(
            $temperatureItemNames,
            $temperatureItems->pluck('name')->all()
        ));

        if ($missingTemperatureNames) {
            throw new RuntimeException(
                'These temperature inventory items were not found: ' .
                    implode(', ', $missingTemperatureNames)
            );
        }

        foreach ($temperatureItems as $item) {
            DB::table('inventory_item_option_groups')->updateOrInsert(
                [
                    'inventory_item_id' => $item->id,
                    'option_group_id'   => $temperature->id,
                ],
                [
                    'is_required' => true,
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );
        }

        // -------------------------------------------------------------
        // Spice Level — sisig rice meals
        // -------------------------------------------------------------
        $spiceItemNames = [
            'Pork Sisig (Rice Meal)',
            'Chicken Sisig (Rice Meal)',
        ];

        $spiceItems = DB::table('inventory_items')
            ->whereIn('name', $spiceItemNames)
            ->get(['id', 'name']);

        $missingSpiceNames = array_values(array_diff(
            $spiceItemNames,
            $spiceItems->pluck('name')->all()
        ));

        if ($missingSpiceNames) {
            throw new RuntimeException(
                'These spice inventory items were not found: ' .
                    implode(', ', $missingSpiceNames)
            );
        }

        foreach ($spiceItems as $item) {
            DB::table('inventory_item_option_groups')->updateOrInsert(
                [
                    'inventory_item_id' => $item->id,
                    'option_group_id'   => $spice->id,
                ],
                [
                    'is_required' => true,
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );
        }

        // -------------------------------------------------------------
        // Add-ons — Rice Toppings only (not required)
        // -------------------------------------------------------------
        $addonsItemNames = [
            'Fried Siomai with Egg',
            'Pork Binagoongan',
            'Chicken Adobo',
            'Pork Adobo',
        ];

        $addonsItems = DB::table('inventory_items')
            ->whereIn('name', $addonsItemNames)
            ->get(['id', 'name']);

        $missingAddonsNames = array_values(array_diff(
            $addonsItemNames,
            $addonsItems->pluck('name')->all()
        ));

        if ($missingAddonsNames) {
            throw new RuntimeException(
                'These add-ons inventory items were not found: ' .
                    implode(', ', $missingAddonsNames)
            );
        }

        foreach ($addonsItems as $item) {
            DB::table('inventory_item_option_groups')->updateOrInsert(
                [
                    'inventory_item_id' => $item->id,
                    'option_group_id'   => $addons->id,
                ],
                [
                    // Optional: cashier chooses to add or not.
                    'is_required' => false,
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );
        }
    }
}
