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
                        class="inventory-tab is-active"
                        role="tab"
                        data-tab="prepped"
                        aria-selected="true">
                        Prepped
                    </button>

                    <button
                        type="button"
                        class="inventory-tab"
                        role="tab"
                        data-tab="coffee"
                        aria-selected="false">
                        Coffee
                    </button>

                    <button
                        type="button"
                        class="inventory-tab"
                        role="tab"
                        data-tab="juice"
                        aria-selected="false">
                        Juice
                    </button>

                    <button
                        type="button"
                        class="inventory-tab"
                        role="tab"
                        data-tab="non-countable"
                        aria-selected="false">
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



    {{-- ========================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ========================================================= --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* =====================================================
               TABS
            ===================================================== */

            const tabs = document.querySelectorAll('.inventory-tab');
            const panels = document.querySelectorAll('.inventory-tab-panel');

            function activateTab(tabName) {
                tabs.forEach(function(tab) {
                    const isActive = tab.dataset.tab === tabName;
                    tab.classList.toggle('is-active', isActive);
                    tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                panels.forEach(function(panel) {
                    const isActive = panel.dataset.panel === tabName;
                    panel.classList.toggle('is-active', isActive);
                    if (isActive) {
                        panel.removeAttribute('hidden');
                    } else {
                        panel.setAttribute('hidden', '');
                    }
                });

                if (history.replaceState) {
                    history.replaceState(null, '', '#' + tabName);
                }
            }

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    activateTab(this.dataset.tab);
                });
            });

            // Restore tab from hash on load
            const hash = window.location.hash.replace('#', '');
            if (hash && document.querySelector('[data-tab="' + hash + '"]')) {
                activateTab(hash);
            }


            /* =====================================================
               SEARCH (debounced)
            ===================================================== */

            const inventorySearch = document.getElementById('inventorySearch');

            if (inventorySearch) {
                let searchTimer;

                inventorySearch.addEventListener('input', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => {
                        this.form.submit();
                    }, 400);
                });
            }


            /* =====================================================
               CREATE MODAL
            ===================================================== */

            const createModal = document.getElementById('createItemModal');
            const openCreateButton = document.getElementById('openCreateModal');

            function openCreateModal() {
                createModal.classList.add('is-open');
                createModal.setAttribute('aria-hidden', 'false');
                document.getElementById('create_name')?.focus();
            }

            function closeCreateModal() {
                createModal.classList.remove('is-open');
                createModal.setAttribute('aria-hidden', 'true');
            }

            if (openCreateButton) {
                openCreateButton.addEventListener('click', openCreateModal);
            }

            createModal
                .querySelectorAll('[data-close-create-modal]')
                .forEach(function(element) {
                    element.addEventListener('click', closeCreateModal);
                });


            /* =====================================================
               EDIT MODAL
            ===================================================== */

            const editModal = document.getElementById('editItemModal');
            const editForm = document.getElementById('editItemForm');

            function openEditModal(item) {
                document.getElementById('edit_name').value = item.name ?? '';
                document.getElementById('edit_category_id').value = item.category_id ?? '';
                document.getElementById('edit_inventory_location_id').value = item.inventory_location_id ?? '';
                document.getElementById('edit_unit_id').value = item.unit_id ?? '';
                document.getElementById('edit_inventory_type').value = item.inventory_type ?? '';
                document.getElementById('edit_price').value = item.price ?? 0;
                document.getElementById('edit_description').value = item.description ?? '';

                editForm.action = "{{ url('/inventory') }}/" + item.id;

                editModal.classList.add('is-open');
                editModal.setAttribute('aria-hidden', 'false');
            }

            function closeEditModal() {
                editModal.classList.remove('is-open');
                editModal.setAttribute('aria-hidden', 'true');
            }

            document
                .querySelectorAll('.edit-inventory-button')
                .forEach(function(button) {
                    button.addEventListener('click', function() {
                        const item = {
                            id: this.dataset.id,
                            name: this.dataset.name,
                            category_id: this.dataset.category,
                            inventory_location_id: this.dataset.location,
                            unit_id: this.dataset.unit,
                            inventory_type: this.dataset.type,
                            price: this.dataset.price,
                            description: this.dataset.description
                        };
                        openEditModal(item);
                    });
                });

            editModal
                .querySelectorAll('[data-close-edit-modal]')
                .forEach(function(element) {
                    element.addEventListener('click', closeEditModal);
                });


            /* =====================================================
               ESCAPE KEY
            ===================================================== */

            document.addEventListener('keydown', function(event) {
                if (event.key !== 'Escape') return;
                closeCreateModal();
                closeEditModal();
            });

        });
    </script>

</x-app-layout>