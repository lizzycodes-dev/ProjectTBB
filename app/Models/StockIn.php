<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\PurchaseItem;

class StockIn extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'quantity',
        'supplier_id',
        'recorded_by',
        'recorded_at',
        'remarks',
    ];
    protected $table = 'stock_ins';

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(Inventory_Item::class, 'inventory_item_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function purchaseItem(): HasOne
    {
        return $this->hasOne(PurchaseItem::class, 'stock_in_id');
    }
}
