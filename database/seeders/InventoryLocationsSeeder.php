<?php

namespace Database\Seeders;

use App\Models\Inventory_Locations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryLocationsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $locations = [
            [
                'name' => 'Kitchen Area',
                'description' => 'Prepared food stock and kitchen items.',
            ],
            [
                'name' => 'Bar Area',
                'description' => 'Coffee and other drinks, along with their ingredients and supplies.',
            ],
        ];

        foreach ($locations as $location) {
            DB::table('inventory_locations')->updateOrInsert(
                ['name' => $location['name']],
                [
                    'description' => $location['description'],
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
