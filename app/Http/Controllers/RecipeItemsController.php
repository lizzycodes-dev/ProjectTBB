<?php

namespace App\Http\Controllers;

use App\Models\Recipe_Items;
use Illuminate\Http\Request;
use App\Models\Menu_Items;
use App\Models\Inventory_Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RecipeItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menuItems = Menu_Items::with([
            'recipeItems.inventoryItem.unit',
            'recipeAdjustments.inventoryItem.unit',
            'optionGroups.optionValues',
        ])
            ->orderBy('name')
            ->paginate(9);

        $inventoryItems = Inventory_Item::with('unit')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('recipe-management.index', compact(
            'menuItems',
            'inventoryItems'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],

            // Ingredients used for every serving
            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.inventory_item_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('inventory_items', 'id')
                    ->where('is_active', true),
            ],
            'ingredients.*.quantity_required' => [
                'required',
                'numeric',
                'gt:0',
            ],

            // Ingredients used only when an option is selected
            'adjustments' => ['nullable', 'array'],
            'adjustments.*.option_value_id' => [
                'required',
                'integer',
                'exists:option_values,id',
            ],
            'adjustments.*.inventory_item_id' => [
                'required',
                'integer',
                Rule::exists('inventory_items', 'id')
                    ->where('is_active', true),
            ],
            'adjustments.*.quantity_adjustment' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);

        $adjustments = $validated['adjustments'] ?? [];

        // Check that every selected stock item has a unit assigned.
        $inventoryIds = collect($validated['ingredients'])
            ->pluck('inventory_item_id')
            ->merge(
                collect($adjustments)->pluck('inventory_item_id')
            )
            ->unique();

        $unitlessItem = Inventory_Item::whereIn('id', $inventoryIds)
            ->whereNull('unit_id')
            ->first();

        if ($unitlessItem) {
            return back()
                ->withInput()
                ->withErrors([
                    'ingredients' => "Please assign a unit to {$unitlessItem->name} in Inventory Management before using it in a recipe.",
                ]);
        }

        DB::transaction(function () use ($validated, $adjustments) {
            // Replace this menu item's base recipe.
            Recipe_Items::where(
                'menu_item_id',
                $validated['menu_item_id']
            )->delete();

            foreach ($validated['ingredients'] as $ingredient) {
                Recipe_Items::create([
                    'menu_item_id' => $validated['menu_item_id'],
                    'inventory_item_id' => $ingredient['inventory_item_id'],
                    'quantity_required' => $ingredient['quantity_required'],
                ]);
            }

            // Replace this menu item's option-specific additions.
            \App\Models\Menu_Option_Recipe_Adjustments::where(
                'menu_item_id',
                $validated['menu_item_id']
            )->delete();

            foreach ($adjustments as $adjustment) {
                \App\Models\Menu_Option_Recipe_Adjustments::create([
                    'menu_item_id' => $validated['menu_item_id'],
                    'option_value_id' => $adjustment['option_value_id'],
                    'inventory_item_id' => $adjustment['inventory_item_id'],
                    'quantity_adjustment' => $adjustment['quantity_adjustment'],
                ]);
            }
        });

        return redirect()
            ->route('recipe-management.index')
            ->with('success', 'Recipe saved successfully.');
    }
    public function storeAdjustments(Request $request)
    {
        $validated = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'adjustments' => ['nullable', 'array'],
            'adjustments.*.option_value_id' => [
                'required',
                'integer',
                'exists:option_values,id',
            ],
            'adjustments.*.inventory_item_id' => [
                'required',
                'integer',
                Rule::exists('inventory_items', 'id')
                    ->where('is_active', true),
            ],
            'adjustments.*.quantity_adjustment' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);

        $itemIds = collect($validated['adjustments'] ?? [])
            ->pluck('inventory_item_id')
            ->unique();

        $unitlessItem = Inventory_Item::whereIn('id', $itemIds)
            ->whereNull('unit_id')
            ->first();

        if ($unitlessItem) {
            return back()
                ->withInput()
                ->withErrors([
                    'adjustments' => "Please assign a unit to {$unitlessItem->name} in Inventory Management first.",
                ]);
        }

        DB::transaction(function () use ($validated) {
            // Replace this menu item's saved option additions.
            \App\Models\Menu_Option_Recipe_Adjustments::where(
                'menu_item_id',
                $validated['menu_item_id']
            )->delete();

            foreach ($validated['adjustments'] ?? [] as $adjustment) {
                \App\Models\Menu_Option_Recipe_Adjustments::create([
                    'menu_item_id' => $validated['menu_item_id'],
                    'option_value_id' => $adjustment['option_value_id'],
                    'inventory_item_id' => $adjustment['inventory_item_id'],
                    'quantity_adjustment' => $adjustment['quantity_adjustment'],
                ]);
            }
        });

        return redirect()
            ->route('recipe-management.index')
            ->with('success', 'Option additions saved successfully.');
    }
    /**
     * Display the specified resource.
     */
    public function show(Recipe_Items $recipe_Items)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Recipe_Items $recipe_Items)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Recipe_Items $recipe_Items)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe_Items $recipe_Items)
    {
        //
    }
}
