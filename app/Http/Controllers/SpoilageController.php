<?php

namespace App\Http\Controllers;

use App\Models\Inventory_Item;
use App\Models\Inventory_Spoilage;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpoilageController extends Controller
{
    public function __construct(private StockMovementService $stockMovement)
    {
    }

    /** Log spoiled / damaged stock: takes it out of stock and records why. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $item = Inventory_Item::stockable()
            ->where('id', $validated['inventory_item_id'])
            ->where('is_active', true)
            ->first();

        if (! $item) {
            return back()
                ->withErrors(['inventory_item_id' => 'Please select an active inventory item.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $item) {
            $stock = $item->primaryStock();

            $applied = $this->stockMovement->move(
                $stock,
                -1 * (float) $validated['quantity'],
                'Spoilage',
                $validated['reason'] ?? 'Spoilage / damage',
            );

            Inventory_Spoilage::create([
                'inventory_item_id' => $item->id,
                'inventory_stock_id' => $stock->id,
                'recorded_by' => auth()->id(),
                // Stock never goes below zero, so log what was really removed.
                'quantity' => abs($applied) > 0 ? abs($applied) : (float) $validated['quantity'],
                'reason' => $validated['reason'] ?? null,
                'spoiled_at' => now(),
            ]);
        });

        return redirect()
            ->route('inventory.index', ['tab' => 'spoilage'])
            ->with('success', 'Spoilage logged and removed from stock.');
    }
}
