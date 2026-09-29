<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Daily_Inventory_Sheet_Entry extends Model
{
    protected $table = 'daily_inventory_sheet_entries';

    protected $fillable = [
        'daily_inventory_sheet_id',
        'inventory_item_id',
        'beginning',
        'ending',
        'applied_out',
        'remarks',
    ];

    protected $casts = [
        'beginning' => 'float',
        'ending' => 'float',
        'applied_out' => 'float',
    ];

    public function sheet(): BelongsTo
    {
        return $this->belongsTo(Daily_Inventory_Sheet::class, 'daily_inventory_sheet_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(Inventory_Item::class, 'inventory_item_id');
    }

    /** Beginning minus Ending, or null until both are filled in. */
    public function out(): ?float
    {
        if ($this->beginning === null || $this->ending === null) {
            return null;
        }

        return round($this->beginning - $this->ending, 3);
    }
}
