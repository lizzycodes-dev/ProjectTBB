<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier_Delivery_Item extends Model
{
    protected $table = 'supplier_delivery_items';

    protected $fillable = [
        'supplier_delivery_id',
        'inventory_item_id',
        'quantity',
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Supplier_Delivery::class, 'supplier_delivery_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(Inventory_Item::class, 'inventory_item_id');
    }
}
