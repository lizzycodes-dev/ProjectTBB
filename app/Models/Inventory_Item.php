<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory_Item extends Model
{
    protected $table = 'inventory_items';

    protected $fillable = [
        'unit_id',
        'category_id',
        'name',
        'menu_name',
        'inventory_type',
        'sheet_group',
        'cost_per_unit',
        'base_price',
        'description',
        'is_sellable',
        'is_active',
    ];

    protected $casts = [
        'cost_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /** Ingredient / Prepped Food rows only. Sellable menu products are excluded. */
    public function scopeStockable(Builder $query): Builder
    {
        return $query->whereIn('inventory_type', ['Ingredient', 'Prepped Food']);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(Inventory_Stock::class, 'inventory_item_id');
    }

    public function suppliers(): BelongsToMany
{
    return $this->belongsToMany(
        Supplier::class,
        'inventory_item_supplier',
        'inventory_item_id',
        'supplier_id'
    )->withTimestamps();
}

    /** Name of the area this item is kept in ("Bar Area" or "Kitchen Area"). */
    public function areaName(): string
    {
        $stock = $this->relationLoaded('inventoryStocks')
            ? $this->inventoryStocks->first()
            : $this->inventoryStocks()->orderBy('id')->first();

        if ($stock) {
            $stock->loadMissing('location');

            if ($stock->location) {
                return $stock->location->name;
            }
        }

        return $this->inventory_type === 'Prepped Food' ? 'Kitchen Area' : 'Bar Area';
    }

    /**
     * The stock record used by the Daily Sheet, Spoilage Log and deliveries.
     * Created in the item's default area when it does not exist yet.
     */
    public function primaryStock(): Inventory_Stock
    {
        $stock = $this->inventoryStocks()->orderBy('id')->first();

        if ($stock) {
            return $stock;
        }

        $locationName = $this->inventory_type === 'Prepped Food' ? 'Kitchen Area' : 'Bar Area';
        $location = Inventory_Locations::where('name', $locationName)->first()
            ?? Inventory_Locations::where('is_active', true)->orderBy('id')->firstOrFail();

        return Inventory_Stock::create([
            'inventory_item_id' => $this->id,
            'location_id' => $location->id,
            'current_quantity' => 0,
            'reorder_level' => 0,
        ]);
    }
}
