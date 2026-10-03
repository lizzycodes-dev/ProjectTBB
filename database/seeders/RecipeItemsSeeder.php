<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
use App\Models\Menu_Items;
use App\Models\Recipe_Items;
use Illuminate\Database\Seeder;

class RecipeItemsSeeder extends Seeder
{
    public function run(): void
    {
        $recipes = [
            'Pork Sisig' => [
                'Pork Sisig' => 1,
            ],

            'Chicken Sisig' => [
                'Chicken Sisig' => 1,
            ],

            'Binagoongan' => [
                'Binagoongan' => 1,
            ],

            'Fish Fillet' => [
                'Fish Fillet' => 1,
            ],

            'Pork Adobo' => [
                'Pork Adobo' => 1,
            ],
        ];

        foreach ($recipes as $menuName => $ingredients) {
            $menuItem = Menu_Items::where('name', $menuName)->first();

            foreach ($ingredients as $inventoryName => $quantity) {
                $inventoryItem = Inventory_Item::where(
                    'name',
                    $inventoryName
                )->first();

                Recipe_Items::create([
                    'menu_item_id' => $menuItem->id,
                    'inventory_item_id' => $inventoryItem->id,
                    'quantity_required' => $quantity,
                ]);
            }
        }
        $matchaLatte = Menu_Items::where('name', 'Matcha Latte')->first();
        $matcha = Inventory_Item::where('name', 'Matcha')->first();

        Recipe_Items::create([
            'menu_item_id' => $matchaLatte->id,
            'inventory_item_id' => $matcha->id,
            'quantity_required' => 0.02,
        ]);
    }
}
