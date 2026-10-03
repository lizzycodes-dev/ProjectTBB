<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Inventory_Transactions;

class Supplier extends Model
{
    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(Inventory_Item::class);
    }
}
