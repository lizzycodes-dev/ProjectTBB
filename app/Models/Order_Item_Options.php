<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Order_Item;
use App\Models\Option_Values;

class Order_Item_Options extends Model
{
    protected $table = 'order_item_options';

    protected $fillable = [
        'order_item_id',
        'option_value_id',
        'price_adjustment',
    ];
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(
            Order_Item::class,
            'order_item_id',
            'id'
        );
    }

    public function optionValue(): BelongsTo
    {
        return $this->belongsTo(
            Option_Values::class,
            'option_value_id',
            'id'
        );
    }
}
