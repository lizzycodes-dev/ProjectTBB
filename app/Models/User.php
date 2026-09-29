<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Added SoftDeletes import
use App\Models\Inventory_Transactions;
use App\Models\Order;
use App\Models\Kitchen_Order_Item;
use App\Models\Payment;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes; // 2. Added SoftDeletes trait

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id', // 3. Added role_id so we can assign positions!
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(Inventory_Transactions::class, 'recorded_by');
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }
    public function kitchenOrderItems(): HasMany
    {
        return $this->hasMany(Kitchen_Order_Item::class, 'prepared_by');
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'received_by');
    }
}