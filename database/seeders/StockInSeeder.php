<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
use App\Models\StockIn;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockInSeeder extends Seeder
{
    /**
     * Opening stock for every active prepped item, recorded this morning.
     * Stock on hand = total stock-in minus total stock-out.
     */
    public function run(): void
    {
        if (StockIn::exists()) {
            return;
        }

        $manager = User::where('email', 'manager@thebrewingbar.test')->first();
        $recordedBy = $manager?->id ?? User::query()->value('id');

        $supplierIds = DB::table('suppliers')->pluck('id', 'name');

        $supplierByItem = [];

        foreach ([
            'Pork Chop',
            'Burger Patty',
            'Chicken Franks',
            'Pork/Chicken Tocino',
            'Corn Beef',
            'Pork/Chicken Ham',
        ] as $name) {
            $supplierByItem[$name] = 'Davao Fresh Meats Trading';
        }

        foreach ([
            'Egg',
            'Mozzarella',
            'Fries',
            'Pasta',
            'Bihon',
            'C/K for Bihon',
            'Mixed Vegetables',
            'Nachos Chips/Beef',
            'Cheese/Quickmelt',
            'Gravy',
            'Carbonara/Spaghetti Sauce',
            'Teriyaki/PorkChop Sauce',
            'Coke/Royal/Sprite',
        ] as $name) {
            $supplierByItem[$name] = 'Mindanao Grocers Supply';
        }

        $items = Inventory_Item::where('inventory_type', 'prepped')
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $recordedAt = now()->startOfDay()->addHours(7);

        foreach ($items as $index => $item) {
            $supplierName = $supplierByItem[$item->name] ?? null;

            StockIn::create([
                'inventory_item_id' => $item->id,
                'quantity' => 25 + (($index % 5) * 10),
                'supplier_id' => $supplierName ? ($supplierIds[$supplierName] ?? null) : null,
                'recorded_by' => $recordedBy,
                'recorded_at' => $recordedAt,
                'remarks' => $supplierName ? 'Delivery from ' . $supplierName : 'Prepared in-house',
            ]);
        }
    }
}