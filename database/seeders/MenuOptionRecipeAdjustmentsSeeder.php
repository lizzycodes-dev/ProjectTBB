<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
use App\Models\Menu_Items;
use App\Models\Menu_Option_Recipe_Adjustments;
use App\Models\Option_Values;
use Illuminate\Database\Seeder;

class MenuOptionRecipeAdjustmentsSeeder extends Seeder
{
    public function run(): void
    {
        $latte = Menu_Items::where('name', 'Latte')->first();
        $caramelLatte = Menu_Items::where('name', 'Caramel Latte')->first();
        $matchaLatte = Menu_Items::where('name', 'Matcha Latte')->first();

        $vanilla = Option_Values::where('name', 'Vanilla')->first();
        $caramel = Option_Values::where('name', 'Caramel')->first();
        $matcha = Inventory_Item::where('name', 'Matcha')->first();

        $vanillaInventory = Inventory_Item::where('name', 'Vanilla')->first();
        $caramelInventory = Inventory_Item::where('name', 'Caramel')->first();

        // Latte + Vanilla
        Menu_Option_Recipe_Adjustments::firstOrCreate(
            [
                'menu_item_id' => $latte->id,
                'option_value_id' => $vanilla->id,
                'inventory_item_id' => $vanillaInventory->id,
            ],
            [
                'quantity_adjustment' => 0.02,
            ]
        );

        // Caramel Latte + Caramel
        Menu_Option_Recipe_Adjustments::firstOrCreate(
            [
                'menu_item_id' => $caramelLatte->id,
                'option_value_id' => $caramel->id,
                'inventory_item_id' => $caramelInventory->id,
            ],
            [
                'quantity_adjustment' => 0.02,
            ]
        );
    }
}
