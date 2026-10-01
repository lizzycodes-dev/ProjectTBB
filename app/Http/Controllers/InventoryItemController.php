<?php

namespace App\Http\Controllers;

use App\Models\Inventory_Item;
use App\Models\Inventory_Locations;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\DailyInventoryCount;
use App\Models\Unit;

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
            'unit',
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
        $units = Unit::orderBy('name')->get();

        return view('inventory.index', compact(
            'items',
            'activeItemCount',
            'categories',
            'locations',
            'units',
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
        $stockDate = $request->query('stock_date', now()->toDateString());

        $baseQuery = Inventory_Item::with([
            'category',
            'inventoryLocation',
            'unit',
        ])
            ->where('is_active', true);

        if ($locationId !== null && $locationId !== '') {
            $baseQuery->where('inventory_location_id', $locationId);
        }

        $preppedItems = (clone $baseQuery)
            ->where('inventory_type', 'prepped')
            ->orderBy('name')
            ->get();

        $physicalItems = (clone $baseQuery)
            ->where('inventory_type', 'physical')
            ->orderBy('name')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Beginning quantities
    |--------------------------------------------------------------------------
    | Use the most recent saved ending quantity before the selected date.
    */
        $beginningQuantities = [];

        $allItems = $preppedItems->concat($physicalItems);

        foreach ($allItems as $item) {
            $previousCount = DailyInventoryCount::where(
                'inventory_item_id',
                $item->id
            )
                ->where('stock_date', '<', $stockDate)
                ->whereNotNull('ending_quantity')
                ->orderByDesc('stock_date')
                ->first();

            $beginningQuantities[$item->id] =
                $previousCount?->ending_quantity ?? 0;
        }

        /*
    |--------------------------------------------------------------------------
    | Sold quantities
    |--------------------------------------------------------------------------
    | Sold stock comes from POS stock-outs recorded on the selected date.
    */
        $soldQuantities = DB::table('stock_outs')
            ->select(
                'inventory_item_id',
                DB::raw('SUM(quantity) as total_sold')
            )
            ->whereDate('recorded_at', $stockDate)
            ->whereIn(
                'inventory_item_id',
                $preppedItems->pluck('id')
            )
            ->groupBy('inventory_item_id')
            ->pluck('total_sold', 'inventory_item_id');

        $locations = Inventory_Locations::orderBy('name')->get();

        return view('inventory.begin-day', compact(
            'preppedItems',
            'physicalItems',
            'locations',
            'locationId',
            'stockDate',
            'beginningQuantities',
            'soldQuantities'
        ));
    }
    public function storeBeginDay(Request $request)
    {
        $validated = $request->validate([
            'stock_date' => ['required', 'date'],
            'location_id' => [
                'nullable',
                'integer',
                'exists:inventory_locations,id',
            ],

            'input_new' => ['nullable', 'array'],
            'input_new.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'actual_quantity' => ['nullable', 'array'],
            'actual_quantity.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'physical_notes' => ['nullable', 'array'],
            'physical_notes.*' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $stockDate = $validated['stock_date'];
        $locationId = $validated['location_id'] ?? null;

        /*
    |--------------------------------------------------------------------------
    | Get active inventory items in the selected location
    |--------------------------------------------------------------------------
    */
        $items = Inventory_Item::where('is_active', true)
            ->when(
                $locationId !== null,
                function ($query) use ($locationId) {
                    $query->where('inventory_location_id', $locationId);
                }
            )
            ->get();

        if ($items->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'location_id' => 'There are no active inventory items in this area.',
                ]);
        }

        $preppedItems = $items
            ->where('inventory_type', 'prepped');

        $physicalItems = $items
            ->where('inventory_type', 'physical');

        /*
    |--------------------------------------------------------------------------
    | Prevent duplicate daily records
    |--------------------------------------------------------------------------
    */
        $itemIds = $items->pluck('id');

        if (
            DailyInventoryCount::whereDate('stock_date', $stockDate)
            ->whereIn('inventory_item_id', $itemIds)
            ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'stock_date' => 'Daily inventory has already been recorded for one or more items on this date.',
                ]);
        }

        DB::transaction(function () use (
            $validated,
            $stockDate,
            $preppedItems,
            $physicalItems
        ) {

            /*
        |--------------------------------------------------------------------------
        | PREPPED FOOD
        |--------------------------------------------------------------------------
        */

            foreach ($preppedItems as $item) {

                // Get previous day's ending balance.
                $previousCount = DailyInventoryCount::where(
                    'inventory_item_id',
                    $item->id
                )
                    ->where('stock_date', '<', $stockDate)
                    ->whereNotNull('ending_quantity')
                    ->orderByDesc('stock_date')
                    ->first();

                $beginning = (float) (
                    $previousCount?->ending_quantity ?? 0
                );

                // Get total sold through POS on this date.
                $sold = (float) (
                    DB::table('stock_outs')
                    ->where('inventory_item_id', $item->id)
                    ->whereDate('recorded_at', $stockDate)
                    ->sum('quantity')
                );

                // Newly prepared stock entered by the user.
                $inputNew = (float) (
                    $validated['input_new'][$item->id] ?? 0
                );

                // Beginning - Sold + Input New.
                $ending = $beginning - $sold + $inputNew;

                /*
            |--------------------------------------------------------------------------
            | Save newly prepared stock as Stock In
            |--------------------------------------------------------------------------
            */

                $stockInId = null;

                if ($inputNew > 0) {
                    $stockInId = DB::table('stock_ins')->insertGetId([
                        'inventory_item_id' => $item->id,
                        'quantity' => $inputNew,
                        'supplier_id' => null,
                        'recorded_by' => auth()->id(),
                        'recorded_at' => $stockDate . ' 00:00:00',
                        'remarks' => 'Input new - daily inventory',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | Save daily inventory record
            |--------------------------------------------------------------------------
            */

                DailyInventoryCount::create([
                    'inventory_item_id' => $item->id,
                    'stock_date' => $stockDate,
                    'beginning_quantity' => $beginning,
                    'ending_quantity' => $ending,
                    'beginning_stock_in_id' => $stockInId,
                    'remarks' => null,
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | PHYSICAL INVENTORY
        |--------------------------------------------------------------------------
        */

            foreach ($physicalItems as $item) {

                // Get previous day's ending balance.
                $previousCount = DailyInventoryCount::where(
                    'inventory_item_id',
                    $item->id
                )
                    ->where('stock_date', '<', $stockDate)
                    ->whereNotNull('ending_quantity')
                    ->orderByDesc('stock_date')
                    ->first();

                $beginning = (float) (
                    $previousCount?->ending_quantity ?? 0
                );

                // Actual physical count entered by the user.
                $actualQuantity = $validated['actual_quantity'][$item->id] ?? null;

                // If the user did not enter a physical count, skip it.
                if ($actualQuantity === null || $actualQuantity === '') {
                    continue;
                }

                DailyInventoryCount::create([
                    'inventory_item_id' => $item->id,
                    'stock_date' => $stockDate,
                    'beginning_quantity' => $beginning,
                    'ending_quantity' => $actualQuantity,
                    'beginning_stock_in_id' => null,
                    'remarks' => $validated['physical_notes'][$item->id] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('inventory.begin-day', [
                'location_id' => $locationId,
                'stock_date' => $stockDate,
            ])
            ->with('success', 'Daily inventory saved successfully.');
    }

    public function create()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        $locations = Inventory_Locations::orderBy('name')->get();

        $units = Unit::orderBy('name')->get();

        return view('inventory.create', compact(
            'categories',
            'locations',
            'units'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],
            'inventory_location_id' => [
                'required',
                'integer',
                'exists:inventory_locations,id',
            ],
            'unit_id' => [
                'nullable',
                'integer',
                'exists:units,id',
            ],
            'inventory_type' => [
                'nullable',
                'in:prepped,physical',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'description' => [
                'required',
                'string',
            ],
        ]);

        Inventory_Item::create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'inventory_location_id' => $validated['inventory_location_id'],
            'unit_id' => $validated['unit_id'] ?? null,
            'inventory_type' => $validated['inventory_type'] ?? null,
            'price' => $validated['price'],
            'description' => $validated['description'],
            'is_active' => true,
        ]);

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Inventory item added successfully.');
    }

    public function edit(Inventory_Item $inventoryItem)
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        $locations = Inventory_Locations::orderBy('name')->get();

        $units = Unit::orderBy('name')->get();

        return view('inventory.edit', compact(
            'inventoryItem',
            'categories',
            'locations',
            'units'
        ));
    }

    public function update(Request $request, Inventory_Item $inventoryItem)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],
            'inventory_location_id' => [
                'required',
                'integer',
                'exists:inventory_locations,id',
            ],
            'unit_id' => [
                'nullable',
                'integer',
                'exists:units,id',
            ],
            'inventory_type' => [
                'nullable',
                'in:prepped,physical',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'description' => [
                'required',
                'string',
            ],
        ]);

        $inventoryItem->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'inventory_location_id' => $validated['inventory_location_id'],
            'unit_id' => $validated['unit_id'] ?? null,
            'inventory_type' => $validated['inventory_type'] ?? null,
            'price' => $validated['price'],
            'description' => $validated['description'],
        ]);

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Inventory item updated successfully.');
    }
}
