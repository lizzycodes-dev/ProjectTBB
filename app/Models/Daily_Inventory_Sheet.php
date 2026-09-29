<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Daily_Inventory_Sheet extends Model
{
    public const OPEN = 'open';
    public const BEGINNING_SAVED = 'beginning_saved';
    public const CLOSED = 'closed';

    protected $table = 'daily_inventory_sheets';

    protected $fillable = [
        'sheet_date',
        'status',
        'opened_by',
        'closed_by',
        'beginning_saved_at',
        'closed_at',
    ];

    protected $casts = [
        'sheet_date' => 'date',
        'beginning_saved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(Daily_Inventory_Sheet_Entry::class, 'daily_inventory_sheet_id');
    }

    public function isClosed(): bool
    {
        return $this->status === self::CLOSED;
    }

    public function beginningIsSaved(): bool
    {
        return $this->status === self::BEGINNING_SAVED;
    }

    /** The sheet currently being worked on, created if none is open. */
    public static function current(): self
    {
        $sheet = static::where('status', '!=', self::CLOSED)
            ->orderByDesc('id')
            ->first();

        if (! $sheet) {
            $sheet = static::create([
                'sheet_date' => now()->toDateString(),
                'status' => self::OPEN,
                'opened_by' => auth()->id(),
            ]);
        }

        // Make sure every active stock item has a row (items may be added later).
        $existing = $sheet->entries()->pluck('inventory_item_id')->all();

        Inventory_Item::stockable()
            ->where('is_active', true)
            ->whereNotIn('id', $existing)
            ->pluck('id')
            ->each(function ($itemId) use ($sheet) {
                Daily_Inventory_Sheet_Entry::create([
                    'daily_inventory_sheet_id' => $sheet->id,
                    'inventory_item_id' => $itemId,
                ]);
            });

        return $sheet;
    }
}
