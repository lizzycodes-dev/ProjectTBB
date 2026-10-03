<?php

namespace Database\Seeders;

use App\Models\Inventory_Locations;
use Illuminate\Database\Seeder;

class InventoryLocationsSeeder extends Seeder
{
    public function run(): void
    {
        Inventory_Locations::create([
            'name' => 'Bar Area',
            'description' => 'Storage area for ingredients and materials used for beverages.',
            'is_active' => true,
        ]);

        Inventory_Locations::create([
            'name' => 'Kitchen Area',
            'description' => 'Storage area for prepped food and kitchen items.',
            'is_active' => true,
        ]);
    }
}
