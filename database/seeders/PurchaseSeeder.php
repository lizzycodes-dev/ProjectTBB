<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\StockIn;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PurchaseSeeder extends Seeder
{
    /**
     * One purchase per supplier for stock-ins that came from a supplier
     * and aren't linked to a purchase yet. Items are added by
     * PurchaseItemSeeder and expenses by ExpenseSeeder.
     */
    public function run(): void
    {
        if (Purchase::exists()) {
            return;
        }

        $groups = StockIn::whereNotNull('supplier_id')
            ->whereDoesntHave('purchaseItem')
            ->orderBy('id')
            ->get()
            ->groupBy('supplier_id');

        $number = 1;

        foreach ($groups as $supplierId => $group) {
            Purchase::create([
                'supplier_id' => $supplierId,
                'purchase_date' => Carbon::parse($group->first()->recorded_at)->toDateString(),
                'reference_number' => 'PO-' . now()->format('Ymd') . '-' . str_pad($number++, 3, '0', STR_PAD_LEFT),
                'notes' => 'Opening stock delivery',
            ]);
        }
    }
}