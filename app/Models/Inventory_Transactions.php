<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Supplier;
use App\Models\Inventory_Stock;
use App\Models\User;

class Inventory_Transactions extends Model
{
    protected $table = 'inventory_transactions';

    protected $fillable = [
        'inventory_stock_id',
        'supplier_id',
        'recorded_by',
        'transaction_type',
        'quantity',
        'unit_cost',
        'reference_type',
        'reference_id',
        'reason',
        'transaction_date',
    ];
    public function inventoryStock(): BelongsTo
    {
        return $this->belongsTo(
            Inventory_Stock::class,
            'inventory_stock_id'
        );
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class,
            'supplier_id'
        );
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'recorded_by'
        );
    }
}
