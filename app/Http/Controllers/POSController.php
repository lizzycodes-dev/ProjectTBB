<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Inventory_Item;
use Illuminate\Http\Request;

class POSController extends Controller
{
    public function index()
    {
        $posCategoryNames = [
            'Coffee',
            'Non Coffee',
            'Frappe Ice Cream on Top',
            'Smoothies Ice Cream on Top',
            'Popping Boba Pearls',
            'Oreo Milk Series',
            'Fizzy Coolers',
            'Buy 1 Take 1 Smoothies',
            'Soda Fruit Jelly Buy 1 Take 1',
            'Fruit Juice Pitcher',
            'Rice Meals',
            'Rice Toppings',
            'Snack Meals',
        ];

        $menuItems = Inventory_Item::query()
            ->with([
                'category',
                'optionGroups.optionValues',
            ])
            ->withSum('stockIns as total_stock_in', 'quantity')
            ->withSum('stockOuts as total_stock_out', 'quantity')
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->whereIn('inventory_type', ['prepped', 'physical'])
            ->whereHas('category', function ($query) use ($posCategoryNames) {
                $query->whereIn('name', $posCategoryNames);
            })
            ->orderBy('name')
            ->get();

        $lowStockThreshold = 5;

        foreach ($menuItems as $menuItem) {
            $stockIn = (float) ($menuItem->total_stock_in ?? 0);
            $stockOut = (float) ($menuItem->total_stock_out ?? 0);

            $currentStock = max(0, $stockIn - $stockOut);

            $menuItem->current_stock = $currentStock;

            $menuItem->stock_status = match (true) {
                $currentStock <= 0 => 'out',
                $currentStock <= $lowStockThreshold => 'low',
                default => 'in',
            };
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->whereIn('name', $posCategoryNames)
            ->orderByRaw(
                "FIELD(name, '" . implode("','", $posCategoryNames) . "')"
            )
            ->get();

        return view('pos.index', compact('menuItems', 'categories'));
    }
    public function readyOrders(Request $request)
    {
        // Optional: accept a "since" timestamp so we only return new ones.
        $since = $request->query('since');

        $query = \App\Models\Order::query()
            ->where('status', 'Ready')
            ->orderByDesc('updated_at');

        if ($since) {
            $query->where('updated_at', '>', $since);
        }

        $orders = $query->limit(20)->get([
            'id',
            'order_number',
            'queue_number',
            'queue_date',
            'updated_at',
        ]);

        return response()->json([
            'now'    => now()->toDateTimeString(),
            'orders' => $orders->map(fn($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'queue_number' => $o->queue_number,
                'queue_label'  => $o->queue_label ?? ('#' . $o->queue_number),
                'updated_at'   => $o->updated_at->toDateTimeString(),
            ]),
        ]);
    }
}
