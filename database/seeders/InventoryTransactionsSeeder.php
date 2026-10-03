<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
use App\Models\Inventory_Locations;
use App\Models\Inventory_Stock;
use App\Models\Inventory_Transactions;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventoryTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        $item = Inventory_Item::where('name', 'Pork Sisig')->first();

        $location = Inventory_Locations::where(
            'name',
            'Kitchen Area'
        )->first();

        $manager = User::where(
            'email',
            'manager@thebrewingbar.test'
        )->first();

        $stock = Inventory_Stock::where([
            'inventory_item_id' => $item->id,
            'location_id' => $location->id,
        ])->first();

        // Initial stock for testing
        $stock->update([
            'current_quantity' => 20,
        ]);

        Inventory_Transactions::create([
            'inventory_stock_id' => $stock->id,
            'supplier_id' => null,
            'recorded_by' => $manager->id,
            'transaction_type' => 'Stock In',
            'quantity' => 20,
            'unit_cost' => null,
            'reference_type' => 'Test',
            'reference_id' => null,
            'reason' => 'Initial test stock',
            'transaction_date' => now(),
        ]);

        // Deduct one serving for ORD-0001
        $stock->update([
            'current_quantity' => $stock->current_quantity - 1,
        ]);

        Inventory_Transactions::create([
            'inventory_stock_id' => $stock->id,
            'supplier_id' => null,
            'recorded_by' => $manager->id,
            'transaction_type' => 'Sale',
            'quantity' => -1,
            'unit_cost' => null,
            'reference_type' => 'Order',
            'reference_id' => 1,
            'reason' => 'Pork Sisig used for order ORD-0001',
            'transaction_date' => now(),
        ]);
    }
}
