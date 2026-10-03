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
            ['name' => 'Size'],
            ['description' => 'Drink size options.']
        );
    }
}
