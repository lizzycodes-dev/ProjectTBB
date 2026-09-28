<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Item extends Model
{
    protected $table = 'inventory_items';

    protected $fillable = [
        'unit_id',
        'category_id',
        'name',
        'menu_name',
        'inventory_type',
        'base_price',
        'description',
        'is_sellable',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_sellable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(Inventory_Stock::class, 'inventory_item_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(Order_Item::class, 'menu_item_id');
    }

    public function optionGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            Option_Groups::class,
            'menu_item_option_groups',
            'menu_item_id',
            'option_group_id'
        )->withPivot('is_required');
    }
}
