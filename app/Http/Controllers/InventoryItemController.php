<?php

namespace App\Http\Controllers;

use App\Models\Daily_Inventory_Sheet;
use App\Models\Inventory_Item;
use App\Models\Inventory_Locations;
use App\Models\Inventory_Spoilage;
use App\Models\Inventory_Stock;
use App\Models\Inventory_Transactions;
use App\Models\Supplier;
use App\Models\Supplier_Delivery;
use App\Models\Unit;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryItemController extends Controller
{
    public const TABS = ['sheet', 'stock', 'spoilage', 'suppliers'];

    /** Sections shown on the Daily Sheet, per area, in display order. */
    public const SHEET_LAYOUT = [
        'Bar Area' => ['Powder', 'Sauce', 'Puree', 'Syrup', 'Other'],
        'Kitchen Area' => ['Prepped Food'],
    ];

    public function __construct(private StockMovementService $stockMovement)
    {
    }

    public function index(Request $request)
    {
        $isManager = $this->isManager();

        $tab = $request->query('tab', 'sheet');
        if (! in_array($tab, self::TABS, true) || ($tab === 'suppliers' && ! $isManager)) {
            $tab = 'sheet';
        }

        // Every stock record for real stock items (menu products are not stock).
        $stocks = Inventory_Stock::with(['inventoryItem.unit', 'location'])
            ->whereHas('inventoryItem', fn ($q) => $q->stockable())
            ->get()
            ->sortBy(fn ($s) => [
                $s->inventoryItem->is_active ? 0 : 1,
                strtolower($s->inventoryItem->name),
            ])
            ->values();

        $lowStocks = $stocks->filter(
            fn ($s) => $s->inventoryItem->is_active
                && (float) $s->current_quantity <= (float) $s->reorder_level
        )->values();

        $data = [
            'tab' => $tab,
            'isManager' => $isManager,
            'lowStocks' => $lowStocks,
            'units' => Unit::orderBy('name')->get(),
            'locations' => Inventory_Locations::where('is_active', true)->orderBy('name')->get(),
            'activeItemCount' => $stocks->filter(fn ($s) => $s->inventoryItem->is_active)->count(),
            'lowStockCount' => $lowStocks->count(),
        ];

        return view('inventory.index', $data + match ($tab) {
            'stock' => $this->stockData($stocks),
            'spoilage' => $this->spoilageData(),
            'suppliers' => $this->supplierData($request),
            default => $this->sheetData($request),
        });
    }

    // ── Tab data ──────────────────────────────────────────────────────────────

    private function stockData($stocks): array
    {
        $restocked = Inventory_Transactions::whereIn('transaction_type', ['Stock In', 'Delivery'])
            ->whereIn('inventory_stock_id', $stocks->pluck('id'))
            ->select('inventory_stock_id', DB::raw('MAX(transaction_date) as last_date'))
            ->groupBy('inventory_stock_id')
            ->pluck('last_date', 'inventory_stock_id');

        return [
            'stocks' => $stocks,
            'lastRestocked' => $restocked,
        ];
    }

    private function sheetData(Request $request): array
    {
        $viewing = null;

        if ($request->filled('sheet')) {
            $viewing = Daily_Inventory_Sheet::where('status', Daily_Inventory_Sheet::CLOSED)
                ->find($request->query('sheet'));
        }

        $sheet = $viewing ?? Daily_Inventory_Sheet::current();
        $readOnly = (bool) $viewing;

        $entries = $sheet->entries()
            ->with(['inventoryItem.unit', 'inventoryItem.inventoryStocks.location'])
            ->get()
            ->filter(fn ($e) => $e->inventoryItem && ($readOnly || $e->inventoryItem->is_active))
            ->sortBy(fn ($e) => strtolower($e->inventoryItem->name));

        // area => group => entries
        $areas = [];
        foreach (self::SHEET_LAYOUT as $area => $groups) {
            foreach ($groups as $group) {
                $rows = $entries->filter(
                    fn ($e) => $e->inventoryItem->areaName() === $area
                        && ($e->inventoryItem->sheet_group ?? 'Prepped Food') === $group
                )->values();

                if ($rows->isNotEmpty()) {
                    $areas[$area][$group] = $rows;
                }
            }
        }

        $history = Daily_Inventory_Sheet::where('status', Daily_Inventory_Sheet::CLOSED)
            ->orderByDesc('closed_at')
            ->limit(12)
            ->get();

        return [
            'sheet' => $sheet,
            'sheetReadOnly' => $readOnly,
            'sheetAreas' => $areas,
            'sheetHistory' => $history,
            'sheetTotal' => $entries->count(),
            'sheetFilled' => $entries->filter(fn ($e) => $e->beginning !== null || $e->ending !== null)->count(),
            'sheetBeginningCount' => $entries->filter(fn ($e) => $e->beginning !== null)->count(),
            'sheetTotalOut' => $entries->sum(fn ($e) => $e->out() ?? 0),
        ];
    }

    private function spoilageData(): array
    {
        $items = Inventory_Item::stockable()
            ->where('is_active', true)
            ->with(['unit', 'inventoryStocks'])
            ->orderBy('name')
            ->get();

        $records = Inventory_Spoilage::with(['inventoryItem.unit', 'recordedBy'])
            ->orderByDesc('spoiled_at')
            ->limit(100)
            ->get();

        return [
            'spoilItems' => $items,
            'spoilRecords' => $records,
        ];
    }

    private function supplierData(Request $request): array
    {
        $view = in_array($request->query('sv'), ['list', 'delivery', 'new'], true)
            ? $request->query('sv')
            : 'list';

        $suppliers = Supplier::with('inventoryItems')
            ->withCount('deliveries')
            ->orderBy('name')
            ->get();

        $selected = $request->filled('supplier')
            ? $suppliers->firstWhere('id', (int) $request->query('supplier'))
            : null;

        $selectedDeliveries = $selected
            ? Supplier_Delivery::with('items.inventoryItem.unit')
                ->where('supplier_id', $selected->id)
                ->orderByDesc('delivery_date')
                ->orderByDesc('id')
                ->limit(10)
                ->get()
            : collect();

        return [
            'sv' => $view,
            'suppliers' => $suppliers,
            'selectedSupplier' => $selected,
            'selectedDeliveries' => $selectedDeliveries,
            'supplyItems' => Inventory_Item::stockable()
                ->where('is_active', true)
                ->with('unit')
                ->orderBy('name')
                ->get(),
        ];
    }

    // ── Item settings ─────────────────────────────────────────────────────────

    public function toggleActive(Inventory_Item $inventoryItem)
    {
        DB::transaction(function () use ($inventoryItem) {
            $inventoryItem->is_active = ! $inventoryItem->is_active;
            $inventoryItem->save();
        });

        return redirect()
            ->route('inventory.index', ['tab' => 'stock'])
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
            ->route('inventory.index', ['tab' => 'stock'])
            ->with('success', 'Inventory item unit updated.');
    }

    /** Unit, cost per unit and reorder level, edited from the item modal. */
    public function updateDetails(Request $request, Inventory_Item $inventoryItem)
    {
        $validated = $request->validate([
            'unit_id' => ['nullable', 'exists:units,id'],
            'cost_per_unit' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $inventoryItem) {
            $inventoryItem->unit_id = $validated['unit_id'] ?? null;
            $inventoryItem->cost_per_unit = $validated['cost_per_unit'] ?? 0;
            $inventoryItem->save();

            if (isset($validated['reorder_level'])) {
                $inventoryItem->inventoryStocks()->update([
                    'reorder_level' => $validated['reorder_level'],
                ]);
            }
        });

        return redirect()
            ->route('inventory.index', ['tab' => 'stock'])
            ->with('success', 'Inventory item updated.');
    }

    // ── Stock in / manual correction ──────────────────────────────────────────

    public function createStockIn()
    {
        return redirect()->route('inventory.index', ['tab' => 'stock']);
    }

    public function storeStockIn(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'location_id' => ['required', 'integer', 'exists:inventory_locations,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
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

        if (! $item->unit_id) {
            return back()
                ->withErrors(['inventory_item_id' => 'Please assign a unit to this item before adding stock.'])
                ->withInput();
        }

        $location = Inventory_Locations::where('id', $validated['location_id'])
            ->where('is_active', true)
            ->first();

        if (! $location) {
            return back()
                ->withErrors(['location_id' => 'Please select an active inventory location.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $item) {
            $stock = Inventory_Stock::firstOrCreate(
                [
                    'inventory_item_id' => $item->id,
                    'location_id' => $validated['location_id'],
                ],
                ['current_quantity' => 0, 'reorder_level' => 0]
            );

            $this->stockMovement->move(
                $stock,
                (float) $validated['quantity'],
                'Stock In',
                $validated['reason'] ?? 'Incoming stock',
                isset($validated['unit_cost']) ? (float) $validated['unit_cost'] : null,
            );

            if (isset($validated['unit_cost'])) {
                $item->cost_per_unit = $validated['unit_cost'];
                $item->save();
            }
        });

        return redirect()
            ->route('inventory.index', ['tab' => 'stock'])
            ->with('success', 'Stock added successfully.');
    }

    public function updateStockQuantity(Request $request, Inventory_Stock $stock)
    {
        $validated = $request->validate([
            'current_quantity' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $stock) {
            $locked = Inventory_Stock::whereKey($stock->id)->lockForUpdate()->firstOrFail();
            $difference = round((float) $validated['current_quantity'] - (float) $locked->current_quantity, 3);

            if ($difference == 0.0) {
                return;
            }

            $this->stockMovement->move(
                $locked,
                $difference,
                'Adjustment',
                'Manual stock quantity adjustment',
            );
        });

        return redirect()
            ->route('inventory.index', ['tab' => 'stock'])
            ->with('success', 'Stock quantity updated.');
    }
}
