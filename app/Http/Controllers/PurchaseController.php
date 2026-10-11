<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockIn;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            ->paginate(10, ['*'], 'purchases_page')
            ->withQueryString();

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

        // Expenses tab
        $expenses = Expense::with(['purchase.supplier'])
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'expenses_page')
            ->withQueryString();

        $totalExpenses = Expense::sum('amount');

        // Which tab is active? purchases | expenses
        $tab = request('tab', 'purchases');
        if (! in_array($tab, ['purchases', 'expenses'], true)) {
            $tab = 'purchases';
        }

        // Purchases to optionally link an expense to
        $linkablePurchases = Purchase::with('supplier')
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('purchases.index', compact(
            'purchases',
            'activeSuppliers',
            'allSuppliers',
            'stockIns',
            'purchaseDetails',
            'expenses',
            'totalExpenses',
            'tab',
            'linkablePurchases'
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

            'stock_ins.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'stock_ins.*.unit_cost' => [
                'required',
                'numeric',
                'gt:0',
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

            $stockInIds = collect($validated['stock_ins'])->pluck('id');

            $stockIns = StockIn::with('inventoryItem')
                ->whereIn('id', $stockInIds)
                ->whereDoesntHave('purchaseItem')
                ->get();

            if ($stockIns->count() !== $stockInIds->count()) {
                throw ValidationException::withMessages([
                    'stock_ins' => 'One or more selected stock-in records have already been recorded as a purchase.',
                ]);
            }

            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $totalAmount = 0;
            $processedAny = false;

            foreach ($stockIns as $stockIn) {

                $submittedStock = collect($validated['stock_ins'])
                    ->firstWhere('id', $stockIn->id);

                if (! $submittedStock) {
                    continue;
                }

                $quantity = (float) ($submittedStock['quantity'] ?? 0);
                $unitCost = (float) ($submittedStock['unit_cost'] ?? 0);

                // Both must be > 0 — a checked row with one side at 0 is a user error.
                if ($quantity <= 0 || $unitCost <= 0) {
                    $itemName = $stockIn->inventoryItem->name ?? 'Unknown Item';

                    throw ValidationException::withMessages([
                        'stock_ins' => "Enter a quantity and unit cost greater than 0 for: {$itemName}.",
                    ]);
                }

                $subtotal = $quantity * $unitCost;

                PurchaseItem::create([
                    'purchase_id'      => $purchase->id,
                    'stock_in_id'      => $stockIn->id,
                    'inventory_item_id'=> $stockIn->inventory_item_id,
                    'quantity'         => $quantity,
                    'unit_cost'        => $unitCost,
                    'subtotal'         => $subtotal,
                ]);

                $stockIn->update([
                    'supplier_id' => $validated['supplier_id'],
                ]);

                $totalAmount += $subtotal;
                $processedAny = true;
            }

            if (! $processedAny) {
                throw ValidationException::withMessages([
                    'stock_ins' => 'Please enter a quantity and unit cost for at least one item.',
                ]);
            }

            $purchase->expense()->create([
                'description'  => 'Inventory purchase',
                'category'     => $validated['expense_category'] ?? 'Purchase',
                'amount'       => $totalAmount,
                'expense_date' => $validated['purchase_date'],
                'notes'        => $validated['expense_notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('purchases.index', ['tab' => 'purchases'])
            ->with('success', 'Purchase recorded successfully.');
    }
}