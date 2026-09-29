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

    // --- Migrated from the deleted Menu_Items model ---

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function recipeItems(): HasMany
    {
        return $this->hasMany(Recipe_Items::class, 'menu_item_id', 'id');
    }

    public function recipeAdjustments(): HasMany
    {
        return $this->hasMany(Menu_Option_Recipe_Adjustments::class, 'menu_item_id');
    }

    public function orderItems(): HasMany
    {
        // Explicitly defining 'menu_item_id' in case the database column wasn't renamed
        return $this->hasMany(Order_Item::class, 'menu_item_id');
    }

    public function optionGroups(): BelongsToMany
    {
        return $this->belongsToMany(Option_Groups::class, 'menu_item_option_groups', 'menu_item_id', 'option_group_id')
            ->withPivot('is_required');
    }
}