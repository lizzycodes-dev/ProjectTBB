<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class FinanceReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['payment', 'cashier', 'orderItems']);

        // --- Filters ---
        if ($request->filled('year')) {
            $query->whereYear('ordered_at', $request->input('year'));
        }

        if ($request->filled('month')) {
            $query->whereMonth('ordered_at', $request->input('month'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('ordered_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('ordered_at', '<=', $request->input('date_to'));
        }

        // --- Totals (Completed orders only, to keep Pending/Ready out of revenue) ---
        $completed = (clone $query)->where('status', 'Completed')->get();

        $totalRevenue = $completed->sum('total_amount');
        $totalDiscounts = $completed->sum('discount_amount');

        $cashSales = $completed
            ->filter(fn ($order) => $order->payment?->payment_method === 'Cash')
            ->sum('total_amount');

        $gcashSales = $completed
            ->filter(fn ($order) => $order->payment?->payment_method === 'GCash')
            ->sum('total_amount');

        // --- Card filter (clicking a summary card filters the table only) ---
        $card = $request->input('card');
        $card = in_array($card, ['cash', 'gcash', 'discounts'], true) ? $card : null;

        $tableQuery = clone $query;

        if ($card !== null) {
            if (! $request->filled('status')) {
                $tableQuery->where('status', 'Completed');
            }

            if ($card === 'cash') {
                $tableQuery->whereHas('payment', fn ($q) => $q->where('payment_method', 'Cash'));
            } elseif ($card === 'gcash') {
                $tableQuery->whereHas('payment', fn ($q) => $q->where('payment_method', 'GCash'));
            } else {
                $tableQuery->where('discount_amount', '>', 0);
            }
        }

        // --- Table rows (all statuses unless a card filter is active, most recent first) ---
        $orders = $tableQuery->orderByDesc('ordered_at')->paginate(10)->withQueryString();
        $years = Order::selectRaw('DISTINCT YEAR(ordered_at) as year')
            ->orderByDesc('year')
            ->pluck('year');

        return view('finances_report.finances_report', compact(
            'orders',
            'totalRevenue',
            'totalDiscounts',
            'cashSales',
            'gcashSales',
            'years',
            'card'
        ));
    }
}