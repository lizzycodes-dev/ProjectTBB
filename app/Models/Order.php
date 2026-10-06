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
        'queue_date',
        'queue_number',
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
        'queue_date' => 'date',
        'ordered_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
    /**
     * Queue label shown on every screen (POS receipt, kitchen, dashboard),
     * e.g. "#2". Falls back to the order number's last digits for old rows.
     */
    public function getQueueLabelAttribute(): string
    {
        $number = $this->queue_number ?? (int) substr($this->order_number, -4);

        return '#' . $number;
    }

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