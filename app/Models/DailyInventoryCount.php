<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyInventoryCount extends Model
{
    protected $table = 'daily_inventory_counts';

    protected $fillable = [
        'inventory_item_id',
        'stock_date',
        'beginning_quantity',
        'sold_quantity',
        'ending_quantity',
        'beginning_stock_in_id',
        'remarks',
    ];

    protected $casts = [
        'stock_date' => 'date',
        'beginning_quantity' => 'decimal:3',
        'sold_quantity' => 'decimal:3',
        'ending_quantity' => 'decimal:3',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(Inventory_Item::class, 'inventory_item_id');
    }

    public function beginningStockIn(): BelongsTo
    {
        return $this->belongsTo(StockIn::class, 'beginning_stock_in_id');
    }
}
