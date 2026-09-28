<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu_Items;

class POSController extends Controller
{
    public function index()
    {
        $menuItems = Menu_Items::with([
            'category',
            'optionGroups.optionValues',
            'recipeItems.inventoryItem.inventoryStocks',
        ])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = Category::where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('pos.index', compact('menuItems', 'categories'));
    }
}