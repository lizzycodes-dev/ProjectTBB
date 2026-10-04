<?php

namespace App\Http\Controllers;

use App\Models\Inventory_Item;
use App\Models\Order;
use App\Models\Order_Item;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Same low-stock threshold the POS uses (POSController).
        $lowStockThreshold = 5;

        $todayQuery = Order::with(['payment', 'orderItems'])
            ->whereDate('ordered_at', today());

        // Completed orders only, same rule as the Finance Report.
        $completed = (clone $todayQuery)->where('status', 'Completed')->get();

        $dailyRevenue = $completed->sum('total_amount');
        $discountsGiven = $completed->sum('discount_amount');

        $cashSales = $completed
            ->filter(fn ($order) => $order->payment?->payment_method === 'Cash')
            ->sum('total_amount');

        $gcashSales = $completed
            ->filter(fn ($order) => $order->payment?->payment_method === 'GCash')
            ->sum('total_amount');

        $avgOrderValue = $completed->count() > 0
            ? $dailyRevenue / $completed->count()
            : 0;

        $orders = $todayQuery->orderByDesc('ordered_at')->paginate(10)->withQueryString();

        $stockItems = Inventory_Item::with('unit')
            ->withSum('stockIns as total_stock_in', 'quantity')
            ->withSum('stockOuts as total_stock_out', 'quantity')
            ->where('is_active', true)
            ->where('inventory_type', 'prepped')
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                $item->current_stock = (float) ($item->total_stock_in ?? 0)
                    - (float) ($item->total_stock_out ?? 0);

                return $item;
            });

        $lowStockItems = $stockItems
            ->filter(fn ($item) => $item->current_stock <= $lowStockThreshold)
            ->sortBy('current_stock')
            ->values();

        $currentInventory = $stockItems->take(5);

        $bestSellers = Order_Item::query()
            ->selectRaw('inventory_item_id, SUM(quantity) as total_sold')
            ->whereHas('order', fn ($query) => $query->where('status', 'Completed'))
            ->groupBy('inventory_item_id')
            ->orderByDesc('total_sold')
            ->orderBy('inventory_item_id')
            ->limit(5)
            ->with('InventoryItem')
            ->get();

        return view('dashboard', compact(
            'dailyRevenue',
            'cashSales',
            'gcashSales',
            'discountsGiven',
            'avgOrderValue',
            'orders',
            'lowStockThreshold',
            'lowStockItems',
            'currentInventory',
            'bestSellers'
        ));
    }
}