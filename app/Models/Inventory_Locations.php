<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Inventory_Stock;

class Inventory_Locations extends Model
{
    protected $table = 'inventory_locations';

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(Inventory_Stock::class, 'location_id');
    }
}
