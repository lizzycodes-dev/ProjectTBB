<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockIn;
use Illuminate\Database\Seeder;

class PurchaseItemSeeder extends Seeder
{
    /**
     * Links each purchase to its supplier's stock-ins for that date,
     * with a unit cost of 5-9 pesos and subtotal = quantity x unit cost.
     */
    public function run(): void
    {
        $purchases = Purchase::doesntHave('purchaseItems')->get();

        foreach ($purchases as $purchase) {
            $stockIns = StockIn::where('supplier_id', $purchase->supplier_id)
                ->whereDate('recorded_at', $purchase->purchase_date)
                ->whereDoesntHave('purchaseItem')
                ->orderBy('id')
                ->get();

            foreach ($stockIns as $index => $stockIn) {
                $unitCost = 5 + ($index % 5);
                $quantity = (float) $stockIn->quantity;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'stock_in_id' => $stockIn->id,
                    'inventory_item_id' => $stockIn->inventory_item_id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $quantity * $unitCost,
                ]);
            }
        }
    }
}