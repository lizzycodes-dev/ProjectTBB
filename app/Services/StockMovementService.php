<?php

namespace App\Services;

use App\Models\Inventory_Stock;
use App\Models\Inventory_Transactions;
use Illuminate\Support\Facades\DB;

/**
 * Single place that changes a stock quantity AND writes the matching
 * inventory_transactions row, so the ledger always agrees with the stock.
 */
class StockMovementService
{
    /**
     * @param  float  $delta  positive = stock in, negative = stock out
     * @return float          the quantity actually applied (stock never goes below 0)
     */
    public function move(
        Inventory_Stock $stock,
        float $delta,
        string $type,
        ?string $reason = null,
        ?float $unitCost = null,
        ?int $supplierId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): float {
        return DB::transaction(function () use (
            $stock, $delta, $type, $reason, $unitCost, $supplierId, $referenceType, $referenceId
        ) {
            $locked = Inventory_Stock::whereKey($stock->id)->lockForUpdate()->firstOrFail();

            $old = (float) $locked->current_quantity;
            $new = max(0, round($old + $delta, 3));
            $applied = round($new - $old, 3);

            if ($applied == 0.0) {
                return 0.0;
            }

            $locked->current_quantity = $new;
            $locked->save();

            Inventory_Transactions::create([
                'inventory_stock_id' => $locked->id,
                'supplier_id' => $supplierId,
                'recorded_by' => auth()->id(),
                'transaction_type' => $type,
                'quantity' => $applied,
                'unit_cost' => $unitCost,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reason' => $reason,
                'transaction_date' => now(),
            ]);

            return $applied;
        });
    }
}
