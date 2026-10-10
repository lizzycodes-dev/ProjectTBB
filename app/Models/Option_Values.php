<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Option_Values extends Model
{
    protected $table = 'option_values';

    protected $fillable = [
        'option_group_id',
        'name',
        'price_adjustment',
        'is_active',
    ];

    public function optionGroup(): BelongsTo
    {
        return $this->belongsTo(
            Option_Groups::class,
            'option_group_id',
            'id'
        );
    }


    public function orderItemOptions(): HasMany
    {
        return $this->hasMany(
            Order_Item_Option::class,
            'option_value_id'
        );
    }
}
