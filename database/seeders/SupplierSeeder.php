<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $suppliers = [
            [
                'name' => 'Davao Fresh Meats Trading',
                'contact_person' => 'Ramon Dela Cruz',
                'phone' => '0917-555-0101',
                'email' => 'orders@davaofreshmeats.test',
                'address' => 'Davao City',
            ],
            [
                'name' => 'Mindanao Grocers Supply',
                'contact_person' => 'Maria Santos',
                'phone' => '0917-555-0102',
                'email' => 'sales@mindanaogrocers.test',
                'address' => 'Davao City',
            ],
            [
                'name' => 'Brew & Bean Wholesale',
                'contact_person' => 'Paolo Reyes',
                'phone' => '0917-555-0103',
                'email' => 'hello@brewandbean.test',
                'address' => 'Davao City',
            ],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('suppliers')->updateOrInsert(
                ['name' => $supplier['name']],
                [
                    'contact_person' => $supplier['contact_person'],
                    'phone' => $supplier['phone'],
                    'email' => $supplier['email'],
                    'address' => $supplier['address'],
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}