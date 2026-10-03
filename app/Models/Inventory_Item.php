<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inventory_Item extends Model
{
    protected $table = 'inventory_items';

    protected $fillable = [
        'name',
        'category_id',
        'inventory_location_id',
        'unit_id',
        'inventory_type',
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

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class, 'inventory_item_id');
    }

    public function stockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class, 'inventory_item_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(Order_Item::class, 'menu_item_id');
    }

    public function optionGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            Option_Groups::class,
            'inventory_item_option_groups',
            'inventory_item_id',
            'option_group_id'
        )->withPivot('is_required');
    }
}
