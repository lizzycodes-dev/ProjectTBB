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
            'optionGroups.optionGroup.optionValues',
        ])
            ->where('is_active', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('name')
            ->get();

        $categories = Category::where('is_active', true)
            ->whereNotIn('name', ['Ingredient', 'Puree', 'Sauce', 'Juice'])
            ->orderBy('id')
            ->get();

        return view('pos.index', compact('menuItems', 'categories'));
    }
}
