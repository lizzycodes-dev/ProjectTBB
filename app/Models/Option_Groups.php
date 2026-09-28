<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Option_Values;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Menu_Items;

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
    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(
            Menu_Items::class,
            'menu_item_option_groups',
            'option_group_id',
            'menu_item_id'
        )->withPivot('is_required');
    }
}
