<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    public function InventoryItem(): HasMany
    {
        return $this->hasMany(Inventory_Item::class, 'category_id');
    }
}
