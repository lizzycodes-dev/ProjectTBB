<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Order;
use App\Models\Item;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Order_Item_Options;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Kitchen_Order_Item;

class Order_Item extends Model
{

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
    ];
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function InventoryItem(): BelongsTo
    {
        return $this->belongsTo(
            Inventory_Item::class,
            'menu_item_id'
        );
    }

    public function kitchenOrderItem(): HasOne
    {
        return $this->hasOne(
            Kitchen_Order_Item::class,
            'order_item_id',
            'id'
        );
    }
}
