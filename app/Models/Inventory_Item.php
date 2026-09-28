<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory_Item extends Model
{
    protected $table = 'inventory_items';

    protected $fillable = [
        'unit_id',
        'name',
        'inventory_type',
        'is_active',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(Inventory_Stock::class, 'inventory_item_id');
    }
}
