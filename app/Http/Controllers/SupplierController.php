<?php

namespace App\Http\Controllers;

use App\Models\Inventory_Item;
use App\Models\Supplier;
use App\Models\Supplier_Delivery;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Suppliers tab of the inventory page (manager only).
 */
class SupplierController extends Controller
{
    public function __construct(private StockMovementService $stockMovement)
    {
    }

    public function store(Request $request)
    {
        $this->requireManager();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'delivery_frequency' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array'],
            'items.*' => ['integer', 'exists:inventory_items,id'],
        ]);

        $supplier = DB::transaction(function () use ($validated) {
            $supplier = Supplier::create([
                'name' => $validated['name'],
                'contact_person' => $validated['contact_person'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'delivery_frequency' => $validated['delivery_frequency'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'is_active' => true,
            ]);

            $supplier->inventoryItems()->sync($validated['items'] ?? []);

            return $supplier;
        });

        return redirect()
            ->route('inventory.index', ['tab' => 'suppliers', 'supplier' => $supplier->id])
            ->with('success', 'Supplier added.');
    }

    /** Record a delivery: adds every line to stock and remembers the cost. */
    public function storeDelivery(Request $request)
    {
        $this->requireManager();

        $validated = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'delivery_date' => ['required', 'date'],
            'received_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'lines.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Ignore the blank rows the form always leaves at the bottom.
        $lines = collect($validated['lines'])
            ->filter(fn ($l) => ! empty($l['inventory_item_id']) && (float) ($l['quantity'] ?? 0) > 0)
            ->values();

        if ($lines->isEmpty()) {
            return back()
                ->withErrors(['lines' => 'Add at least one item with a quantity greater than 0.'])
                ->withInput();
        }

        $items = Inventory_Item::stockable()
            ->where('is_active', true)
            ->whereIn('id', $lines->pluck('inventory_item_id'))
            ->get()
            ->keyBy('id');

        if ($items->count() !== $lines->pluck('inventory_item_id')->unique()->count()) {
            return back()
                ->withErrors(['lines' => 'Every delivered item must be an active inventory item.'])
                ->withInput();
        }

        $supplier = Supplier::findOrFail($validated['supplier_id']);

        DB::transaction(function () use ($validated, $lines, $items, $supplier) {
            $delivery = Supplier_Delivery::create([
                'supplier_id' => $supplier->id,
                'recorded_by' => auth()->id(),
                'received_by' => $validated['received_by'] ?? null,
                'delivery_date' => $validated['delivery_date'],
                'total_cost' => 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            $total = 0;

            foreach ($lines as $line) {
                $item = $items[(int) $line['inventory_item_id']];
                $qty = (float) $line['quantity'];
                $cost = isset($line['unit_cost']) && $line['unit_cost'] !== ''
                    ? (float) $line['unit_cost']
                    : (float) $item->cost_per_unit;

                $delivery->items()->create([
                    'inventory_item_id' => $item->id,
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                ]);

                $this->stockMovement->move(
                    $item->primaryStock(),
                    $qty,
                    'Delivery',
                    'Delivery from ' . $supplier->name,
                    $cost,
                    $supplier->id,
                    'Supplier_Delivery',
                    $delivery->id,
                );

                $item->cost_per_unit = $cost;
                $item->save();

                // Remember that this supplier provides this item.
                $supplier->inventoryItems()->syncWithoutDetaching([$item->id]);

                $total += $qty * $cost;
            }

            $delivery->update(['total_cost' => round($total, 2)]);

            if (! $supplier->last_delivery_date || $supplier->last_delivery_date->lte($validated['delivery_date'])) {
                $supplier->update(['last_delivery_date' => $validated['delivery_date']]);
            }
        });

        return redirect()
            ->route('inventory.index', ['tab' => 'suppliers', 'supplier' => $supplier->id])
            ->with('success', 'Delivery recorded and stock updated.');
    }
}
