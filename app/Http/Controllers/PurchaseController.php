<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockIn;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with([
            'supplier',
            'purchaseItems.inventoryItem.unit',
            'expense',
        ])
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(10);

        $purchaseDetails = $purchases->getCollection()->mapWithKeys(function ($purchase) {
            return [
                $purchase->id => [
                    'date' => \Carbon\Carbon::parse($purchase->purchase_date)->format('M d, Y'),
                    'reference' => $purchase->reference_number ?? '—',
                    'supplier' => $purchase->supplier->name ?? '—',

                    'items' => $purchase->purchaseItems->map(function ($item) {
                        return [
                            'name' => $item->inventoryItem->name ?? 'Unknown Item',
                            'quantity' => $item->quantity,
                            'unit' => $item->inventoryItem->unit->name ?? '',
                            'unit_cost' => $item->unit_cost,
                            'subtotal' => $item->subtotal,
                        ];
                    })->values(),

                    'total' => $purchase->purchaseItems->sum('subtotal'),

                    'expense' => $purchase->expense ? [
                        'category' => $purchase->expense->category ?? 'Purchase',
                        'amount' => $purchase->expense->amount,
                        'notes' => $purchase->expense->notes ?? null,
                    ] : null,

                    'notes' => $purchase->notes,
                ],
            ];
        })->toArray();

        $activeSuppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        $allSuppliers = Supplier::orderBy('name')->get();

        $stockIns = StockIn::with([
            'inventoryItem',
            'supplier',
        ])
            ->whereDoesntHave('purchaseItem')
            ->latest('recorded_at')
            ->latest('id')
            ->get();

        return view('purchases.index', compact(
            'purchases',
            'activeSuppliers',
            'allSuppliers',
            'stockIns',
            'purchaseDetails'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],

            'purchase_date' => [
                'required',
                'date',
            ],

            'reference_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'stock_ins' => [
                'required',
                'array',
                'min:1',
            ],

            'stock_ins.*.id' => [
                'required',
                'integer',
                'exists:stock_ins,id',
            ],

            'stock_ins.*.unit_cost' => [
                'required',
                'numeric',
                'min:0',
            ],

            'expense_category' => [
                'nullable',
                'string',
                'max:255',
            ],

            'expense_notes' => [
                'nullable',
                'string',
            ],
        ]);

        DB::transaction(function () use ($validated) {

            $stockInIds = collect($validated['stock_ins'])
                ->pluck('id');

            $stockIns = StockIn::with('inventoryItem')
                ->whereIn('id', $stockInIds)
                ->whereDoesntHave('purchaseItem')
                ->get();

            if ($stockIns->count() !== $stockInIds->count()) {
                throw new \Exception(
                    'One or more selected stock-in records have already been recorded as a purchase.'
                );
            }

            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $totalAmount = 0;

            foreach ($stockIns as $stockIn) {

                $submittedStock = collect($validated['stock_ins'])
                    ->firstWhere('id', $stockIn->id);

                $unitCost = (float) $submittedStock['unit_cost'];
                $quantity = (float) $stockIn->quantity;
                $subtotal = $quantity * $unitCost;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'stock_in_id' => $stockIn->id,
                    'inventory_item_id' => $stockIn->inventory_item_id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                $stockIn->update([
                    'supplier_id' => $validated['supplier_id'],
                ]);

                $totalAmount += $subtotal;
            }

            $purchase->expense()->create([
                'description' => 'Inventory purchase',
                'category' => $validated['expense_category'] ?? 'Purchase',
                'amount' => $totalAmount,
                'expense_date' => $validated['purchase_date'],
                'notes' => $validated['expense_notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase recorded successfully.');
    }
}
