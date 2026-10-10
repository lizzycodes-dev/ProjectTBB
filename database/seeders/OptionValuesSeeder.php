<?php

namespace Database\Seeders;

use App\Models\Option_Groups;
use App\Models\Option_Values;
use Illuminate\Database\Seeder;
use RuntimeException;

class OptionValuesSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------------
        // Temperature
        // -------------------------------------------------------------
        $temperature = Option_Groups::where('name', 'Temperature')->first();

        if (! $temperature) {
            throw new RuntimeException(
                'Missing Temperature option group. Run OptionGroupsSeeder first.'
            );
        }

        Option_Values::firstOrCreate(
            ['option_group_id' => $temperature->id, 'name' => 'Hot'],
            ['price_adjustment' => 0, 'is_active' => true]
        );

        Option_Values::firstOrCreate(
            ['option_group_id' => $temperature->id, 'name' => 'Iced'],
            ['price_adjustment' => 10, 'is_active' => true]
        );

        // -------------------------------------------------------------
        // Spice Level
        // -------------------------------------------------------------
        $spice = Option_Groups::where('name', 'Spice Level')->first();

        if (! $spice) {
            throw new RuntimeException(
                'Missing Spice Level option group. Run OptionGroupsSeeder first.'
            );
        }

        Option_Values::firstOrCreate(
            ['option_group_id' => $spice->id, 'name' => 'Spicy'],
            ['price_adjustment' => 0, 'is_active' => true]
        );

        Option_Values::firstOrCreate(
            ['option_group_id' => $spice->id, 'name' => 'Non-Spicy'],
            ['price_adjustment' => 0, 'is_active' => true]
        );

        // -------------------------------------------------------------
        // Add-ons (Rice Toppings)
        // -------------------------------------------------------------
        $addons = Option_Groups::where('name', 'Add-ons')->first();

        if (! $addons) {
            throw new RuntimeException(
                'Missing Add-ons option group. Run OptionGroupsSeeder first.'
            );
        }

        Option_Values::firstOrCreate(
            ['option_group_id' => $addons->id, 'name' => 'Extra Rice'],
            ['price_adjustment' => 20, 'is_active' => true]
        );

        Option_Values::firstOrCreate(
            ['option_group_id' => $addons->id, 'name' => 'Extra Egg'],
            ['price_adjustment' => 20, 'is_active' => true]
        );
    }
}
