<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory_Spoilage extends Model
{
    protected $table = 'inventory_spoilages';

    protected $fillable = [
        'inventory_item_id',
        'inventory_stock_id',
        'recorded_by',
        'quantity',
        'reason',
        'spoiled_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'spoiled_at' => 'datetime',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(Inventory_Item::class, 'inventory_item_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
