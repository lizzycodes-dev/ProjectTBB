<?php

namespace App\Http\Controllers;

use App\Models\Inventory_Item;
use App\Models\Inventory_Locations;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Unit;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));
        $categoryId = $request->query('category_id');
        $locationId = $request->query('location_id');

        /*
    |--------------------------------------------------------------------------
    | PREPPED FOOD / COUNTABLE
    |--------------------------------------------------------------------------
    */

        $preppedQuery = Inventory_Item::with([
            'category',
            'inventoryLocation',
            'unit',
        ])
            ->withSum('stockIns as total_stock_in', 'quantity')
            ->withSum('stockOuts as total_stock_out', 'quantity')
            ->where('inventory_type', 'prepped');

        /*
    |--------------------------------------------------------------------------
    | NON-COUNTABLE
    | Ingredient / Puree / Sauce / Powder
    |--------------------------------------------------------------------------
    */

        $nonCountableQuery = Inventory_Item::with([
            'category',
            'inventoryLocation',
            'unit',
        ])
            ->where('inventory_type', 'physical')
            ->whereHas('category', function ($query) {
                $query->whereIn('name', [
                    'Ingredient',
                    'Puree',
                    'Sauce',
                    'Powder',
                ]);
            });

        /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

        if ($search !== '') {

            $preppedQuery->where(
                'name',
                'like',
                "%{$search}%"
            );

            $nonCountableQuery->where(
                'name',
                'like',
                "%{$search}%"
            );
        }

        /*
    |--------------------------------------------------------------------------
    | CATEGORY FILTER
    |--------------------------------------------------------------------------
    */

        if ($categoryId !== null && $categoryId !== '') {

            $preppedQuery->where(
                'category_id',
                $categoryId
            );

            $nonCountableQuery->where(
                'category_id',
                $categoryId
            );
        }

        /*
    |--------------------------------------------------------------------------
    | LOCATION FILTER
    |--------------------------------------------------------------------------
    */

        if ($locationId !== null && $locationId !== '') {

            $preppedQuery->where(
                'inventory_location_id',
                $locationId
            );

            $nonCountableQuery->where(
                'inventory_location_id',
                $locationId
            );
        }

        /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */

        $preppedItems = $preppedQuery
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10, ['*'], 'prepped_page')
            ->withQueryString();

        $nonCountableItems = $nonCountableQuery
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10, ['*'], 'non_countable_page')
            ->withQueryString();


        //modal items
        $coffeeItems = Inventory_Item::with('category')
            ->where('is_active', true)
            ->where('inventory_type', 'physical')
            ->whereHas('category', function ($query) {
                $query->where('name', 'Coffee');
            })
            ->orderBy('name')
            ->get();

        $juiceItems = Inventory_Item::with('category')
            ->where('is_active', true)
            ->where('inventory_type', 'physical')
            ->whereHas('category', function ($query) {
                $query->where('name', 'Juice');
            })
            ->orderBy('name')
            ->get();
        /*
    |--------------------------------------------------------------------------
    | SUMMARY
    |--------------------------------------------------------------------------
    */

        $activeItemCount = Inventory_Item::where('is_active', true)
            ->when(
                $locationId !== null && $locationId !== '',
                function ($query) use ($locationId) {
                    $query->where(
                        'inventory_location_id',
                        $locationId
                    );
                }
            )
            ->count();

        $categories = Category::orderBy('name')->get();

        $locations = Inventory_Locations::orderBy('name')->get();

        $units = Unit::orderBy('name')->get();

        $juiceItems = Inventory_Item::with([
            'category',
            'inventoryLocation',
            'unit',
        ])
            ->where('inventory_type', 'physical')
            ->whereHas('category', function ($query) {
                $query->where('name', 'Juice');
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($categoryId !== null && $categoryId !== '', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($locationId !== null && $locationId !== '', function ($query) use ($locationId) {
                $query->where('inventory_location_id', $locationId);
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10, ['*'], 'juice_page')
            ->withQueryString();


        $coffeeItems = Inventory_Item::with([
            'category',
            'inventoryLocation',
            'unit',
        ])
            ->where('inventory_type', 'physical')
            ->whereHas('category', function ($query) {
                $query->where('name', 'Coffee');
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($categoryId !== null && $categoryId !== '', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($locationId !== null && $locationId !== '', function ($query) use ($locationId) {
                $query->where('inventory_location_id', $locationId);
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10, ['*'], 'coffee_page')
            ->withQueryString();
        /*
        |--------------------------------------------------------------------------
        | MODAL STOCK VALUES
        |--------------------------------------------------------------------------
        */

        $stockDate = now()->toDateString();

        $modalItems = $preppedItems->getCollection()
            ->concat($nonCountableItems->getCollection());

        $beginningQuantities = [];

        foreach ($modalItems as $item) {
            $stockInBefore = DB::table('stock_ins')
                ->where('inventory_item_id', $item->id)
                ->where('recorded_at', '<', $stockDate . ' 00:00:00')
                ->sum('quantity');

            $stockOutBefore = DB::table('stock_outs')
                ->where('inventory_item_id', $item->id)
                ->where('recorded_at', '<', $stockDate . ' 00:00:00')
                ->sum('quantity');

            $beginningQuantities[$item->id] =
                (float) $stockInBefore - (float) $stockOutBefore;
        }
        $inputNewQuantities = DB::table('stock_ins')
            ->whereDate('recorded_at', $stockDate)
            ->whereIn(
                'inventory_item_id',
                $preppedItems->getCollection()->pluck('id')
            )
            ->select(
                'inventory_item_id',
                DB::raw('SUM(quantity) as total_input_new')
            )
            ->groupBy('inventory_item_id')
            ->pluck('total_input_new', 'inventory_item_id');

        $soldQuantities = DB::table('stock_outs')
            ->whereDate('recorded_at', $stockDate)
            ->whereIn(
                'inventory_item_id',
                $preppedItems->getCollection()->pluck('id')
            )
            ->select(
                'inventory_item_id',
                DB::raw('SUM(quantity) as total_sold')
            )
            ->groupBy('inventory_item_id')
            ->pluck('total_sold', 'inventory_item_id');
        return view('inventory.index', compact(
            'preppedItems',
            'nonCountableItems',
            'activeItemCount',
            'categories',
            'locations',
            'units',
            'search',
            'categoryId',
            'locationId',
            'juiceItems',
            'coffeeItems',
            'coffeeItems',
            'juiceItems',
            'beginningQuantities',
            'inputNewQuantities',
            'soldQuantities',
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
    public function storeDailyInventory(Request $request)
    {
        $validated = $request->validate([
            'inventory_type' => [
                'required',
                'string',
                'in:prepped,non-countable,coffee,juice',
            ],

            'stock_date' => [
                'required',
                'date',
            ],

            'input_new' => [
                'nullable',
                'array',
            ],

            'input_new.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'actual_quantity' => [
                'nullable',
                'array',
            ],

            'actual_quantity.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'physical_notes' => [
                'nullable',
                'array',
            ],

            'physical_notes.*' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $type = $validated['inventory_type'];
        $stockDate = $validated['stock_date'];

        /*
    |--------------------------------------------------------------------------
    | PREPPED FOOD
    |--------------------------------------------------------------------------
    |
    | Only Input New is entered manually.
    | Sold comes automatically from stock_outs.
    | Beginning and Ending are calculated from stock transactions.
    |
    */

        if ($type === 'prepped') {

            $items = Inventory_Item::where('is_active', true)
                ->where('inventory_type', 'prepped')
                ->orderBy('name')
                ->get();

            DB::transaction(function () use (
                $items,
                $validated,
                $stockDate
            ) {

                foreach ($items as $item) {

                    $inputNew = (float) (
                        $validated['input_new'][$item->id] ?? 0
                    );

                    if ($inputNew <= 0) {
                        continue;
                    }

                    DB::table('stock_ins')->insert([
                        'inventory_item_id' => $item->id,
                        'quantity' => $inputNew,
                        'supplier_id' => null,
                        'recorded_by' => auth()->id(),
                        'recorded_at' => $stockDate . ' 00:00:00',
                        'remarks' => 'Added through prepped food inventory',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            return back()->with(
                'success',
                'Prepped food stock added successfully.'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | NON-COUNTABLE
    |--------------------------------------------------------------------------
    |
    | These are manually counted items.
    | They are not automatically deducted by POS.
    |
    */

        if ($type === 'non-countable') {

            $items = Inventory_Item::where('is_active', true)
                ->where('inventory_type', 'physical')
                ->whereHas('category', function ($query) {
                    $query->whereIn('name', [
                        'Ingredient',
                        'Puree',
                        'Sauce',
                        'Powder',
                    ]);
                })
                ->orderBy('name')
                ->get();

            DB::transaction(function () use (
                $items,
                $validated,
                $stockDate
            ) {

                foreach ($items as $item) {

                    $actualQuantity =
                        $validated['actual_quantity'][$item->id] ?? null;

                    if ($actualQuantity === null || $actualQuantity === '') {
                        continue;
                    }

                    DB::table('stock_ins')->insert([
                        'inventory_item_id' => $item->id,
                        'quantity' => $actualQuantity,
                        'supplier_id' => null,
                        'recorded_by' => auth()->id(),
                        'recorded_at' => $stockDate . ' 00:00:00',
                        'remarks' =>
                        $validated['physical_notes'][$item->id]
                            ?? 'Manual physical count',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            return back()->with(
                'success',
                'Non-countable inventory updated successfully.'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | COFFEE / JUICE
    |--------------------------------------------------------------------------
    |
    | These are made-to-order items.
    | Their sold quantity is handled by POS.
    |
    */

        if (in_array($type, ['coffee', 'juice'])) {

            return back()->with(
                'success',
                ucfirst($type) . ' sales are automatically tracked from POS.'
            );
        }

        return back()->withErrors([
            'inventory_type' => 'Invalid inventory type.',
        ]);
    }
}
