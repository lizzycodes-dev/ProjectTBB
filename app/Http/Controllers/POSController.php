<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;

class POSController extends Controller
{
    public function index()
    {
        $menuItems = Item::with([
            'category',
            'optionGroups.optionValues',
        ])
            ->where('is_sellable', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = Category::where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('pos.index', compact('menuItems', 'categories'));
    }
}
