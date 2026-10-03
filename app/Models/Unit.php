<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Inventory_Item;

class Unit extends Model
{
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(Inventory_Item::class);
    }
}
