<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory_Item;
use Illuminate\Support\Facades\DB;
use App\Models\Unit;
use App\Models\Inventory_Stock;
use App\Models\Inventory_Transactions;
use App\Models\Inventory_Locations;

class InventoryItemController extends Controller
{
    public function index()
    {
        $items = Inventory_Item::with([
            'unit',
            'inventoryStocks.location',
        ])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(8);

        $activeItemCount = Inventory_Item::where('is_active', true)->count();

        $lowStockCount = \App\Models\Inventory_Stock::whereColumn(
            'current_quantity',
            '<=',
            'reorder_level'
        )->whereHas('inventoryItem', function ($query) {
            $query->where('is_active', true);
        })->count();

        $units = Unit::orderBy('name')->get();

        $stockItems = Inventory_Item::with('unit')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $locations = Inventory_Locations::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('inventory.index', compact(
            'items',
            'activeItemCount',
            'lowStockCount',
            'units',
            'stockItems',
            'locations'
        ));
    }
    public function toggleActive(Inventory_Item $inventoryItem)
    {
        DB::transaction(function () use ($inventoryItem) {
            $inventoryItem->is_active = !$inventoryItem->is_active;
            $inventoryItem->save();
        });

        return redirect()
            ->route('inventory.index')
            ->with(
                'success',
                $inventoryItem->is_active
                    ? 'Inventory item activated.'
                    : 'Inventory item deactivated.'
            );
    }
    public function updateUnit(Request $request, Inventory_Item $inventoryItem)
    {
        $validated = $request->validate([
            'unit_id' => ['nullable', 'exists:units,id'],
        ]);

        $inventoryItem->unit_id = $validated['unit_id'] ?? null;
        $inventoryItem->save();

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Inventory item unit updated.');
    }
    public function createStockIn()
    {
        $items = Inventory_Item::with('unit')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $locations = Inventory_Locations::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('inventory.stock-in', compact('items', 'locations'));
    }

    public function storeStockIn(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => [
                'required',
                'integer',
                'exists:inventory_items,id',
            ],
            'location_id' => [
                'required',
                'integer',
                'exists:inventory_locations,id',
            ],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $item = Inventory_Item::where('id', $validated['inventory_item_id'])
            ->where('is_active', true)
            ->first();

        if (!$item) {
            return back()
                ->withErrors(['inventory_item_id' => 'Please select an active inventory item.'])
                ->withInput();
        }

        if (!$item->unit_id) {
            return back()
                ->withErrors(['inventory_item_id' => 'Please assign a unit to this item before adding stock.'])
                ->withInput();
        }

        $location = Inventory_Locations::where('id', $validated['location_id'])
            ->where('is_active', true)
            ->first();

        if (!$location) {
            return back()
                ->withErrors(['location_id' => 'Please select an active inventory location.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $item) {
            $stock = Inventory_Stock::where('inventory_item_id', $item->id)
                ->where('location_id', $validated['location_id'])
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                $stock = Inventory_Stock::create([
                    'inventory_item_id' => $item->id,
                    'location_id' => $validated['location_id'],
                    'current_quantity' => 0,
                    'reorder_level' => 0,
                ]);
            }

            $stock->increment('current_quantity', $validated['quantity']);

            Inventory_Transactions::create([
                'inventory_stock_id' => $stock->id,
                'supplier_id' => null,
                'recorded_by' => auth()->id(),
                'transaction_type' => 'Stock In',
                'quantity' => $validated['quantity'],
                'unit_cost' => $validated['unit_cost'] ?? null,
                'reference_type' => null,
                'reference_id' => null,
                'reason' => $validated['reason'] ?? 'Incoming stock',
                'transaction_date' => now(),
            ]);
        });

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Stock added successfully.');
    }
    public function updateStockQuantity(Request $request, Inventory_Stock $stock)
    {
        $validated = $request->validate([
            'current_quantity' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $stock) {
            $stock = Inventory_Stock::whereKey($stock->id)
                ->lockForUpdate()
                ->firstOrFail();

            $newQuantity = (float) $validated['current_quantity'];
            $oldQuantity = (float) $stock->current_quantity;
            $difference = round($newQuantity - $oldQuantity, 3);

            if ($difference == 0) {
                return;
            }

            $stock->current_quantity = $newQuantity;
            $stock->save();

            Inventory_Transactions::create([
                'inventory_stock_id' => $stock->id,
                'supplier_id' => null,
                'recorded_by' => auth()->id(),
                'transaction_type' => 'Adjustment',
                'quantity' => $difference,
                'unit_cost' => null,
                'reference_type' => null,
                'reference_id' => null,
                'reason' => 'Manual stock quantity adjustment',
                'transaction_date' => now(),
            ]);
        });

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Stock quantity updated.');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventory_Item $inventory_Item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inventory_Item $inventory_Item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inventory_Item $inventory_Item)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory_Item $inventory_Item)
    {
        //
    }
}
