<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Menu_Items;
use App\Models\Inventory_Item;

class Recipe_Items extends Model
{
    protected $table = 'recipe_items';

    protected $fillable = [
        'menu_item_id',
        'inventory_item_id',
        'quantity_required',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(
            Menu_Items::class,
            'menu_item_id'
        );
    }
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(Inventory_Item::class);
    }
}
