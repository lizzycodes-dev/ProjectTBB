<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    protected $table = 'StockIn';

    protected $fillable = [
        'inventory_item_id',
    ];
}
