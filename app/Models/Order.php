<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Order_Item;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Payment;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'cashier_id',
        'order_number',
        'order_type',
        'status',
        'subtotal',
        'discount_amount',
        'discount_type',
        'total_amount',
        'ordered_at',
        'completed_at',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
    public function orderItems(): HasMany
    {
        return $this->hasMany(Order_Item::class);
    }
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}