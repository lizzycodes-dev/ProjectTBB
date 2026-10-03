<?php

namespace App\Http\Controllers;

use App\Models\Daily_Inventory_Sheet;
use App\Models\Daily_Inventory_Sheet_Entry;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Daily Inventory Sheet: count Beginning -> Save -> count Ending -> Close Day.
 * Every button on the sheet posts the whole form, so nothing typed is lost.
 */
class DailySheetController extends Controller
{
    public function __construct(private StockMovementService $stockMovement)
    {
    }

    /** Copy Current Stock into Beginning. */
    public function useCurrentStock(Request $request, Daily_Inventory_Sheet $sheet)
    {
        if ($sheet->status !== Daily_Inventory_Sheet::OPEN) {
            return $this->back()->withErrors(['sheet' => 'Beginning counts are already saved.']);
        }

        DB::transaction(function () use ($request, $sheet) {
            $this->persist($request, $sheet);

            $sheet->entries()->with('inventoryItem')->get()->each(function ($entry) {
                if (! $entry->inventoryItem || ! $entry->inventoryItem->is_active) {
                    return;
                }

                $entry->beginning = (float) $entry->inventoryItem->primaryStock()->current_quantity;
                $entry->save();
            });
        });

        return $this->back()->with(
            'success',
            'Current stock was copied into Beginning. Review the counts, then save.'
        );
    }

    public function saveBeginning(Request $request, Daily_Inventory_Sheet $sheet)
    {
        if ($sheet->status !== Daily_Inventory_Sheet::OPEN) {
            return $this->back()->withErrors(['sheet' => 'Beginning counts are already saved.']);
        }

        $missing = false;

        DB::transaction(function () use ($request, $sheet, &$missing) {
            $this->persist($request, $sheet);

            $missing = $this->activeEntries($sheet)->contains(fn ($e) => $e->beginning === null);

            if (! $missing) {
                $sheet->status = Daily_Inventory_Sheet::BEGINNING_SAVED;
                $sheet->beginning_saved_at = now();
                $sheet->save();
            }
        });

        if ($missing) {
            return $this->back()->withErrors([
                'sheet' => 'Enter a beginning count for every Bar and Kitchen item before saving.',
            ]);
        }

        return $this->back()->with(
            'success',
            'Beginning inventory saved. Ending counts are now ready for close time.'
        );
    }

    /** Save ending counts / remarks. Stock is deducted as each Ending is entered. */
    public function saveEnding(Request $request, Daily_Inventory_Sheet $sheet)
    {
        if ($sheet->status !== Daily_Inventory_Sheet::BEGINNING_SAVED) {
            return $this->back()->withErrors(['sheet' => 'Save the Beginning counts first.']);
        }

        DB::transaction(fn () => $this->persist($request, $sheet));

        return $this->back()->with('success', 'Ending counts saved.');
    }

    public function close(Request $request, Daily_Inventory_Sheet $sheet)
    {
        if ($sheet->status !== Daily_Inventory_Sheet::BEGINNING_SAVED) {
            return $this->back()->withErrors([
                'sheet' => 'Complete and save Beginning first, then enter every Ending count before closing the day.',
            ]);
        }

        $incomplete = false;

        DB::transaction(function () use ($request, $sheet, &$incomplete) {
            $this->persist($request, $sheet);

            $entries = $this->activeEntries($sheet);
            $incomplete = $entries->contains(fn ($e) => $e->ending === null);

            if ($incomplete) {
                return;
            }

            // Stock ends the day exactly at the counted Ending.
            foreach ($entries as $entry) {
                $stock = $entry->inventoryItem->primaryStock();
                $difference = round($entry->ending - (float) $stock->current_quantity, 3);

                if ($difference != 0.0) {
                    $this->stockMovement->move(
                        $stock,
                        $difference,
                        'Adjustment',
                        'Daily sheet closing count',
                        null,
                        null,
                        'Daily_Inventory_Sheet',
                        $sheet->id,
                    );
                }
            }

            $sheet->status = Daily_Inventory_Sheet::CLOSED;
            $sheet->closed_at = now();
            $sheet->closed_by = auth()->id();
            $sheet->save();
        });

        if ($incomplete) {
            return $this->back()->withErrors([
                'sheet' => 'Enter every Ending count before closing the day.',
            ]);
        }

        return redirect()
            ->route('inventory.index', ['tab' => 'sheet'])
            ->with('success', 'Day closed and saved. The sheet is ready for the next opening count.');
    }

