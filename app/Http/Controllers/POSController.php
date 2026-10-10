<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Inventory_Item;

class POSController extends Controller
{
    public function index()
    {
        // Categories that should appear on the POS sidebar (in this order).
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

        $menuItems = Inventory_Item::with([
            'category',
            'optionGroups.optionValues',
        ])
            ->withSum('stockIns as total_stock_in', 'quantity')
            ->withSum('stockOuts as total_stock_out', 'quantity')
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->whereIn('inventory_type', ['prepped', 'physical'])
            ->whereHas('category', function ($query) use ($posCategoryNames) {
                // Only show items that belong to the POS selling categories.
                $query->whereIn('name', $posCategoryNames);
            })
            ->orderBy('name')
            ->get();

        $lowStockThreshold = 5;

        foreach ($menuItems as $menuItem) {
            $stockIn = (float) ($menuItem->total_stock_in ?? 0);
            $stockOut = (float) ($menuItem->total_stock_out ?? 0);

            $currentStock = $stockIn - $stockOut;

            $menuItem->current_stock = $currentStock;

            if ($currentStock <= 0) {
                $menuItem->stock_status = 'out';
            } elseif ($currentStock <= $lowStockThreshold) {
                $menuItem->stock_status = 'low';
            } else {
                $menuItem->stock_status = 'in';
            }
        }

        // Only load categories that are used on the POS sidebar.
        // Keep the same explicit order as $posCategoryNames.
        $categories = Category::where('is_active', true)
            ->whereIn('name', $posCategoryNames)
            ->orderByRaw(
                "FIELD(name, '" . implode("','", $posCategoryNames) . "')"
            )
            ->get();

        return view('pos.index', compact('menuItems', 'categories'));
    }
}
