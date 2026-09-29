<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Recipe_Items;
use App\Models\Menu_Option_Recipe_Adjustments;
use App\Models\Order_Item;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Menu_Items extends Model
{
    protected $table = 'menu_items';

    protected $fillable = [
        'category_id',
        'name',
        'base_price',
        'description',
        'is_active',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function recipeItems(): HasMany
    {
        return $this->hasMany(
            Recipe_Items::class,
            'menu_item_id',
            'id'
        );
    }

    public function recipeAdjustments(): HasMany
    {
        return $this->hasMany(
            Menu_Option_Recipe_Adjustments::class,
            'menu_item_id'
        );
    }
    public function orderItems(): HasMany
    {
        return $this->hasMany(Order_Item::class);
    }
    public function optionGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            Option_Groups::class,
            'menu_item_option_groups',
            'menu_item_id',
            'option_group_id'
        )
            ->withPivot('is_required');
    }
}
