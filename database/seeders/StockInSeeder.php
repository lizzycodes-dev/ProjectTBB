<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
use App\Models\StockIn;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StockInSeeder extends Seeder
{
    /**
     * Opening stock + restock deliveries for prepped food only.
     * Anchored on 2026-10-10 so the demo data lands on a fixed date.
     */
    public function run(): void
    {
        if (StockIn::exists()) {
            return;
        }

        $manager    = User::where('email', 'manager@thebrewingbar.test')->first();
        $recordedBy = $manager?->id ?? User::query()->value('id');

        $supplierIds = DB::table('suppliers')->pluck('id', 'name');

        // Supplier assignment by item name
        $supplierByItem = [];

        foreach (
            [
                'Pork Chop',
                'Burger Patty',
                'Chicken Franks',
                'Pork/Chicken Tocino',
                'Corn Beef',
                'Pork/Chicken Ham',
            ] as $name
        ) {
            $supplierByItem[$name] = 'Davao Fresh Meats Trading';
        }

        foreach (
            [
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
            ] as $name
        ) {
            $supplierByItem[$name] = 'Mindanao Grocers Supply';
        }

        // Only prepped food items, only in the Kitchen Area.
        $items = Inventory_Item::with('category')
            ->where('inventory_type', 'prepped')
            ->where('is_active', true)
            ->whereHas('inventoryLocation', function ($q) {
                $q->where('name', 'Kitchen Area');
            })
            ->whereHas('category', function ($q) {
                // Exclude any category that isn't a real food dish.
                $q->whereNotIn('name', [
                    'Ingredient',
                    'Puree',
                    'Sauce',
                    'Powder',
                ]);
            })
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            $this->command->warn('No prepped food items found. Skipping StockInSeeder.');
            return;
        }

        $this->command->info("Seeding stock-ins for {$items->count()} prepped food items...");

        // Anchor on 2026-10-10
        $anchor = Carbon::create(2026, 10, 10, 0, 0, 0);

        foreach ($items as $index => $item) {
            $supplierName = $supplierByItem[$item->name] ?? null;
            $supplierId   = $supplierName ? ($supplierIds[$supplierName] ?? null) : null;

            $base = 40 + (($index % 5) * 15);   // 40, 55, 70, 85, 100

            $deliveries = [
                // [daysBefore, quantityMultiplier, hour]
                [14, 1.00, 8],
                [7, 0.75, 8],
                [2, 0.50, 9],
                [0, 1.25, 7],   // lands ON 2026-10-10, 07:00
            ];

            foreach ($deliveries as [$daysBefore, $multiplier, $hour]) {
                $when = $anchor->copy()->subDays($daysBefore)->addHours($hour);

                StockIn::create([
                    'inventory_item_id' => $item->id,
                    'quantity'          => (int) round($base * $multiplier),
                    'supplier_id'       => $supplierId,
                    'recorded_by'       => $recordedBy,
                    'recorded_at'       => $when,
                    'remarks'           => $supplierName
                        ? 'Delivery from ' . $supplierName
                        : 'Prepared in-house',
                ]);
            }
        }

        $this->command->info('Done. Seeded ' . ($items->count() * 4) . ' stock-ins.');
    }
}
