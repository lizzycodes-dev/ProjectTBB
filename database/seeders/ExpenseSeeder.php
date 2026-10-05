<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Purchase;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        if (Expense::exists()) {
            return;
        }

        // 1. One expense per purchase (same as PurchaseController::store).
        $purchases = Purchase::with('purchaseItems')->doesntHave('expense')->get();

        foreach ($purchases as $purchase) {
            $total = $purchase->purchaseItems->sum('subtotal');

            if ($total <= 0) {
                continue;
            }

            $purchase->expense()->create([
                'description' => 'Inventory purchase',
                'category' => 'Purchase',
                'amount' => $total,
                'expense_date' => $purchase->purchase_date,
                'notes' => null,
            ]);
        }

        // 2. Operating expenses over the last 60 days.
        mt_srand(20261007);

        for ($daysAgo = 60; $daysAgo >= 0; $daysAgo--) {
            $date = today()->subDays($daysAgo);

            if ($daysAgo % 14 === 0) {
                $this->add($date, 'Packaging', 'Cups, lids, takeout containers', mt_rand(35, 60) * 10);
            }

            if ($daysAgo % 14 === 7) {
                $this->add($date, 'Supplies', 'Cleaning supplies', mt_rand(15, 25) * 10);
            }

            if ($date->day === 5) {
                $this->add($date, 'Utilities', 'Electricity bill', 1200);
            }

            if ($date->day === 10) {
                $this->add($date, 'Utilities', 'Water bill', 350);
            }

            if ($daysAgo === 20) {
                $this->add($date, 'Maintenance', 'Espresso machine servicing', 750);
            }
        }
    }

    private function add($date, string $category, string $description, int $amount): void
    {
        Expense::create([
            'description' => $description,
            'category' => $category,
            'amount' => $amount,
            'expense_date' => $date->toDateString(),
            'notes' => null,
        ]);
    }
}