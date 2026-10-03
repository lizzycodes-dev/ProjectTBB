<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Coffee',
                'description' => 'Coffee-based beverages.',
            ],
            [
                'name' => 'Non-Coffee',
                'description' => 'Non-coffee beverages.',
            ],
            [
                'name' => 'Pastries',
                'description' => 'Pastries and baked products.',
            ],
            [
                'name' => 'Food',
                'description' => 'Food and meal items.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
