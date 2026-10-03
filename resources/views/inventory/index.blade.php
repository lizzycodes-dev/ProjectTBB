<x-app-layout>

    <div class="inventory-page">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <div class="inventory-header">

            <div>
                <h1>Manage Inventory Item Information</h1>
                <p>Manage inventory items and monitor their stock classification.</p>
            </div>

            <button
                type="button"
                class="inventory-new-button"
                id="openCreateModal">
                <span class="new-button-icon">+</span>
                <span>New Item</span>
            </button>

        </div>


        {{-- ========================================================= --}}
        {{-- MAIN LAYOUT: LEFT CONTENT + RIGHT SUMMARY --}}
        {{-- ========================================================= --}}

        <div class="inventory-layout">

            {{-- ======================== LEFT COLUMN ======================== --}}
            <div class="inventory-main">

                {{-- TABS --}}
                <div class="inventory-tabs" role="tablist">

                    <button
                        type="button"
                        class="inventory-tab {{ request()->has('prepped_page') || (!request()->hasAny(['coffee_page', 'juice_page', 'non_countable_page'])) ? 'is-active' : '' }}"
                        role="tab"
                        data-tab="prepped"
                        aria-selected="{{ request()->has('prepped_page') || (!request()->hasAny(['coffee_page', 'juice_page', 'non_countable_page'])) ? 'true' : 'false' }}">
                        Prepped
                    </button>

                    <button
                        type="button"
                        class="inventory-tab {{ request()->has('coffee_page') ? 'is-active' : '' }}"
                        role="tab"
                        data-tab="coffee"
                        aria-selected="{{ request()->has('coffee_page') ? 'true' : 'false' }}">
                        Coffee
                    </button>

                    <button
                        type="button"
                        class="inventory-tab {{ request()->has('juice_page') ? 'is-active' : '' }}"
                        role="tab"
                        data-tab="juice"
                        aria-selected="{{ request()->has('juice_page') ? 'true' : 'false' }}">
                        Juice
                    </button>

                    <button
                        type="button"
                        class="inventory-tab {{ request()->has('non_countable_page') ? 'is-active' : '' }}"
                        role="tab"
                        data-tab="non-countable"
                        aria-selected="{{ request()->has('non_countable_page') ? 'true' : 'false' }}">
                        Non countable
                    </button>

                </div>


                {{-- SEARCH / FILTER --}}
                <form
                    method="GET"
                    action="{{ route('inventory.index') }}"
                    class="inventory-search"
                    id="inventoryFilterForm">

                    <input
                        type="search"
                        id="inventorySearch"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by item name..."
                        aria-label="Search inventory items"
                        autocomplete="off">

                    <select
                        name="location_id"
                        onchange="this.form.submit()">
                        <option value="">All Areas</option>
                        @foreach ($locations as $location)
                        <option
                            value="{{ $location->id }}"
                            @selected((string) $locationId===(string) $location->id)>
                            {{ $location->name }}
                        </option>
                        @endforeach
                    </select>

                    <select
                        name="category_id"
                        onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected((string) $categoryId===(string) $category->id)>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>

                    @if (
                    $search !== '' ||
                    ($categoryId !== null && $categoryId !== '') ||
                    ($locationId !== null && $locationId !== '')
                    )
                    <a href="{{ route('inventory.index') }}" class="inventory-clear">
                        Clear
                    </a>
                    @endif

                </form>


                {{-- SUCCESS --}}
                @if (session('success'))
                <div class="inventory-alert">
                    {{ session('success') }}
                </div>
                @endif


                {{-- ERRORS --}}
                @if ($errors->any())
                <div class="inventory-error">
                    <strong>Please check the following:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif


                {{-- ===================================================== --}}
                {{-- TAB PANELS --}}
                {{-- ===================================================== --}}

                {{-- PANEL: PREPPED --}}
                <div
                    class="inventory-tab-panel is-active"
                    id="panel-prepped"
                    role="tabpanel"
                    data-panel="prepped">

                    <div class="inventory-table-section">

                        <div class="inventory-table-title">
                            <div>
                                <h3>Prepped Food</h3>
                                <p>Countable inventory prepared in advance.</p>
                                <button
                                    type="button"
                                    class="inventory-settings-button"
                                    data-settings-type="prepped"
                                    title="Daily stock settings">
                                    ⚙
                                </button>

                            </div>
                            <span class="table-type-badge countable">Countable</span>
                        </div>

                        <div class="inventory-table-wrapper">
                            <table class="inventory-table">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Unit</th>
                                        <th>Current Stock</th>
                                        <th>Item Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($preppedItems as $item)
                                    @php
                                    $stockIn = (float) ($item->total_stock_in ?? 0);
                                    $stockOut = (float) ($item->total_stock_out ?? 0);
                                    $currentStock = $stockIn - $stockOut;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong class="item-name">{{ $item->name }}</strong>
                                        </td>
                                        <td>{{ $item->category?->name ?? '—' }}</td>
                                        <td>{{ $item->inventoryLocation?->name ?? '—' }}</td>
                                        <td>
                                            @if ($item->unit)
                                            <span class="unit-badge">{{ $item->unit->abbreviation }}</span>
                                            @else
                                            <span class="no-value">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="stock-number">{{ number_format($currentStock, 3) }}</strong>
                                        </td>
                                        <td>
                                            @if ($item->is_active)
                                            <span class="item-status active">Active</span>
                                            @else
                                            <span class="item-status inactive">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button
                                                    type="button"
                                                    class="edit-inventory-button"
                                                    data-id="{{ $item->id }}"
                                                    data-name="{{ $item->name }}"
                                                    data-category="{{ $item->category_id }}"
                                                    data-location="{{ $item->inventory_location_id }}"
                                                    data-unit="{{ $item->unit_id }}"
                                                    data-type="{{ $item->inventory_type }}"
                                                    data-price="{{ $item->price }}"
                                                    data-description="{{ $item->description }}">
                                                    Edit
                                                </button>
                                                <form
                                                    method="POST"
                                                    action="{{ route('inventory.toggle-active', $item) }}"
                                                    onsubmit="return confirm('Are you sure you want to {{ $item->is_active ? 'deactivate' : 'activate' }} this inventory item?');">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="toggle-button {{ $item->is_active ? 'deactivate' : 'activate' }}">
                                                        {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            No prepped food items found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($preppedItems->hasPages())
                        <div class="inventory-pagination">
                            {{ $preppedItems->links() }}
                        </div>
                        @endif

                    </div>
                </div>


                {{-- PANEL: COFFEE --}}
                <div
                    class="inventory-tab-panel"
                    id="panel-coffee"
                    role="tabpanel"
                    data-panel="coffee"
                    hidden>

                    <div class="inventory-table-section">

                        <div class="inventory-table-title">
                            <div>
                                <h3>Coffee</h3>
                                <p>Coffee drinks prepared when ordered.</p>
                                <button
                                    type="button"
                                    class="inventory-settings-button"
                                    data-settings-type="coffee"
                                    title="Daily sales settings">
                                    ⚙
                                </button>
                            </div>
                            <span class="table-type-badge made-to-order">Made to Order</span>
                        </div>

                        <div class="inventory-table-wrapper">
                            <table class="inventory-table">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Selling Price</th>
                                        <th>Stock Monitoring</th>
                                        <th>Item Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($coffeeItems as $item)
                                    <tr>
                                        <td>
                                            <strong class="item-name">{{ $item->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="category-badge">{{ $item->category?->name ?? 'Coffee' }}</span>
                                        </td>
                                        <td>{{ $item->inventoryLocation?->name ?? '—' }}</td>
                                        <td>
                                            <strong class="price-value">₱{{ number_format($item->price, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="manual-stock-badge">Made to Order</span>
                                        </td>
                                        <td>
                                            @if ($item->is_active)
                                            <span class="item-status active">Active</span>
                                            @else
                                            <span class="item-status inactive">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button
                                                    type="button"
                                                    class="edit-inventory-button"
                                                    data-id="{{ $item->id }}"
                                                    data-name="{{ $item->name }}"
                                                    data-category="{{ $item->category_id }}"
                                                    data-location="{{ $item->inventory_location_id }}"
                                                    data-unit="{{ $item->unit_id }}"
                                                    data-type="{{ $item->inventory_type }}"
                                                    data-price="{{ $item->price }}"
                                                    data-description="{{ $item->description }}">
                                                    Edit
                                                </button>
                                                <form
                                                    method="POST"
                                                    action="{{ route('inventory.toggle-active', $item) }}"
                                                    onsubmit="return confirm('Are you sure you want to {{ $item->is_active ? 'deactivate' : 'activate' }} this inventory item?');">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="toggle-button {{ $item->is_active ? 'deactivate' : 'activate' }}">
                                                        {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            No coffee items found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($coffeeItems->hasPages())
                        <div class="inventory-pagination">
                            {{ $coffeeItems->links() }}
                        </div>
                        @endif

                    </div>
                </div>


                {{-- PANEL: JUICE --}}
                <div
                    class="inventory-tab-panel"
                    id="panel-juice"
                    role="tabpanel"
                    data-panel="juice"
                    hidden>

                    <div class="inventory-table-section">

                        <div class="inventory-table-title">
                            <div>
                                <h3>Juice</h3>
                                <p>Juice drinks prepared when ordered.</p>
                                <button
                                    type="button"
                                    class="inventory-settings-button"
                                    data-settings-type="juice"
                                    title="Daily sales settings">
                                    ⚙
                                </button>
                            </div>
                            <span class="table-type-badge made-to-order">Made to Order</span>
                        </div>

                        <div class="inventory-table-wrapper">
                            <table class="inventory-table">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Selling Price</th>
                                        <th>Stock Monitoring</th>
                                        <th>Item Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($juiceItems as $item)
                                    <tr>
                                        <td>
                                            <strong class="item-name">{{ $item->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="category-badge">{{ $item->category?->name ?? 'Juice' }}</span>
                                        </td>
                                        <td>{{ $item->inventoryLocation?->name ?? '—' }}</td>
                                        <td>
                                            <strong class="price-value">₱{{ number_format($item->price, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="manual-stock-badge">Made to Order</span>
                                        </td>
                                        <td>
                                            @if ($item->is_active)
                                            <span class="item-status active">Active</span>
                                            @else
                                            <span class="item-status inactive">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button
                                                    type="button"
                                                    class="edit-inventory-button"
                                                    data-id="{{ $item->id }}"
                                                    data-name="{{ $item->name }}"
                                                    data-category="{{ $item->category_id }}"
                                                    data-location="{{ $item->inventory_location_id }}"
                                                    data-unit="{{ $item->unit_id }}"
                                                    data-type="{{ $item->inventory_type }}"
                                                    data-price="{{ $item->price }}"
                                                    data-description="{{ $item->description }}">
                                                    Edit
                                                </button>
                                                <form
                                                    method="POST"
                                                    action="{{ route('inventory.toggle-active', $item) }}"
                                                    onsubmit="return confirm('Are you sure you want to {{ $item->is_active ? 'deactivate' : 'activate' }} this inventory item?');">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="toggle-button {{ $item->is_active ? 'deactivate' : 'activate' }}">
                                                        {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            No juice items found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($juiceItems->hasPages())
                        <div class="inventory-pagination">
                            {{ $juiceItems->links() }}
                        </div>
                        @endif

                    </div>
                </div>


                {{-- PANEL: NON-COUNTABLE --}}
                <div
                    class="inventory-tab-panel"
                    id="panel-non-countable"
                    role="tabpanel"
                    data-panel="non-countable"
                    hidden>

                    <div class="inventory-table-section non-countable-section">

                        <div class="inventory-table-title">
                            <div>
                                <h3>Non-Countable Inventory</h3>
                                <p>Ingredients and supplies that are manually monitored.</p>
                                <button
                                    type="button"
                                    class="inventory-settings-button"
                                    data-settings-type="non-countable"
                                    title="Manual inventory settings">
                                    ⚙
                                </button>
                            </div>
                            <span class="table-type-badge non-countable">Non-Countable</span>
                        </div>

                        <div class="inventory-category-note">
                            <span>Categories:</span>
                            Ingredient · Puree · Sauce · Powder
                        </div>

                        <div class="inventory-table-wrapper">
                            <table class="inventory-table non-countable-table">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Unit</th>
                                        <th>Stock Monitoring</th>
                                        <th>Item Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($nonCountableItems as $item)
                                    <tr>
                                        <td>
                                            <strong class="item-name">{{ $item->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="category-badge">{{ $item->category?->name ?? '—' }}</span>
                                        </td>
                                        <td>{{ $item->inventoryLocation?->name ?? '—' }}</td>
                                        <td>
                                            @if ($item->unit)
                                            <span class="unit-badge">{{ $item->unit->abbreviation }}</span>
                                            @else
                                            <span class="no-value">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="manual-stock-badge">Manual Monitoring</span>
                                        </td>
                                        <td>
                                            @if ($item->is_active)
                                            <span class="item-status active">Active</span>
                                            @else
                                            <span class="item-status inactive">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button
                                                    type="button"
                                                    class="edit-inventory-button"
                                                    data-id="{{ $item->id }}"
                                                    data-name="{{ $item->name }}"
                                                    data-category="{{ $item->category_id }}"
                                                    data-location="{{ $item->inventory_location_id }}"
                                                    data-unit="{{ $item->unit_id }}"
                                                    data-type="{{ $item->inventory_type }}"
                                                    data-price="{{ $item->price }}"
                                                    data-description="{{ $item->description }}">
                                                    Edit
                                                </button>
                                                <form
                                                    method="POST"
                                                    action="{{ route('inventory.toggle-active', $item) }}"
                                                    onsubmit="return confirm('Are you sure you want to {{ $item->is_active ? 'deactivate' : 'activate' }} this inventory item?');">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="toggle-button {{ $item->is_active ? 'deactivate' : 'activate' }}">
                                                        {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            No non-countable inventory items found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($nonCountableItems->hasPages())
                        <div class="inventory-pagination">
                            {{ $nonCountableItems->links() }}
                        </div>
                        @endif

                    </div>
                </div>

            </div>

        </div>

    </div>



    {{-- ========================================================= --}}
    {{-- CREATE ITEM MODAL --}}
    {{-- ========================================================= --}}

    <div
        id="createItemModal"
        class="inventory-modal"
        aria-hidden="true">

        <div class="inventory-modal-backdrop" data-close-create-modal></div>

        <div
            class="inventory-modal-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="createItemTitle">

            <div class="inventory-modal-header">
                <div>
                    <h2 id="createItemTitle">Add New Inventory Item</h2>
                    <p>Add the item information to your inventory.</p>
                </div>
                <button
                    type="button"
                    class="inventory-modal-close"
                    data-close-create-modal>
                    ×
                </button>
            </div>

            <form method="POST" action="{{ route('inventory.store') }}">
                @csrf

                <div class="inventory-modal-body">

                    <div class="form-group">
                        <label for="create_name">Item Name</label>
                        <input
                            type="text"
                            id="create_name"
                            name="name"
                            class="modal-input"
                            value="{{ old('name') }}"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="create_category_id">Category</label>
                        <select
                            id="create_category_id"
                            name="category_id"
                            class="modal-input"
                            required>
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(old('category_id')==$category->id)>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_inventory_location_id">Location</label>
                        <select
                            id="create_inventory_location_id"
                            name="inventory_location_id"
                            class="modal-input"
                            required>
                            <option value="">Select location</option>
                            @foreach ($locations as $location)
                            <option
                                value="{{ $location->id }}"
                                @selected(old('inventory_location_id')==$location->id)>
                                {{ $location->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_unit_id">
                            Unit <span>(optional)</span>
                        </label>
                        <select
                            id="create_unit_id"
                            name="unit_id"
                            class="modal-input">
                            <option value="">No unit</option>
                            @foreach ($units as $unit)
                            <option
                                value="{{ $unit->id }}"
                                @selected(old('unit_id')==$unit->id)>
                                {{ $unit->name }} ({{ $unit->abbreviation }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_inventory_type">Inventory Type</label>
                        <select
                            id="create_inventory_type"
                            name="inventory_type"
                            class="modal-input">
                            <option value="">Select type</option>
                            <option
                                value="prepped"
                                @selected(old('inventory_type')==='prepped' )>
                                Prepped / Countable
                            </option>
                            <option
                                value="physical"
                                @selected(old('inventory_type')==='physical' )>
                                Physical / Non-Countable
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_price">Selling Price</label>
                        <input
                            type="number"
                            id="create_price"
                            name="price"
                            class="modal-input"
                            value="{{ old('price', 0) }}"
                            min="0"
                            step="0.01"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="create_description">Description</label>
                        <textarea
                            id="create_description"
                            name="description"
                            class="modal-input"
                            rows="3"
                            required>{{ old('description') }}</textarea>
                    </div>

                </div>

                <div class="inventory-modal-footer">
                    <button
                        type="button"
                        class="modal-secondary-button"
                        data-close-create-modal>
                        Cancel
                    </button>
                    <button type="submit" class="modal-primary-button">
                        Add Item
                    </button>
                </div>

            </form>

        </div>
    </div>



    {{-- ========================================================= --}}
    {{-- EDIT ITEM MODAL --}}
    {{-- ========================================================= --}}

    <div
        id="editItemModal"
        class="inventory-modal"
        aria-hidden="true">

        <div class="inventory-modal-backdrop" data-close-edit-modal></div>

        <div
            class="inventory-modal-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="editItemTitle">

            <div class="inventory-modal-header">
                <div>
                    <h2 id="editItemTitle">Edit Inventory Item</h2>
                    <p>Update the item's information.</p>
                </div>
                <button
                    type="button"
                    class="inventory-modal-close"
                    data-close-edit-modal>
                    ×
                </button>
            </div>

            <form method="POST" id="editItemForm">
                @csrf
                @method('PUT')

                <div class="inventory-modal-body">

                    <div class="form-group">
                        <label for="edit_name">Item Name</label>
                        <input
                            type="text"
                            id="edit_name"
                            name="name"
                            class="modal-input"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="edit_category_id">Category</label>
                        <select
                            id="edit_category_id"
                            name="category_id"
                            class="modal-input"
                            required>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_inventory_location_id">Location</label>
                        <select
                            id="edit_inventory_location_id"
                            name="inventory_location_id"
                            class="modal-input"
                            required>
                            @foreach ($locations as $location)
                            <option value="{{ $location->id }}">
                                {{ $location->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_unit_id">
                            Unit <span>(optional)</span>
                        </label>
                        <select
                            id="edit_unit_id"
                            name="unit_id"
                            class="modal-input">
                            <option value="">No unit</option>
                            @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">
                                {{ $unit->name }} ({{ $unit->abbreviation }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_inventory_type">Inventory Type</label>
                        <select
                            id="edit_inventory_type"
                            name="inventory_type"
                            class="modal-input">
                            <option value="">Select type</option>
                            <option value="prepped">Prepped / Countable</option>
                            <option value="physical">Physical / Non-Countable</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_price">Selling Price</label>
                        <input
                            type="number"
                            id="edit_price"
                            name="price"
                            class="modal-input"
                            min="0"
                            step="0.01"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea
                            id="edit_description"
                            name="description"
                            class="modal-input"
                            rows="3"
                            required></textarea>
                    </div>

                </div>

                <div class="inventory-modal-footer">
                    <button
                        type="button"
                        class="modal-secondary-button"
                        data-close-edit-modal>
                        Cancel
                    </button>
                    <button type="submit" class="modal-primary-button">
                        Save Changes
                    </button>
                </div>

            </form>

        </div>
    </div>

    {{-- =========================================================
     DAILY INVENTORY MODAL
     ========================================================= --}}

    <div class="inventory-daily-modal" id="inventoryDailyModal" aria-hidden="true">

        <div class="inventory-daily-backdrop" data-close-daily-modal></div>

        <div class="inventory-daily-content" role="dialog" aria-modal="true">

            <div class="inventory-daily-header">

                <div>
                    <h2 id="dailyModalTitle">Daily Inventory</h2>

                    <p id="dailyModalDescription">
                        Review and add inventory stock.
                    </p>
                </div>

                <button
                    type="button"
                    class="inventory-daily-close"
                    data-close-daily-modal
                    aria-label="Close">
                    &times;
                </button>

            </div>


            <form
                method="POST"
                action="{{ route('inventory.daily.store') }}"
                id="dailyInventoryForm">

                @csrf

                <input
                    type="hidden"
                    name="inventory_type"
                    id="dailyInventoryType">

                <input
                    type="hidden"
                    name="stock_date"
                    id="dailyStockDate"
                    value="{{ now()->toDateString() }}">


                <div class="inventory-daily-body">


                    {{-- =====================================================
                     PREPPED FOOD
                     ===================================================== --}}

                    <div
                        id="preppedDailyFields"
                        class="daily-field-group">

                        <div class="daily-date-row">

                            <label for="preppedStockDate">
                                Date
                            </label>

                            <input
                                type="date"
                                id="preppedStockDate"
                                value="{{ $stockDate }}"
                                max="{{ now()->toDateString() }}">

                        </div>


                        <div class="daily-info-box">

                            <strong>Prepped Food</strong>

                            <span>
                                Beginning, Sold, Input New, and Ending are
                                calculated from inventory transactions.
                                Enter only the new stock you want to add.
                            </span>

                        </div>


                        <div class="daily-table-wrapper">

                            <table class="daily-inventory-table">

                                <thead>

                                    <tr>
                                        <th>Item</th>
                                        <th>Beginning</th>
                                        <th>Sold</th>
                                        <th>Input New</th>
                                        <th>Add Stock</th>
                                        <th>Ending</th>
                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach ($preppedItems ?? [] as $item)

                                    @php

                                    $beginning =
                                    (float) ($beginningQuantities[$item->id] ?? 0);

                                    $sold =
                                    (float) ($soldQuantities[$item->id] ?? 0);

                                    $inputNew =
                                    (float) ($inputNewQuantities[$item->id] ?? 0);

                                    $ending =
                                    $beginning - $sold + $inputNew;

                                    @endphp

                                    <tr>

                                        {{-- ITEM --}}
                                        <td>
                                            <strong>
                                                {{ $item->name }}
                                            </strong>
                                        </td>


                                        {{-- BEGINNING --}}
                                        <td>

                                            <span class="system-value">
                                                {{ number_format($beginning, 0) }}
                                            </span>

                                        </td>


                                        {{-- SOLD --}}
                                        <td>

                                            <span class="system-value">
                                                {{ number_format($sold, 0) }}
                                            </span>

                                        </td>


                                        {{-- INPUT NEW
                                         Existing stock-in transactions
                                         for the selected day.
                                         This is READ-ONLY. --}}
                                        <td>

                                            <span class="system-value">
                                                {{ number_format($inputNew, 0) }}
                                            </span>

                                        </td>


                                        {{-- ADD STOCK
                                         Only this field is submitted
                                         as a NEW stock-in transaction. --}}
                                        <td>

                                            <input
                                                type="number"
                                                name="input_new[{{ $item->id }}]"
                                                class="daily-input prepped-input-new"
                                                data-beginning="{{ $beginning }}"
                                                data-sold="{{ $sold }}"
                                                data-input-new="{{ $inputNew }}"
                                                min="0"
                                                step="1"
                                                value="0"
                                                placeholder="0">

                                        </td>


                                        {{-- ENDING --}}
                                        <td>

                                            <span class="ending-value">

                                                {{ number_format($ending, 0) }}

                                            </span>

                                        </td>

                                    </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>



                    {{-- =====================================================
                     NON-COUNTABLE
                     ===================================================== --}}

                    <div
                        id="nonCountableDailyFields"
                        class="daily-field-group"
                        hidden>

                        <div class="daily-date-row">

                            <label for="nonCountableStockDate">
                                Date
                            </label>

                            <input
                                type="date"
                                id="preppedStockDate"
                                value="{{ $stockDate }}"
                                max="{{ now()->toDateString() }}">

                        </div>


                        <div class="daily-info-box">

                            <strong>
                                Non-Countable Inventory
                            </strong>

                            <span>
                                Manually count the actual quantity.
                                The system does not deduct these items from sales.
                            </span>

                        </div>


                        <div class="daily-table-wrapper">

                            <table class="daily-inventory-table">

                                <thead>

                                    <tr>
                                        <th>Item</th>
                                        <th>Beginning</th>
                                        <th>System Unit</th>
                                        <th>Actual Quantity</th>
                                        <th>Notes</th>
                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach ($nonCountableItems ?? [] as $item)

                                    <tr>

                                        <td>
                                            <strong>
                                                {{ $item->name }}
                                            </strong>
                                        </td>


                                        <td>

                                            <span class="system-value">
                                                {{ number_format(
                                                $beginningQuantities[$item->id] ?? 0,
                                                2
                                            ) }}
                                            </span>

                                        </td>


                                        <td>

                                            <span class="unit-badge">
                                                {{ $item->unit?->abbreviation ?? '—' }}
                                            </span>

                                        </td>


                                        <td>

                                            <input
                                                type="number"
                                                name="actual_quantity[{{ $item->id }}]"
                                                class="daily-input"
                                                min="0"
                                                step="0.01"
                                                placeholder="Enter quantity">

                                        </td>


                                        <td>

                                            <input
                                                type="text"
                                                name="physical_notes[{{ $item->id }}]"
                                                class="daily-input daily-notes"
                                                maxlength="255"
                                                placeholder="Optional note">

                                        </td>

                                    </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>



                    {{-- =====================================================
                     COFFEE / JUICE
                     ===================================================== --}}

                    <div
                        id="salesDailyFields"
                        class="daily-field-group"
                        hidden>

                        <div class="daily-date-row">

                            <label for="salesStockDate">
                                Date
                            </label>

                            <input
                                type="date"
                                id="preppedStockDate"
                                value="{{ $stockDate }}"
                                max="{{ now()->toDateString() }}">

                        </div>


                        <div class="daily-info-box">

                            <strong id="salesDailyType">
                                Daily Sales
                            </strong>

                            <span>
                                These items are made to order.
                                Sales are automatically tracked from POS.
                            </span>

                        </div>


                        <div class="daily-table-wrapper">

                            <table class="daily-inventory-table">

                                <thead>

                                    <tr>
                                        <th>Item</th>
                                        <th>Sold</th>
                                    </tr>

                                </thead>


                                <tbody id="salesDailyItems">

                                    @foreach (
                                    ($coffeeItems ?? collect())
                                    ->concat($juiceItems ?? collect())
                                    as $item
                                    )

                                    <tr
                                        data-sales-type="{{ strtolower($item->category?->name ?? '') }}"
                                        hidden>

                                        <td>
                                            <strong>
                                                {{ $item->name }}
                                            </strong>
                                        </td>


                                        <td>

                                            <input
                                                type="number"
                                                name="sold_quantity[{{ $item->id }}]"
                                                class="daily-input"
                                                min="0"
                                                step="1"
                                                value="0"
                                                placeholder="Automatically tracked">

                                        </td>

                                    </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                 FOOTER
                 ===================================================== --}}

                <div class="inventory-daily-footer">

                    <button
                        type="button"
                        class="modal-secondary-button"
                        data-close-daily-modal>
                        Cancel
                    </button>


                    <button
                        type="submit"
                        class="modal-primary-button">
                        Add Stock
                    </button>

                </div>

            </form>

        </div>

    </div>

    {{-- ========================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ========================================================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const tabs = document.querySelectorAll('.inventory-tab');
            const panels = document.querySelectorAll('.inventory-tab-panel');

            function activateTab(target) {

                // Remove active state from all tabs
                tabs.forEach(function(item) {
                    item.classList.remove('is-active');
                    item.setAttribute('aria-selected', 'false');
                });

                // Hide all panels
                panels.forEach(function(panel) {
                    panel.classList.remove('is-active');
                    panel.hidden = true;
                });

                // Activate selected tab
                const targetTab = document.querySelector(
                    '.inventory-tab[data-tab="' + target + '"]'
                );

                if (targetTab) {
                    targetTab.classList.add('is-active');
                    targetTab.setAttribute('aria-selected', 'true');
                }

                // Show matching panel
                const targetPanel = document.querySelector(
                    '.inventory-tab-panel[data-panel="' + target + '"]'
                );

                if (targetPanel) {
                    targetPanel.classList.add('is-active');
                    targetPanel.hidden = false;
                }
            }

            // Normal tab clicking
            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    activateTab(this.dataset.tab);
                });
            });

            // Determine active tab after page reload
            const params = new URLSearchParams(window.location.search);

            let initialTab = 'prepped';

            if (params.has('coffee_page')) {
                initialTab = 'coffee';
            } else if (params.has('juice_page')) {
                initialTab = 'juice';
            } else if (params.has('non_countable_page')) {
                initialTab = 'non-countable';
            } else if (params.has('prepped_page')) {
                initialTab = 'prepped';
            }

            activateTab(initialTab);

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* =====================================================
               DAILY INVENTORY MODAL
               ===================================================== */

            const modal =
                document.getElementById('inventoryDailyModal');

            if (!modal) return;


            const title =
                document.getElementById('dailyModalTitle');

            const description =
                document.getElementById('dailyModalDescription');


            const preppedFields =
                document.getElementById('preppedDailyFields');

            const nonCountableFields =
                document.getElementById('nonCountableDailyFields');

            const salesFields =
                document.getElementById('salesDailyFields');


            const salesTitle =
                document.getElementById('salesDailyType');

            const salesDailyItems =
                document.getElementById('salesDailyItems');


            const dailyInventoryType =
                document.getElementById('dailyInventoryType');

            const dailyStockDate =
                document.getElementById('dailyStockDate');

            const preppedStockDate = document.getElementById('preppedStockDate');
            const preppedDailyFields = document.getElementById('preppedDailyFields');

            if (preppedStockDate) {
                preppedStockDate.addEventListener('change', async function() {
                    const selectedDate = this.value;

                    if (!selectedDate) {
                        return;
                    }

                    const today = new Date().toISOString().split('T')[0];

                    // Prevent future dates
                    if (selectedDate > today) {
                        this.value = today;
                        return;
                    }

                    try {
                        const response = await fetch(
                            `{{ route('inventory.daily-history') }}?date=${selectedDate}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            }
                        );

                        if (!response.ok) {
                            throw new Error('Failed to load inventory history.');
                        }

                        const result = await response.json();

                        updatePreppedInventoryTable(result);

                    } catch (error) {
                        console.error(error);
                        alert('Unable to load inventory history.');
                    }
                });
            }

            const nonCountableStockDate =
                document.getElementById('nonCountableStockDate');

            const salesStockDate =
                document.getElementById('salesStockDate');



            /* =====================================================
               CLOSE MODAL
               ===================================================== */

            function closeDailyModal() {

                modal.classList.remove('is-open');

                modal.setAttribute(
                    'aria-hidden',
                    'true'
                );

            }

            function updatePreppedInventoryTable(result) {
                const tableBody = document.querySelector(
                    '#preppedDailyFields .daily-inventory-table tbody'
                );

                if (!tableBody) {
                    return;
                }

                tableBody.innerHTML = '';

                result.items.forEach(item => {
                    const beginning = Number(item.beginning || 0);
                    const inputNew = Number(item.input_new || 0);
                    const sold = Number(item.sold || 0);
                    const ending = Number(item.ending || 0);

                    const row = document.createElement('tr');

                    row.innerHTML = `
            <td>
                <strong>${item.name}</strong>
            </td>

            <td>
                <span class="system-value">
                    ${beginning.toLocaleString()}
                </span>
            </td>

            <td>
                <span class="system-value">
                    ${inputNew.toLocaleString()}
                </span>
            </td>

            <td>
                <span class="system-value">
                    ${sold.toLocaleString()}
                </span>
            </td>

            <td class="add-stock-column">
                <input
                    type="number"
                    name="input_new[${item.id}]"
                    class="daily-input prepped-input-new"
                    data-beginning="${beginning}"
                    data-sold="${sold}"
                    data-input-new="${inputNew}"
                    min="0"
                    step="1"
                    value="0"
                    placeholder="0">
            </td>

            <td>
                <span class="ending-value">
                    ${ending.toLocaleString()}
                </span>
            </td>
        `;

                    tableBody.appendChild(row);
                });

                updateAddStockVisibility(result.is_today);
            }

            function updateAddStockVisibility(isToday) {
                const addStockColumns = document.querySelectorAll(
                    '#preppedDailyFields .add-stock-column'
                );

                const addStockButton = document.querySelector(
                    '#dailyInventoryForm .modal-primary-button'
                );

                addStockColumns.forEach(column => {
                    column.style.display = isToday ? '' : 'none';
                });

                if (addStockButton) {
                    addStockButton.style.display = isToday ? '' : 'none';
                }
            }

            /* =====================================================
               SYNC DATE
               ===================================================== */

            function syncStockDate(input) {

                if (
                    dailyStockDate &&
                    input
                ) {

                    dailyStockDate.value =
                        input.value;

                }

            }


            if (preppedStockDate) {

                preppedStockDate.addEventListener(
                    'change',
                    function() {

                        syncStockDate(this);

                    }
                );

            }


            if (nonCountableStockDate) {

                nonCountableStockDate.addEventListener(
                    'change',
                    function() {

                        syncStockDate(this);

                    }
                );

            }


            if (salesStockDate) {

                salesStockDate.addEventListener(
                    'change',
                    function() {

                        syncStockDate(this);

                    }
                );

            }



            /* =====================================================
               SHOW COFFEE / JUICE ITEMS
               ===================================================== */

            function showSalesItems(type) {

                if (!salesDailyItems) return;


                salesDailyItems
                    .querySelectorAll('tr')
                    .forEach(function(row) {

                        const rowType =
                            row.dataset.salesType;

                        row.hidden =
                            rowType !== type;


                        if (row.hidden) {

                            const input =
                                row.querySelector('input');

                            if (input) {

                                input.value = 0;

                            }

                        }

                    });

            }



            /* =====================================================
               OPEN DAILY INVENTORY MODAL
               ===================================================== */

            document
                .querySelectorAll('.inventory-settings-button')
                .forEach(function(button) {

                    button.addEventListener(
                        'click',
                        function() {

                            const type =
                                this.dataset.settingsType;


                            /* -------------------------------------
                               INVENTORY TYPE
                               ------------------------------------- */

                            if (dailyInventoryType) {

                                dailyInventoryType.value =
                                    type;

                            }



                            /* -------------------------------------
                               DATE
                               ------------------------------------- */

                            if (dailyStockDate) {

                                if (
                                    type === 'prepped' &&
                                    preppedStockDate
                                ) {

                                    dailyStockDate.value =
                                        preppedStockDate.value;

                                } else if (
                                    type === 'non-countable' &&
                                    nonCountableStockDate
                                ) {

                                    dailyStockDate.value =
                                        nonCountableStockDate.value;

                                } else if (
                                    (
                                        type === 'coffee' ||
                                        type === 'juice'
                                    ) &&
                                    salesStockDate
                                ) {

                                    dailyStockDate.value =
                                        salesStockDate.value;

                                }

                            }



                            /* -------------------------------------
                               HIDE ALL SECTIONS
                               ------------------------------------- */

                            preppedFields.hidden = true;

                            nonCountableFields.hidden = true;

                            salesFields.hidden = true;



                            /* -------------------------------------
                               PREPPED
                               ------------------------------------- */

                            if (type === 'prepped') {

                                title.textContent =
                                    'Prepped Food — Stock';

                                description.textContent =
                                    'Review stock transactions and add new prepped food stock.';

                                preppedFields.hidden =
                                    false;

                            }



                            /* -------------------------------------
                               NON-COUNTABLE
                               ------------------------------------- */
                            else if (
                                type === 'non-countable'
                            ) {

                                title.textContent =
                                    'Non-Countable — Manual Inventory';

                                description.textContent =
                                    'Manually record the actual quantity of ingredients and supplies.';

                                nonCountableFields.hidden =
                                    false;

                            }



                            /* -------------------------------------
                               COFFEE
                               ------------------------------------- */
                            else if (type === 'coffee') {

                                title.textContent =
                                    'Coffee — Daily Sales';

                                description.textContent =
                                    'Coffee sales are automatically tracked from POS.';

                                salesTitle.textContent =
                                    'Coffee Sales';

                                showSalesItems('coffee');

                                salesFields.hidden =
                                    false;

                            }



                            /* -------------------------------------
                               JUICE
                               ------------------------------------- */
                            else if (type === 'juice') {

                                title.textContent =
                                    'Juice — Daily Sales';

                                description.textContent =
                                    'Juice sales are automatically tracked from POS.';

                                salesTitle.textContent =
                                    'Juice Sales';

                                showSalesItems('juice');

                                salesFields.hidden =
                                    false;

                            }



                            /* -------------------------------------
                               OPEN MODAL
                               ------------------------------------- */

                            modal.classList.add(
                                'is-open'
                            );

                            modal.setAttribute(
                                'aria-hidden',
                                'false'
                            );

                        }
                    );

                });



            /* =====================================================
               CLOSE BUTTONS / BACKDROP
               ===================================================== */

            document
                .querySelectorAll('[data-close-daily-modal]')
                .forEach(function(element) {

                    element.addEventListener(
                        'click',
                        closeDailyModal
                    );

                });



            /* =====================================================
               ESCAPE KEY
               ===================================================== */

            document.addEventListener(
                'keydown',
                function(event) {

                    if (
                        event.key === 'Escape' &&
                        modal.classList.contains('is-open')
                    ) {

                        closeDailyModal();

                    }

                }
            );



            /* =====================================================
               PREPPED ENDING CALCULATION
               
               Ending =
               Beginning
               - Sold
               + Existing Input New
               + New Add Stock
               ===================================================== */

            document
                .querySelectorAll('.prepped-input-new')
                .forEach(function(input) {

                    input.addEventListener(
                        'input',
                        function() {

                            const beginning =
                                Number(
                                    this.dataset.beginning || 0
                                );


                            const sold =
                                Number(
                                    this.dataset.sold || 0
                                );


                            const existingInputNew =
                                Number(
                                    this.dataset.inputNew || 0
                                );


                            const newAddStock =
                                Number(
                                    this.value || 0
                                );


                            const ending =
                                beginning -
                                sold +
                                existingInputNew +
                                newAddStock;


                            const row =
                                this.closest('tr');


                            const endingElement =
                                row.querySelector(
                                    '.ending-value'
                                );


                            if (endingElement) {

                                endingElement.textContent =
                                    ending.toLocaleString(
                                        undefined, {
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        }
                                    );

                            }

                        }
                    );

                });

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const editModal = document.getElementById('editItemModal');
            const editForm = document.getElementById('editItemForm');

            document.querySelectorAll('.edit-inventory-button').forEach(button => {
                button.addEventListener('click', function() {

                    const id = this.dataset.id;

                    // Fill the form fields
                    document.getElementById('edit_name').value =
                        this.dataset.name || '';

                    document.getElementById('edit_category_id').value =
                        this.dataset.category || '';

                    document.getElementById('edit_inventory_location_id').value =
                        this.dataset.location || '';

                    document.getElementById('edit_unit_id').value =
                        this.dataset.unit || '';

                    document.getElementById('edit_inventory_type').value =
                        this.dataset.type || '';

                    document.getElementById('edit_price').value =
                        this.dataset.price || '';

                    document.getElementById('edit_description').value =
                        this.dataset.description || '';

                    // Set the PUT route for this specific item
                    editForm.action = `/inventory/${id}`;

                    // Open modal
                    editModal.classList.add('is-open');
                    editModal.setAttribute('aria-hidden', 'false');
                });
            });

            // Close modal
            document.querySelectorAll('[data-close-edit-modal]').forEach(button => {
                button.addEventListener('click', function() {
                    editModal.classList.remove('is-open');
                    editModal.setAttribute('aria-hidden', 'true');
                });
            });

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const createModal = document.getElementById('createItemModal');
            const openCreateModal = document.getElementById('openCreateModal');

            if (openCreateModal && createModal) {

                // Open New Item modal
                openCreateModal.addEventListener('click', function() {
                    createModal.classList.add('is-open');
                    createModal.setAttribute('aria-hidden', 'false');
                });

                // Close New Item modal
                document.querySelectorAll('[data-close-create-modal]').forEach(button => {
                    button.addEventListener('click', function() {
                        createModal.classList.remove('is-open');
                        createModal.setAttribute('aria-hidden', 'true');
                    });
                });

            }

        });
    </script>
</x-app-layout>