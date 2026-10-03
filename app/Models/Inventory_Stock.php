<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Inventory_Item;
use App\Models\Inventory_Locations;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Inventory_Transactions;

class Inventory_Stock extends Model
{

    protected $table = 'inventory_stocks';

    protected $fillable = [
        'inventory_item_id',
        'location_id',
        'current_quantity',
        'reorder_level',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(Inventory_Item::class, 'inventory_item_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Inventory_Locations::class, 'location_id');
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(Inventory_Transactions::class, 'inventory_stock_id');
    }
}
