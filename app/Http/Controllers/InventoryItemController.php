<?php

namespace App\Http\Controllers;

use App\Models\Inventory_Item;
use App\Models\Inventory_Locations;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\DailyInventoryCount;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));
        $categoryId = $request->query('category_id');
        $locationId = $request->query('location_id');

        $itemsQuery = Inventory_Item::with([
            'category',
            'inventoryLocation',
        ])
            ->withSum('stockIns as total_stock_in', 'quantity')
            ->withSum('stockOuts as total_stock_out', 'quantity');

        if ($search !== '') {
            $itemsQuery->where('name', 'like', "%{$search}%");
        }

        if ($categoryId !== null && $categoryId !== '') {
            $itemsQuery->where('category_id', $categoryId);
        }

        if ($locationId !== null && $locationId !== '') {
            $itemsQuery->where('inventory_location_id', $locationId);
        }

        $items = $itemsQuery
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $activeItemCount = Inventory_Item::where('is_active', true)
            ->when($locationId !== null && $locationId !== '', function ($query) use ($locationId) {
                $query->where('inventory_location_id', $locationId);
            })
            ->count();

        $categories = Category::orderBy('name')->get();
        $locations = Inventory_Locations::orderBy('name')->get();

        return view('inventory.index', compact(
            'items',
            'activeItemCount',
            'categories',
            'locations',
            'search',
            'categoryId',
            'locationId'
        ));
    }

    public function toggleActive(Inventory_Item $inventoryItem)
    {
        $inventoryItem->is_active = ! $inventoryItem->is_active;
        $inventoryItem->save();

        return redirect()
            ->route('inventory.index')
            ->with(
                'success',
                $inventoryItem->is_active
                    ? 'Inventory item activated.'
                    : 'Inventory item deactivated.'
            );
    }

    public function createStockIn()
    {
        $items = Inventory_Item::with('inventoryLocation')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('inventory.stock-in', compact('items'));
    }

    public function storeStockIn(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => [
                'required',
                'integer',
                'exists:inventory_items,id',
            ],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $item = Inventory_Item::whereKey($validated['inventory_item_id'])
            ->where('is_active', true)
            ->first();

        if (! $item) {
            throw ValidationException::withMessages([
                'inventory_item_id' => 'Please select an active inventory item.',
            ]);
        }

        DB::table('stock_ins')->insert([
            'inventory_item_id' => $item->id,
            'quantity' => $validated['quantity'],
            'supplier_id' => $validated['supplier_id'] ?? null,
            'recorded_by' => auth()->id(),
            'recorded_at' => now(),
            'remarks' => $validated['remarks'] ?? 'Incoming stock',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Stock added successfully.');
    }

    public function createBeginDay(Request $request)
    {
        $locationId = $request->query('location_id');

        $itemsQuery = Inventory_Item::with(['category', 'inventoryLocation'])
            ->where('is_active', true);

        if ($locationId !== null && $locationId !== '') {
            $itemsQuery->where('inventory_location_id', $locationId);
        }

        $items = $itemsQuery->orderBy('name')->get();
        $locations = Inventory_Locations::orderBy('name')->get();

        return view('inventory.begin-day', compact(
            'items',
            'locations',
            'locationId'
        ));
    }

    public function storeBeginDay(Request $request)
    {
        $validated = $request->validate([
            'stock_date' => ['required', 'date'],
            'location_id' => ['required', 'integer', 'exists:inventory_locations,id'],
            'beginning' => ['sometimes', 'array'],
            'beginning.*' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $stockDate = $validated['stock_date'];
        $locationId = $validated['location_id'];

        // Get only active items in the selected area.
        $items = Inventory_Item::where('is_active', true)
            ->where('inventory_location_id', $locationId)
            ->orderBy('name')
            ->get();

        if ($items->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'beginning' => 'There are no active inventory items in this area.',
                ]);
        }

        // Keep only quantities that were actually entered.
        // Blank inputs are skipped; zero is retained as a valid quantity.
        $enteredQuantities = collect($validated['beginning'] ?? [])
            ->filter(fn($quantity) => $quantity !== null && $quantity !== '');

        if ($enteredQuantities->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'beginning' => 'Enter a quantity for at least one item.',
                ]);
        }

        // Make sure the submitted item IDs belong to this selected area.
        $itemsById = $items->keyBy('id');

        foreach ($enteredQuantities as $itemId => $quantity) {
            if (! $itemsById->has($itemId)) {
                throw ValidationException::withMessages([
                    'beginning' => 'One or more selected items are not available in this area.',
                ]);
            }
        }

        $itemIds = $enteredQuantities->keys();

        // Prevent duplicate beginning entries for the same item and date.
        if (
            DailyInventoryCount::whereDate('stock_date', $stockDate)
            ->whereIn('inventory_item_id', $itemIds)
            ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'stock_date' => 'Beginning stock has already been recorded for one or more of these items on this date.',
                ]);
        }

        DB::transaction(function () use (
            $itemsById,
            $enteredQuantities,
            $validated,
            $stockDate
        ) {
            foreach ($enteredQuantities as $itemId => $quantity) {
                $item = $itemsById->get($itemId);
                $now = now();

                $stockInId = DB::table('stock_ins')->insertGetId([
                    'inventory_item_id' => $item->id,
                    'quantity' => $quantity,
                    'supplier_id' => null,
                    'recorded_by' => auth()->id(),
                    'recorded_at' => $stockDate . ' 00:00:00',
                    'remarks' => 'Beginning stock' . (
                        ! empty($validated['remarks'])
                        ? ': ' . $validated['remarks']
                        : ''
                    ),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DailyInventoryCount::create([
                    'inventory_item_id' => $item->id,
                    'stock_date' => $stockDate,
                    'beginning_quantity' => $quantity,
                    'beginning_stock_in_id' => $stockInId,
                    'ending_quantity' => null,
                    'remarks' => $validated['remarks'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('inventory.index', ['location_id' => $locationId])
            ->with('success', 'Beginning stock recorded successfully.');
    }
}
