<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Inventory_Item;

class POSController extends Controller
{
    public function index()
    {
        $menuItems = Inventory_Item::with([
            'category',
            'optionGroups',
        ])
            ->withSum('stockIns as total_stock_in', 'quantity')
            ->withSum('stockOuts as total_stock_out', 'quantity')
            ->where('is_active', true)
            ->whereIn('inventory_type', ['prepped', 'physical'])
            ->whereHas('category', function ($query) {
                $query->whereIn('name', ['Food', 'Coffee', 'Non Coffee', 'Juice']);
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

        $categories = Category::where('is_active', true)
            ->whereNotIn('name', ['Ingredient', 'Puree', 'Sauce', 'Juice'])
            ->orderBy('id')
            ->get();



        return view('pos.index', compact('menuItems', 'categories'));
    }
}