    /** Throw the day away: put back whatever was deducted and start over. */
    public function reset(Daily_Inventory_Sheet $sheet)
    {
        if ($sheet->isClosed()) {
            return $this->back()->withErrors(['sheet' => 'A closed sheet cannot be reset.']);
        }

        DB::transaction(function () use ($sheet) {
            $sheet->entries()->with('inventoryItem')->get()->each(function ($entry) use ($sheet) {
                if ($entry->applied_out != 0.0 && $entry->inventoryItem) {
                    $this->stockMovement->move(
                        $entry->inventoryItem->primaryStock(),
                        (float) $entry->applied_out,
                        'Adjustment',
                        'Daily sheet reset',
                        null,
                        null,
                        'Daily_Inventory_Sheet',
                        $sheet->id,
                    );
                }

                $entry->update([
                    'beginning' => null,
                    'ending' => null,
                    'applied_out' => 0,
                    'remarks' => null,
                ]);
            });

            $sheet->update([
                'status' => Daily_Inventory_Sheet::OPEN,
                'beginning_saved_at' => null,
            ]);
        });

        return $this->back()->with('success', 'Daily sheet reset. Enter a new beginning inventory.');
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function back()
    {
        return redirect()->route('inventory.index', ['tab' => 'sheet']);
    }

    private function activeEntries(Daily_Inventory_Sheet $sheet)
    {
        return $sheet->entries()
            ->with('inventoryItem')
            ->get()
            ->filter(fn ($e) => $e->inventoryItem && $e->inventoryItem->is_active);
    }

    /**
     * Save what was typed. Beginning is only editable before it is saved,
     * Ending only after. Remarks are always editable until the day closes.
     */
    private function persist(Request $request, Daily_Inventory_Sheet $sheet): void
    {
        $request->validate([
            'entries' => ['nullable', 'array'],
            'entries.*.beginning' => ['nullable', 'numeric', 'min:0'],
            'entries.*.ending' => ['nullable', 'numeric', 'min:0'],
            'entries.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $submitted = $request->input('entries', []);
        $status = $sheet->status;

        $entries = $sheet->entries()->with('inventoryItem')->get()->keyBy('inventory_item_id');

        foreach ($submitted as $itemId => $row) {
            /** @var Daily_Inventory_Sheet_Entry|null $entry */
            $entry = $entries->get((int) $itemId);

            if (! $entry) {
                continue;
            }

            $entry->remarks = isset($row['remarks']) && $row['remarks'] !== '' ? $row['remarks'] : null;

            if ($status === Daily_Inventory_Sheet::OPEN && array_key_exists('beginning', $row)) {
                $entry->beginning = $row['beginning'] === null || $row['beginning'] === ''
                    ? null
                    : (float) $row['beginning'];
            }

            if ($status === Daily_Inventory_Sheet::BEGINNING_SAVED && array_key_exists('ending', $row)) {
                $entry->ending = $row['ending'] === null || $row['ending'] === ''
                    ? null
                    : (float) $row['ending'];

                $this->applyOut($entry, $sheet);
            }

            $entry->save();
        }
    }

    /** Deduct (or give back) stock so it always matches Beginning - Ending. */
    private function applyOut(Daily_Inventory_Sheet_Entry $entry, Daily_Inventory_Sheet $sheet): void
    {
        $newOut = ($entry->beginning !== null && $entry->ending !== null)
            ? round($entry->beginning - $entry->ending, 3)
            : 0.0;

        $delta = round($newOut - $entry->applied_out, 3);

        if ($delta == 0.0 || ! $entry->inventoryItem) {
            return;
        }

        $moved = $this->stockMovement->move(
            $entry->inventoryItem->primaryStock(),
            -$delta,
            'Daily Sheet',
            'Daily sheet usage',
            null,
            null,
            'Daily_Inventory_Sheet',
            $sheet->id,
        );

        // Track what was really taken out (stock never goes below zero).
        $entry->applied_out = round($entry->applied_out - $moved, 3);
    }
}
