<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Inventory_Locations;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inventory_Item extends Model
{
    protected $table = 'inventory_items';

    protected $fillable = [
        'name',
        'category_id',
        'inventory_location_id',
        'price',
        'description',
        'is_active',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventoryLocation(): BelongsTo
    {
        return $this->belongsTo(Inventory_Locations::class, 'inventory_location_id');
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class, 'inventory_item_id');
    }

    public function stockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class, 'inventory_item_id');
    }

    public function dailyCounts(): HasMany
    {
        return $this->hasMany(DailyInventoryCount::class, 'inventory_item_id');
    }
    public function optionGroups(): HasMany
    {
        return $this->hasMany(
            Inventory_Item_Option_Groups::class,
            'inventory_item_id'
        );
    }
}
