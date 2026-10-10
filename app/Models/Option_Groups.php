<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Option_Groups extends Model
{
    protected $table = 'option_groups';

    protected $fillable = [
        'name',
        'description',
    ];

    public function optionValues(): HasMany
    {
        return $this->hasMany(
            Option_Values::class,
            'option_group_id',
            'id'
        );
    }

    public function InventoryItems(): BelongsToMany
    {
        return $this->belongsToMany(
            Inventory_Item::class,
            'inventory_item_option_groups',
            'option_group_id',
            'menu_item_id'
        )->withPivot('is_required');
    }
}
