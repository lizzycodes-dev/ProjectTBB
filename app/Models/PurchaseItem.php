<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'stock_in_id',
        'inventory_item_id',
        'quantity',
        'unit_cost',
        'subtotal',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function stockIn(): BelongsTo
    {
        return $this->belongsTo(StockIn::class, 'stock_in_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(Inventory_Item::class, 'inventory_item_id');
    }
}
