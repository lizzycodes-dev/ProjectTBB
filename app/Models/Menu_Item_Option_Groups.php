<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu_Item_Option_Groups extends Model
{
    protected $table = 'menu_item_option_groups';

    protected $fillable = [
        'menu_item_id',
        'option_group_id',
        'is_required',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(
            Item::class,
            'menu_item_id'
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
