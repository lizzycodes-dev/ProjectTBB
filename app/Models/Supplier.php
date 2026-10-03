<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'delivery_frequency',
        'last_delivery_date',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'last_delivery_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(Inventory_Transactions::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Supplier_Delivery::class);
    }

    public function inventoryItems(): BelongsToMany
{
    return $this->belongsToMany(
        Inventory_Item::class,
        'inventory_item_supplier',
        'supplier_id',
        'inventory_item_id'
    )->withTimestamps();
}
}
