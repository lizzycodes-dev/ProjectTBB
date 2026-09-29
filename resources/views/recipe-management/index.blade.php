<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Page heading --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    Recipe Management
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Select a menu item to view or edit its recipe and option ingredients.
                </p>
            </div>

            {{-- Success message --}}
            @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-100 p-3 text-green-800">
                {{ session('success') }}
            </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-100 p-3 text-red-800">
                <p class="font-semibold">Please check the form:</p>
                <ul class="mt-2 list-inside list-disc text-sm">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Menu Item</th>
                                <th class="px-5 py-3">Base Price</th>
                                <th class="px-5 py-3">Recipe Status</th>
                                <th class="px-5 py-3 text-right">Details</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($menuItems as $menuItem)
                            {{-- Clickable menu item row --}}
                            <tr class="hover:bg-amber-50">
                                <td class="px-5 py-4 font-medium text-gray-800">
                                    {{ $menuItem->name }}
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    ₱{{ number_format($menuItem->base_price, 2) }}
                                </td>

                                <td class="px-5 py-4">
                                    @if ($menuItem->recipeItems->isEmpty())
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
                                        Stock Not Set
                                    </span>
                                    @else
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                        Recipe Set
                                    </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <button
                                        type="button"
                                        class="toggle-recipe rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100"
                                        data-target="recipe-details-{{ $menuItem->id }}"
                                        aria-expanded="false">
                                        <span class="toggle-label">View / Edit</span>
                                        <span class="ml-1 toggle-icon">＋</span>
                                    </button>
                                </td>
                            </tr>

                            {{-- Expandable recipe details row --}}
                            <tr id="recipe-details-{{ $menuItem->id }}" class="hidden bg-gray-50">
                                <td colspan="4" class="px-5 py-5">
                                    <div class="rounded-lg border border-gray-200 bg-white p-4 sm:p-5">

                                        <div class="mb-5">
                                            <h2 class="text-base font-semibold text-gray-800">
                                                {{ $menuItem->name }} — Recipe Editor
                                            </h2>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Quantities are for one serving. Save the base ingredients
                                                and option-specific additions together.
                                            </p>
                                        </div>

                                        <form
                                            action="{{ route('recipe-management.store') }}"
                                            method="POST"
                                            class="recipe-form">
                                            @csrf

                                            <input
                                                type="hidden"
                                                name="menu_item_id"
                                                value="{{ $menuItem->id }}">

                                            {{-- Base recipe ingredients --}}
                                            <div>
                                                <h3 class="mb-1 text-sm font-semibold text-gray-700">
                                                    Recipe Ingredients
                                                </h3>
                                                <p class="mb-4 text-xs text-gray-500">
                                                    Add the inventory items used every time this menu item is prepared.
                                                </p>

                                                <div class="recipe-ingredients space-y-3">
                                                    @forelse ($menuItem->recipeItems as $index => $recipeItem)
                                                    <div class="ingredient-row flex flex-wrap items-end gap-3">
                                                        <div class="min-w-[200px] flex-1">
                                                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                                                Inventory Item
                                                            </label>

                                                            <select
                                                                name="ingredients[{{ $index }}][inventory_item_id]"
                                                                required
                                                                class="w-full rounded-lg border-gray-300 text-sm">
                                                                <option value="">Select inventory item</option>

                                                                @foreach ($inventoryItems as $inventoryItem)
                                                                <option
                                                                    value="{{ $inventoryItem->id }}"
                                                                    @selected($recipeItem->inventory_item_id == $inventoryItem->id)>
                                                                    {{ $inventoryItem->name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="w-36">
                                                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                                                Quantity / serving
                                                            </label>

                                                            <input
                                                                type="number"
                                                                name="ingredients[{{ $index }}][quantity_required]"
                                                                value="{{ $recipeItem->quantity_required }}"
                                                                min="0.001"
                                                                step="0.001"
                                                                required
                                                                class="w-full rounded-lg border-gray-300 text-sm">
                                                        </div>

                                                        <button
                                                            type="button"
                                                            class="remove-ingredient rounded-lg border border-red-300 px-3 py-2 text-sm text-red-700 hover:bg-red-50">
                                                            Remove
                                                        </button>
                                                    </div>
                                                    @empty
                                                    <div class="ingredient-row flex flex-wrap items-end gap-3">
                                                        <div class="min-w-[200px] flex-1">
                                                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                                                Inventory Item
                                                            </label>

                                                            <select
                                                                name="ingredients[0][inventory_item_id]"
                                                                required
                                                                class="w-full rounded-lg border-gray-300 text-sm">
                                                                <option value="">Select inventory item</option>

                                                                @foreach ($inventoryItems as $inventoryItem)
                                                                <option value="{{ $inventoryItem->id }}">
                                                                    {{ $inventoryItem->name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="w-36">
                                                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                                                Quantity / serving
                                                            </label>

                                                            <input
                                                                type="number"
                                                                name="ingredients[0][quantity_required]"
                                                                min="0.001"
                                                                step="0.001"
                                                                required
                                                                class="w-full rounded-lg border-gray-300 text-sm">
                                                        </div>

                                                        <button
                                                            type="button"
                                                            class="remove-ingredient rounded-lg border border-red-300 px-3 py-2 text-sm text-red-700 hover:bg-red-50">
                                                            Remove
                                                        </button>
                                                    </div>
                                                    @endforelse
                                                </div>

                                                <button
                                                    type="button"
                                                    class="add-ingredient mt-4 rounded-lg border border-amber-800 px-3 py-2 text-sm font-medium text-amber-900 hover:bg-amber-50">
                                                    + Add Ingredient
                                                </button>
                                            </div>

                                            {{-- Option-specific ingredients --}}
                                            <div class="mt-6 border-t border-gray-100 pt-5">
                                                <h3 class="mb-1 text-sm font-semibold text-gray-700">
                                                    Ingredients for Selected Options
                                                </h3>

                                                <p class="mb-4 text-xs text-gray-500">
                                                    Add a row only when selecting an option changes the stock used.
                                                    For example, Vanilla flavor may add vanilla syrup.
                                                </p>

                                                <div class="option-adjustments space-y-3">
                                                    @foreach ($menuItem->recipeAdjustments as $index => $adjustment)
                                                    <div class="adjustment-row flex flex-wrap items-end gap-3">
                                                        <div class="min-w-[160px] flex-1">
                                                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                                                Selected Option
                                                            </label>

                                                            <select
                                                                name="adjustments[{{ $index }}][option_value_id]"
                                                                required
                                                                class="w-full rounded-lg border-gray-300 text-sm">
                                                                <option value="">Select option</option>

                                                                @foreach ($menuItem->optionGroups as $optionGroup)
                                                                @foreach ($optionGroup->optionValues as $optionValue)
                                                                <option
                                                                    value="{{ $optionValue->id }}"
                                                                    @selected($adjustment->option_value_id == $optionValue->id)>
                                                                    {{ $optionGroup->name }} — {{ $optionValue->name }}
                                                                </option>
                                                                @endforeach
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="min-w-[160px] flex-1">
                                                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                                                Inventory Item
                                                            </label>

                                                            <select
                                                                name="adjustments[{{ $index }}][inventory_item_id]"
                                                                required
                                                                class="adjustment-inventory w-full rounded-lg border-gray-300 text-sm">
                                                                <option value="">Select inventory item</option>

                                                                @foreach ($inventoryItems as $inventoryItem)
                                                                <option
                                                                    value="{{ $inventoryItem->id }}"
                                                                    data-unit="{{ $inventoryItem->unit?->abbreviation ?? 'Unit not set' }}"
                                                                    @selected($adjustment->inventory_item_id == $inventoryItem->id)>
                                                                    {{ $inventoryItem->name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="w-36">
                                                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                                                Quantity / serving
                                                            </label>

                                                            <input
                                                                type="number"
                                                                name="adjustments[{{ $index }}][quantity_adjustment]"
                                                                value="{{ $adjustment->quantity_adjustment }}"
                                                                min="0.001"
                                                                step="0.001"
                                                                required
                                                                class="w-full rounded-lg border-gray-300 text-sm">
                                                        </div>

                                                        <div class="w-28 pb-2 text-xs text-gray-500">
                                                            <span class="adjustment-unit">
                                                                {{ $adjustment->inventoryItem?->unit?->abbreviation ?? 'Unit not set' }}
                                                            </span>
                                                        </div>

                                                        <button
                                                            type="button"
                                                            class="remove-adjustment rounded-lg border border-red-300 px-3 py-2 text-sm text-red-700 hover:bg-red-50">
                                                            Remove
                                                        </button>
                                                    </div>
                                                    @endforeach
                                                </div>

                                                <template class="adjustment-template">
                                                    <div class="adjustment-row flex flex-wrap items-end gap-3">
                                                        <div class="min-w-[160px] flex-1">
                                                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                                                Selected Option
                                                            </label>

                                                            <select
                                                                name="adjustments[__INDEX__][option_value_id]"
                                                                required
                                                                class="w-full rounded-lg border-gray-300 text-sm">
                                                                <option value="">Select option</option>

                                                                @foreach ($menuItem->optionGroups as $optionGroup)
                                                                @foreach ($optionGroup->optionValues as $optionValue)
                                                                <option value="{{ $optionValue->id }}">
                                                                    {{ $optionGroup->name }} — {{ $optionValue->name }}
                                                                </option>
                                                                @endforeach
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="min-w-[160px] flex-1">
                                                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                                                Inventory Item
                                                            </label>

                                                            <select
                                                                name="adjustments[__INDEX__][inventory_item_id]"
                                                                required
                                                                class="adjustment-inventory w-full rounded-lg border-gray-300 text-sm">
                                                                <option value="">Select inventory item</option>

                                                                @foreach ($inventoryItems as $inventoryItem)
                                                                <option
                                                                    value="{{ $inventoryItem->id }}"
                                                                    data-unit="{{ $inventoryItem->unit?->abbreviation ?? 'Unit not set' }}">
                                                                    {{ $inventoryItem->name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="w-36">
                                                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                                                Quantity / serving
                                                            </label>

                                                            <input
                                                                type="number"
                                                                name="adjustments[__INDEX__][quantity_adjustment]"
                                                                min="0.001"
                                                                step="0.001"
                                                                required
                                                                class="w-full rounded-lg border-gray-300 text-sm">
                                                        </div>

                                                        <div class="w-28 pb-2 text-xs text-gray-500">
                                                            <span class="adjustment-unit">Unit not set</span>
                                                        </div>

                                                        <button
                                                            type="button"
                                                            class="remove-adjustment rounded-lg border border-red-300 px-3 py-2 text-sm text-red-700 hover:bg-red-50">
                                                            Remove
                                                        </button>
                                                    </div>
                                                </template>

                                                <button
                                                    type="button"
                                                    class="add-adjustment mt-4 rounded-lg border border-amber-800 px-3 py-2 text-sm font-medium text-amber-900 hover:bg-amber-50">
                                                    + Add Option Ingredient
                                                </button>
                                            </div>

                                            {{-- Save --}}
                                            <div class="mt-6 flex justify-end border-t border-gray-100 pt-4">
                                                <button
                                                    type="submit"
                                                    class="rounded-lg bg-amber-800 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-900">
                                                    Save Recipe
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-500">
                                    No menu items found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
            <div class="mt-4">
                {{ $menuItems->links() }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Expand or collapse a menu item's recipe editor.
            document.querySelectorAll('.toggle-recipe').forEach(function(button) {
                button.addEventListener('click', function() {
                    const details = document.getElementById(button.dataset.target);
                    const isHidden = details.classList.contains('hidden');

                    details.classList.toggle('hidden', !isHidden);
                    button.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                    button.querySelector('.toggle-label').textContent =
                        isHidden ? 'Hide Details' : 'View / Edit';
                    button.querySelector('.toggle-icon').textContent =
                        isHidden ? '−' : '＋';
                });
            });

            // Set up ingredient and option rows inside each recipe form.
            document.querySelectorAll('.recipe-form').forEach(function(form) {
                const ingredientContainer = form.querySelector('.recipe-ingredients');
                const addIngredientButton = form.querySelector('.add-ingredient');

                function updateIngredientNames() {
                    ingredientContainer.querySelectorAll('.ingredient-row').forEach(function(row, index) {
                        row.querySelector('select').name =
                            `ingredients[${index}][inventory_item_id]`;

                        row.querySelector('input').name =
                            `ingredients[${index}][quantity_required]`;
                    });
                }

                addIngredientButton.addEventListener('click', function() {
                    const firstRow = ingredientContainer.querySelector('.ingredient-row');
                    const newRow = firstRow.cloneNode(true);

                    newRow.querySelector('select').selectedIndex = 0;
                    newRow.querySelector('input').value = '';

                    ingredientContainer.appendChild(newRow);
                    updateIngredientNames();
                });

                ingredientContainer.addEventListener('click', function(event) {
                    if (!event.target.classList.contains('remove-ingredient')) {
                        return;
                    }

                    const rows = ingredientContainer.querySelectorAll('.ingredient-row');

                    if (rows.length > 1) {
                        event.target.closest('.ingredient-row').remove();
                        updateIngredientNames();
                    } else {
                        alert('A recipe must have at least one ingredient row.');
                    }
                });

                updateIngredientNames();

                // Option-specific adjustments.
                const adjustmentContainer = form.querySelector('.option-adjustments');
                const adjustmentTemplate = form.querySelector('.adjustment-template');
                const addAdjustmentButton = form.querySelector('.add-adjustment');

                function updateAdjustmentNames() {
                    adjustmentContainer.querySelectorAll('.adjustment-row').forEach(function(row, index) {
                        row.querySelectorAll('select, input').forEach(function(field) {
                            field.name = field.name.replace(
                                /adjustments\[\d+\]/,
                                `adjustments[${index}]`
                            );
                        });
                    });
                }

                function updateUnit(row) {
                    const select = row.querySelector('.adjustment-inventory');
                    const selectedOption = select.options[select.selectedIndex];
                    const unit = selectedOption?.dataset.unit || 'Unit not set';

                    row.querySelector('.adjustment-unit').textContent = unit;
                }

                addAdjustmentButton.addEventListener('click', function() {
                    const nextIndex =
                        adjustmentContainer.querySelectorAll('.adjustment-row').length;

                    const html = adjustmentTemplate.innerHTML.replaceAll(
                        '__INDEX__',
                        nextIndex
                    );

                    adjustmentContainer.insertAdjacentHTML('beforeend', html);
                    updateAdjustmentNames();
                });

                adjustmentContainer.addEventListener('change', function(event) {
                    if (event.target.classList.contains('adjustment-inventory')) {
                        updateUnit(event.target.closest('.adjustment-row'));
                    }
                });

                adjustmentContainer.addEventListener('click', function(event) {
                    if (!event.target.classList.contains('remove-adjustment')) {
                        return;
                    }

                    event.target.closest('.adjustment-row').remove();
                    updateAdjustmentNames();
                });

                adjustmentContainer.querySelectorAll('.adjustment-row').forEach(updateUnit);
                updateAdjustmentNames();
            });
        });
    </script>
</x-app-layout>