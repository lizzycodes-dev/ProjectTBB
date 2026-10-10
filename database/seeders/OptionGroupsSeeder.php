<?php

namespace Database\Seeders;

use App\Models\Option_Groups;
use Illuminate\Database\Seeder;

class OptionGroupsSeeder extends Seeder
{
    public function run(): void
    {
        Option_Groups::firstOrCreate(
            ['name' => 'Temperature'],
            ['description' => 'Temperature options for beverages.']
        );

        Option_Groups::firstOrCreate(
            ['name' => 'Spice Level'],
            ['description' => 'Spicy or non-spicy variation for sisig dishes.']
        );

        Option_Groups::firstOrCreate(
            ['name' => 'Add-ons'],
            ['description' => 'Optional add-ons for rice toppings (extra rice, extra egg, etc.).']
        );
    }
}
