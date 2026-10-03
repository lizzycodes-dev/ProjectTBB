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
        $temperature = Option_Groups::where('name', 'Temperature')->first();
        $sizeGroup = Option_Groups::where('name', 'Size')->first();

        if (! $temperature || ! $sizeGroup) {
            throw new RuntimeException(
                'Missing Temperature or Size option group. Run OptionGroupsSeeder first.'
            );
        }

        // Temperature choices. Iced adds ₱10 to the listed Hot price.
        Option_Values::firstOrCreate(
            [
                'option_group_id' => $temperature->id,
                'name' => 'Hot',
            ],
            [
                'price_adjustment' => 0,
                'is_active' => true,
            ]
        );

        Option_Values::firstOrCreate(
            [
                'option_group_id' => $temperature->id,
                'name' => 'Iced',
            ],
            [
                'price_adjustment' => 10,
                'is_active' => true,
            ]
        );

        // Keep these available for future menu items that offer a size choice.
        Option_Values::firstOrCreate(
            [
                'option_group_id' => $sizeGroup->id,
                'name' => 'Regular',
            ],
            [
                'price_adjustment' => 0,
                'is_active' => true,
            ]
        );

        Option_Values::firstOrCreate(
            [
                'option_group_id' => $sizeGroup->id,
                'name' => 'Large',
            ],
            [
                'price_adjustment' => 20,
                'is_active' => true,
            ]
        );
    }
}
