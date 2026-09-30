<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory_Item_Option_Groups extends Model
{
    protected $table = 'inventory_item_option_groups';

    protected $fillable = [
        'inventory_item_id',
        'option_group_id',
        'is_required',
    ];

    public function InventoryItem(): BelongsTo
    {
        return $this->belongsTo(
            Inventory_Item::class,
            'inventory_item_id'
        );
    }

    public function optionGroup(): BelongsTo
    {
        return $this->belongsTo(
            Option_Groups::class,
            'option_group_id'
        );
    }
}
