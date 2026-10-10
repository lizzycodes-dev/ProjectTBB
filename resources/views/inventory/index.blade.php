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

            <div class="inventory-header-actions">

                {{-- Archive --}}
                <button
                    type="button"
                    class="inventory-archive-button"
                    id="openArchiveModal">
                    <span class="archive-button-icon">▣</span>
                    <span>Archive</span>
                </button>

                {{-- New Item --}}
                <button
                    type="button"
                    class="inventory-new-button"
                    id="openCreateModal">
                    <span class="new-button-icon">+</span>
                    <span>New Item</span>
                </button>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- MAIN LAYOUT --}}
        {{-- ========================================================= --}}
        <div class="inventory-layout">

            <div class="inventory-main">

                @php
                $activeTab = request('tab',
                request()->has('drinks_page') ? 'drinks'
                : (request()->has('non_countable_page') ? 'non-countable'
                : 'prepped')
                );
                @endphp

                <div class="inventory-tabs" role="tablist">
                    <button type="button"
                        class="inventory-tab {{ $activeTab === 'prepped' ? 'is-active' : '' }}"
                        data-tab="prepped"
                        aria-selected="{{ $activeTab === 'prepped' ? 'true' : 'false' }}">
                        Prepped
                    </button>

                    <button type="button"
                        class="inventory-tab {{ $activeTab === 'drinks' ? 'is-active' : '' }}"
                        data-tab="drinks"
                        aria-selected="{{ $activeTab === 'drinks' ? 'true' : 'false' }}">
                        Drinks
                    </button>

                    <button type="button"
                        class="inventory-tab {{ $activeTab === 'non-countable' ? 'is-active' : '' }}"
                        data-tab="non-countable"
                        aria-selected="{{ $activeTab === 'non-countable' ? 'true' : 'false' }}">
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

                    <!--<select name="location_id" onchange="this.form.submit()">
                        <option value="">All Areas</option>
                        @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected((string) $locationId===(string) $location->id)>
                            {{ $location->name }}
                        </option>
                        @endforeach
                    </select>-->

                    <select name="category_id" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $categoryId===(string) $category->id)>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>

                    @if ($search !== '' || ($categoryId !== null && $categoryId !== '') || ($locationId !== null && $locationId !== ''))
                    <a href="{{ route('inventory.index') }}" class="inventory-clear">Clear</a>
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
                <div class="inventory-tab-panel is-active" id="panel-prepped" role="tabpanel" data-panel="prepped">

                    <div class="inventory-table-section">

                        <div class="inventory-table-title">
                            <div>
                                <h3>Prepped Food</h3>
                                <p>Countable inventory prepared in advance.</p>

                                <button
                                    type="button"
                                    class="inventory-settings-button"
                                    data-settings-type="prepped"
                                    title="Daily sales settings">

                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="inventory-settings-icon">
                                        <path d="M9 4h6" />
                                        <path d="M9 3h6v3H9z" />
                                        <path d="M6 5H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1" />
                                        <path d="M8 11h8" />
                                        <path d="M8 15h5" />
                                        <path d="M17 14v5" />
                                        <path d="M14.5 16.5h5" />
                                    </svg>

                                    <span>Manage Stocks</span>
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
                                        <th class="unit-column">Unit</th>
                                        <th>Type</th>
                                        <th>Selling Price</th>
                                        <th>Current Stock</th>
                                        <th>Description</th>
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
                                        <td><strong class="item-name">{{ $item->name }}</strong></td>
                                        <td>{{ $item->category?->name ?? '—' }}</td>
                                        <td>{{ $item->inventoryLocation?->name ?? '—' }}</td>
                                        <td class="unit-column">
                                            @if ($item->unit)
                                            <span class="unit-badge">{{ $item->unit->abbreviation }}</span>
                                            @else
                                            <span class="no-value">—</span>
                                            @endif
                                        </td>
                                        <td><strong class="price-value">{{ $item->inventory_type }}</strong></td>
                                        <td><strong class="price-value">₱{{ number_format($item->price, 2) }}</strong></td>
                                        <td><strong class="stock-number">{{ number_format($currentStock, 3) }}</strong></td>
                                        <td><strong class="price-value">{{ $item->description }}</strong></td>
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
                                                    data-description="{{ $item->description }}"
                                                    data-option-groups='@json($item->optionGroups->mapWithKeys(fn($g) => [$g->id => (bool) $g->pivot->is_required]))'>
                                                    Edit
                                                </button>
                                                <form method="POST" action="{{ route('inventory.toggle-active', $item) }}" onsubmit="return confirm('Are you sure you want to {{ $item->is_active ? 'deactivate' : 'activate' }} this inventory item?');">
                                                    @csrf
                                                    <button type="submit" class="toggle-button {{ $item->is_active ? 'deactivate' : 'activate' }}">
                                                        {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="empty-state">No prepped food items found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($preppedItems->hasPages())
                        <div class="inventory-pagination">{{ $preppedItems->links() }}</div>
                        @endif

                    </div>
                </div>


                {{-- PANEL: DRINKS --}}
                <div class="inventory-tab-panel" id="panel-drinks" role="tabpanel" data-panel="drinks" hidden>

                    <div class="inventory-table-section">

                        <div class="inventory-table-title">
                            <div>
                                <h3>Drinks</h3>
                                <p>Made-to-order drinks. Sales are automatically tracked from POS.</p>

                                <button
                                    type="button"
                                    class="inventory-settings-button"
                                    data-settings-type="drinks"
                                    title="View drink sales">

                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="inventory-settings-icon">
                                        <path d="M9 4h6" />
                                        <path d="M9 3h6v3H9z" />
                                        <path d="M6 5H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1" />
                                        <path d="M8 11h8" />
                                        <path d="M8 15h5" />
                                        <path d="M17 14v5" />
                                        <path d="M14.5 16.5h5" />
                                    </svg>

                                    <span>View Sold</span>
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
                                        <th>Description</th>
                                        <th>Item Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($drinkItems as $item)
                                    <tr>
                                        <td><strong class="item-name">{{ $item->name }}</strong></td>
                                        <td><span class="category-badge">{{ $item->category?->name ?? '—' }}</span></td>
                                        <td>{{ $item->inventoryLocation?->name ?? '—' }}</td>
                                        <td><strong class="price-value">₱{{ number_format($item->price, 2) }}</strong></td>
                                        <td><span class="manual-stock-badge">Made to Order</span></td>
                                        <td><strong class="price-value">{{ $item->description }}</strong></td>
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
                                                    data-description="{{ $item->description }}"
                                                    data-option-groups='@json($item->optionGroups->mapWithKeys(fn($g) => [$g->id => (bool) $g->pivot->is_required]))'>
                                                    Edit
                                                </button>
                                                <form method="POST" action="{{ route('inventory.toggle-active', $item) }}" onsubmit="return confirm('Are you sure you want to {{ $item->is_active ? 'deactivate' : 'activate' }} this inventory item?');">
                                                    @csrf
                                                    <button type="submit" class="toggle-button {{ $item->is_active ? 'deactivate' : 'activate' }}">
                                                        {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="empty-state">No drinks found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($drinkItems->hasPages())
                        <div class="inventory-pagination">{{ $drinkItems->links() }}</div>
                        @endif

                    </div>
                </div>


                {{-- PANEL: NON-COUNTABLE --}}
                <div class="inventory-tab-panel" id="panel-non-countable" role="tabpanel" data-panel="non-countable" hidden>

                    <div class="inventory-table-section non-countable-section">

                        <div class="inventory-table-title">
                            <div>
                                <h3>Non-Countable Inventory</h3>
                                <p>Ingredients and supplies that are manually monitored.</p>

                                <button
                                    type="button"
                                    class="inventory-settings-button"
                                    data-settings-type="non-countable"
                                    title="Daily sales settings">

                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="inventory-settings-icon">
                                        <path d="M9 4h6" />
                                        <path d="M9 3h6v3H9z" />
                                        <path d="M6 5H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1" />
                                        <path d="M8 11h8" />
                                        <path d="M8 15h5" />
                                        <path d="M17 14v5" />
                                        <path d="M14.5 16.5h5" />
                                    </svg>

                                    <span>Manage Stocks</span>
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
                                        <th class="unit-column">Unit</th>
                                        <th>Stock Monitoring</th>
                                        <th>Description</th>
                                        <th>Item Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($nonCountableItems as $item)
                                    <tr>
                                        <td><strong class="item-name">{{ $item->name }}</strong></td>
                                        <td><span class="category-badge">{{ $item->category?->name ?? '—' }}</span></td>
                                        <td>{{ $item->inventoryLocation?->name ?? '—' }}</td>
                                        <td class="unit-column">
                                            @if ($item->unit)
                                            <span class="unit-badge">{{ $item->unit->abbreviation }}</span>
                                            @else
                                            <span class="no-value">—</span>
                                            @endif
                                        </td>
                                        <td><span class="manual-stock-badge">Manual Monitoring</span></td>
                                        <td><strong class="item-name">{{ $item->description }}</strong></td>
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
                                                    data-description="{{ $item->description }}"
                                                    data-option-groups='@json($item->optionGroups->mapWithKeys(fn($g) => [$g->id => (bool) $g->pivot->is_required]))'>
                                                    Edit
                                                </button>
                                                <form method="POST" action="{{ route('inventory.toggle-active', $item) }}" onsubmit="return confirm('Are you sure you want to {{ $item->is_active ? 'deactivate' : 'activate' }} this inventory item?');">
                                                    @csrf
                                                    <button type="submit" class="toggle-button {{ $item->is_active ? 'deactivate' : 'activate' }}">
                                                        {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="empty-state">No non-countable inventory items found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($nonCountableItems->hasPages())
                        <div class="inventory-pagination">{{ $nonCountableItems->links() }}</div>
                        @endif

                    </div>
                </div>

            </div>

        </div>

    </div>



    {{-- ========================================================= --}}
    {{-- CREATE ITEM MODAL --}}
    {{-- ========================================================= --}}
    <div id="createItemModal" class="inventory-modal" aria-hidden="true">
        <div class="inventory-modal-backdrop" data-close-create-modal></div>
        <div class="inventory-modal-content" role="dialog" aria-modal="true" aria-labelledby="createItemTitle">
            <div class="inventory-modal-header">
                <div>
                    <h2 id="createItemTitle">Add New Inventory Item</h2>
                    <p>Add the item information to your inventory.</p>
                </div>
                <button type="button" class="inventory-modal-close" data-close-create-modal>×</button>
            </div>

            <form method="POST" action="{{ route('inventory.store') }}">
                @csrf
                <div class="inventory-modal-body">

                    <div class="form-group">
                        <label for="create_name">Item Name</label>
                        <input type="text" id="create_name" name="name" class="modal-input" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="create_category_id">Category</label>
                        <select id="create_category_id" name="category_id" class="modal-input" required>
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id')==$category->id)>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_inventory_location_id">Location</label>
                        <select id="create_inventory_location_id" name="inventory_location_id" class="modal-input" required>
                            <option value="">Select location</option>
                            @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected(old('inventory_location_id')==$location->id)>
                                {{ $location->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_unit_id">Unit <span>(optional)</span></label>
                        <select id="create_unit_id" name="unit_id" class="modal-input">
                            <option value="">No unit</option>
                            @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id')==$unit->id)>
                                {{ $unit->name }} ({{ $unit->abbreviation }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_inventory_type">Inventory Type</label>
                        <select id="create_inventory_type" name="inventory_type" class="modal-input">
                            <option value="">Select type</option>
                            <option value="prepped" @selected(old('inventory_type')==='prepped' )>Prepped / Countable</option>
                            <option value="physical" @selected(old('inventory_type')==='physical' )>Physical / Non-Countable</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_price">Selling Price</label>
                        <input type="number" id="create_price" name="price" class="modal-input" value="{{ old('price', 0) }}" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="create_description">Description</label>
                        <textarea id="create_description" name="description" class="modal-input" rows="3" required>{{ old('description') }}</textarea>
                    </div>

                    {{-- Option Groups (New Item) --}}
                    <div class="form-group">
                        <label>Option Groups</label>

                        <div class="option-groups-list">
                            @forelse ($optionGroups as $group)
                            <label class="option-group-row">
                                <input
                                    type="checkbox"
                                    class="option-group-checkbox"
                                    name="option_groups[{{ $group->id }}][enabled]"
                                    value="1">

                                <span class="option-group-name">{{ $group->name }}</span>

                                <select
                                    class="option-required-select modal-input"
                                    name="option_groups[{{ $group->id }}][required]"
                                    disabled>
                                    <option value="1">Required</option>
                                    <option value="0">Optional</option>
                                </select>
                            </label>
                            @empty
                            <span class="no-value">No option groups available.</span>
                            @endforelse
                        </div>
                    </div>

                </div>

                <div class="inventory-modal-footer">
                    <button type="button" class="modal-secondary-button" data-close-create-modal>Cancel</button>
                    <button type="submit" class="modal-primary-button">Add Item</button>
                </div>
            </form>
        </div>
    </div>



    {{-- ========================================================= --}}
    {{-- EDIT ITEM MODAL --}}
    {{-- ========================================================= --}}
    <div id="editItemModal" class="inventory-modal" aria-hidden="true">
        <div class="inventory-modal-backdrop" data-close-edit-modal></div>
        <div class="inventory-modal-content" role="dialog" aria-modal="true" aria-labelledby="editItemTitle">
            <div class="inventory-modal-header">
                <div>
                    <h2 id="editItemTitle">Edit Inventory Item</h2>
                    <p>Update the item's information.</p>
                </div>
                <button type="button" class="inventory-modal-close" data-close-edit-modal>×</button>
            </div>

            <form method="POST" id="editItemForm">
                @csrf
                @method('PUT')
                <div class="inventory-modal-body">

                    <div class="form-group">
                        <label for="edit_name">Item Name</label>
                        <input type="text" id="edit_name" name="name" class="modal-input" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_category_id">Category</label>
                        <select id="edit_category_id" name="category_id" class="modal-input" required>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_inventory_location_id">Location</label>
                        <select id="edit_inventory_location_id" name="inventory_location_id" class="modal-input" required>
                            @foreach ($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_unit_id">Unit <span>(optional)</span></label>
                        <select id="edit_unit_id" name="unit_id" class="modal-input">
                            <option value="">No unit</option>
                            @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_inventory_type">Inventory Type</label>
                        <select id="edit_inventory_type" name="inventory_type" class="modal-input">
                            <option value="">Select type</option>
                            <option value="prepped">Prepped / Countable</option>
                            <option value="physical">Physical / Non-Countable</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_price">Selling Price</label>
                        <input type="number" id="edit_price" name="price" class="modal-input" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea id="edit_description" name="description" class="modal-input" rows="3" required></textarea>
                    </div>

                    {{-- Option Groups (Edit Item) --}}
                    {{-- Rendered unchecked + disabled by default; JS populates from data-option-groups --}}
                    <div class="form-group">
                        <label>Option Groups</label>

                        <div class="option-groups-list">
                            @forelse ($optionGroups as $group)
                            <label class="option-group-row">
                                <input
                                    type="checkbox"
                                    class="option-group-checkbox"
                                    name="option_groups[{{ $group->id }}][enabled]"
                                    value="1">

                                <span class="option-group-name">{{ $group->name }}</span>

                                <select
                                    class="option-required-select modal-input"
                                    name="option_groups[{{ $group->id }}][required]"
                                    disabled>
                                    <option value="1">Required</option>
                                    <option value="0">Optional</option>
                                </select>
                            </label>
                            @empty
                            <span class="no-value">No option groups available.</span>
                            @endforelse
                        </div>
                    </div>

                </div>

                <div class="inventory-modal-footer">
                    <button type="button" class="modal-secondary-button" data-close-edit-modal>Cancel</button>
                    <button type="submit" class="modal-primary-button">Save Changes</button>
                </div>
            </form>
        </div>
    </div>



    {{-- ========================================================= --}}
    {{-- DAILY INVENTORY MODAL --}}
    {{-- ========================================================= --}}
    <div class="inventory-daily-modal" id="inventoryDailyModal" aria-hidden="true">

        <div class="inventory-daily-backdrop" data-close-daily-modal></div>

        <div class="inventory-daily-content" role="dialog" aria-modal="true">

            <div class="inventory-daily-header">
                <div>
                    <h2 id="dailyModalTitle">Daily Inventory</h2>
                    <p id="dailyModalDescription">Review and add inventory stock.</p>
                </div>
                <button type="button" class="inventory-daily-close" data-close-daily-modal aria-label="Close">&times;</button>
            </div>

            <form method="POST" action="{{ route('inventory.daily.store') }}" id="dailyInventoryForm">
                @csrf

                <input type="hidden" name="inventory_type" id="dailyInventoryType">
                <input type="hidden" name="stock_date" id="dailyStockDate" value="{{ now()->toDateString() }}">

                <div class="inventory-daily-body">

                    {{-- PREPPED --}}
                    <div id="preppedDailyFields" class="daily-field-group">

                        <div class="daily-date-row">
                            <label for="preppedStockDate">Date</label>
                            <input type="date" id="preppedStockDate" value="{{ $stockDate }}" max="{{ now()->toDateString() }}">
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
                                    $beginning = (float) ($beginningQuantities[$item->id] ?? 0);
                                    $sold = (float) ($soldQuantities[$item->id] ?? 0);
                                    $inputNew = (float) ($inputNewQuantities[$item->id] ?? 0);
                                    $ending = $beginning - $sold + $inputNew;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $item->name }}</strong></td>
                                        <td><span class="system-value">{{ number_format($beginning, 0) }}</span></td>
                                        <td><span class="system-value">{{ number_format($sold, 0) }}</span></td>
                                        <td><span class="system-value">{{ number_format($inputNew, 0) }}</span></td>
                                        <td>
                                            <input
                                                type="number"
                                                name="input_new[{{ $item->id }}]"
                                                class="daily-input prepped-input-new"
                                                data-beginning="{{ $beginning }}"
                                                data-sold="{{ $sold }}"
                                                data-input-new="{{ $inputNew }}"
                                                min="0" step="1" value="0" placeholder="0">
                                        </td>
                                        <td>
                                            <span class="ending-value"
                                                style="
                                                        font-weight: 700;
                                                        @if ($ending <= 5)
                                                            color: #dc2626;
                                                        @elseif ($ending <= 15)
                                                            color: #ea580c;
                                                        @else
                                                            color: #16a34a;
                                                        @endif
                                                    ">
                                                {{ number_format($ending, 0) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>


                    {{-- NON-COUNTABLE --}}
                    <div id="nonCountableDailyFields" class="daily-field-group" hidden>

                        <div class="daily-date-row">
                            <label for="nonCountableStockDate">Date</label>
                            <input type="date" id="nonCountableStockDate" value="{{ $stockDate }}" max="{{ now()->toDateString() }}">
                        </div>

                        <div class="daily-info-box">
                            <strong>Non-Countable Inventory</strong>
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
                                <tbody id="nonCountableDailyTableBody"></tbody>
                            </table>
                        </div>
                    </div>


                    {{-- DRINKS --}}
                    <div id="drinksDailyFields" class="daily-field-group" hidden>

                        <div class="daily-date-row" style="flex-wrap: wrap; gap: 10px;">
                            <label for="drinksStockDate">Date</label>
                            <input type="date" id="drinksStockDate" value="{{ $stockDate }}" max="{{ now()->toDateString() }}">

                            <label for="drinksCategoryFilter" style="margin-left: 10px;">Category</label>
                            <select id="drinksCategoryFilter" class="daily-input" style="min-width: 220px;">
                                <option value="">All Drink Categories</option>
                                @foreach ($drinkCategoryNames as $name)
                                <option value="{{ $name }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="daily-info-box">
                            <strong>Drinks</strong>
                            <span>These items are made to order. Sales are automatically tracked from POS.</span>
                        </div>

                        <div class="daily-table-wrapper">
                            <table class="daily-inventory-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Category</th>
                                        <th>Sold</th>
                                    </tr>
                                </thead>
                                <tbody id="drinksSalesBody"></tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="inventory-daily-footer">
                    <button type="button" class="modal-secondary-button" data-close-daily-modal>Cancel</button>
                    <button type="submit" id="dailyInventorySaveButton" class="modal-primary-button">Add Stock</button>
                </div>
            </form>

        </div>
    </div>



    {{-- ========================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ========================================================= --}}

    {{-- TAB SWITCHING --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.inventory-tab');
            const panels = document.querySelectorAll('.inventory-tab-panel');

            function activateTab(target, updateUrl = false) {
                tabs.forEach(t => {
                    t.classList.remove('is-active');
                    t.setAttribute('aria-selected', 'false');
                });
                panels.forEach(p => {
                    p.classList.remove('is-active');
                    p.hidden = true;
                });

                const tab = document.querySelector('.inventory-tab[data-tab="' + target + '"]');
                if (tab) {
                    tab.classList.add('is-active');
                    tab.setAttribute('aria-selected', 'true');
                }

                const panel = document.querySelector('.inventory-tab-panel[data-panel="' + target + '"]');
                if (panel) {
                    panel.classList.add('is-active');
                    panel.hidden = false;
                }

                if (updateUrl) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', target);

                    // Remove ALL page params so pagination state doesn't leak across tabs
                    ['prepped_page', 'drinks_page', 'non_countable_page', 'page']
                    .forEach(p => url.searchParams.delete(p));

                    window.history.replaceState({}, '', url.toString());
                }
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    activateTab(this.dataset.tab, true);
                });
            });

            // Determine initial tab
            const params = new URLSearchParams(window.location.search);
            const explicitTab = params.get('tab');

            let initialTab = 'prepped';
            if (['prepped', 'drinks', 'non-countable'].includes(explicitTab)) {
                initialTab = explicitTab;
            } else if (params.has('drinks_page')) {
                initialTab = 'drinks';
            } else if (params.has('non_countable_page')) {
                initialTab = 'non-countable';
            }

            // If URL has an explicit tab but also has other tabs' page params, clean it
            if (explicitTab) {
                const url = new URL(window.location.href);
                let dirty = false;

                ['prepped_page', 'drinks_page', 'non_countable_page'].forEach(p => {
                    if (url.searchParams.has(p)) {
                        url.searchParams.delete(p);
                        dirty = true;
                    }
                });

                if (dirty) {
                    window.history.replaceState({}, '', url.toString());
                }
            }

            activateTab(initialTab, false);
        });
        const searchForm = document.getElementById('inventoryFilterForm');
        const searchInput = document.getElementById('inventorySearch');

        if (searchForm && searchInput) {
            let timer = null;

            searchInput.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    // Preserve current tab when searching
                    let tab = new URLSearchParams(window.location.search).get('tab') || 'prepped';
                    let hidden = searchForm.querySelector('input[name="tab"]');
                    if (!hidden) {
                        hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'tab';
                        searchForm.appendChild(hidden);
                    }
                    hidden.value = tab;
                    searchForm.submit();
                }, 350);
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(timer);
                    let tab = new URLSearchParams(window.location.search).get('tab') || 'prepped';
                    let hidden = searchForm.querySelector('input[name="tab"]');
                    if (!hidden) {
                        hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'tab';
                        searchForm.appendChild(hidden);
                    }
                    hidden.value = tab;
                    searchForm.submit();
                }
            });
        }
    </script>


    {{-- DAILY INVENTORY MODAL --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const modal = document.getElementById('inventoryDailyModal');
            if (!modal) return;

            const title = document.getElementById('dailyModalTitle');
            const description = document.getElementById('dailyModalDescription');

            const preppedFields = document.getElementById('preppedDailyFields');
            const nonCountableFields = document.getElementById('nonCountableDailyFields');
            const drinksFields = document.getElementById('drinksDailyFields');

            const dailyInventoryType = document.getElementById('dailyInventoryType');
            const dailyStockDate = document.getElementById('dailyStockDate');

            const preppedStockDate = document.getElementById('preppedStockDate');
            const nonCountableStockDate = document.getElementById('nonCountableStockDate');
            const drinksStockDate = document.getElementById('drinksStockDate');
            const drinksCategoryFilter = document.getElementById('drinksCategoryFilter');
            const drinksSalesBody = document.getElementById('drinksSalesBody');

            function closeDailyModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            }

            document.querySelectorAll('[data-close-daily-modal]').forEach(el => {
                el.addEventListener('click', closeDailyModal);
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeDailyModal();
                }
            });

            /* ---------------- prepped ---------------- */
            if (preppedStockDate) {
                preppedStockDate.addEventListener('change', async function() {
                    const selectedDate = this.value;
                    if (!selectedDate) return;

                    const today = new Date().toISOString().split('T')[0];
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

                        if (!response.ok) throw new Error('Failed to load inventory history.');
                        const result = await response.json();
                        updatePreppedInventoryTable(result);

                    } catch (error) {
                        console.error(error);
                        alert('Unable to load inventory history.');
                    }
                });
            }

            function updatePreppedInventoryTable(result) {
                const tableBody = document.querySelector('#preppedDailyFields .daily-inventory-table tbody');
                if (!tableBody) return;

                tableBody.innerHTML = '';

                result.items.forEach(item => {
                    const beginning = Number(item.beginning || 0);
                    const inputNew = Number(item.input_new || 0);
                    const sold = Number(item.sold || 0);
                    const ending = Number(item.ending || 0);

                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td><strong>${item.name}</strong></td>
                    <td><span class="system-value">${beginning.toLocaleString()}</span></td>
                    <td><span class="system-value">${sold.toLocaleString()}</span></td>
                    <td><span class="system-value">${inputNew.toLocaleString()}</span></td>
                    <td class="add-stock-column">
                        <input
                            type="number"
                            name="input_new[${item.id}]"
                            class="daily-input prepped-input-new"
                            data-beginning="${beginning}"
                            data-sold="${sold}"
                            data-input-new="${inputNew}"
                            min="0" step="1" value="0" placeholder="0">
                    </td>
                    <td><span class="ending-value">${ending.toLocaleString()}</span></td>
                `;
                    tableBody.appendChild(row);
                });

                bindPreppedInputs();
                updateAddStockVisibility(result.is_today);
            }

            function updateAddStockVisibility(isToday) {
                const cols = document.querySelectorAll('#preppedDailyFields .add-stock-column');
                const button = document.querySelector('#dailyInventoryForm .modal-primary-button');

                cols.forEach(col => col.style.display = isToday ? '' : 'none');
                if (button) button.style.display = isToday ? '' : 'none';
            }

            function bindPreppedInputs() {
                document.querySelectorAll('.prepped-input-new').forEach(input => {
                    input.addEventListener('input', function() {
                        const beginning = Number(this.dataset.beginning || 0);
                        const sold = Number(this.dataset.sold || 0);
                        const inputNew = Number(this.dataset.inputNew || 0);
                        const addStock = Number(this.value || 0);

                        const ending = beginning - sold + inputNew + addStock;
                        const row = this.closest('tr');
                        const el = row?.querySelector('.ending-value');

                        if (el) {
                            el.textContent = ending.toLocaleString(undefined, {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            });
                        }
                    });
                });
            }

            bindPreppedInputs();

            /* ---------------- non-countable ---------------- */
            async function loadNonCountableInventoryHistory(selectedDate) {
                if (!selectedDate) return;

                try {
                    const response = await fetch(
                        `{{ route('inventory.non-countable-history') }}?date=${selectedDate}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }
                    );

                    if (!response.ok) throw new Error('Failed to load non-countable history.');
                    const result = await response.json();
                    updateNonCountableInventoryTable(result);

                } catch (error) {
                    console.error(error);
                    alert('Unable to load non-countable inventory history.');
                }
            }

            if (nonCountableStockDate) {
                nonCountableStockDate.addEventListener('change', function() {
                    const selectedDate = this.value;
                    if (!selectedDate) return;

                    const today = new Date().toISOString().split('T')[0];
                    if (selectedDate > today) {
                        this.value = today;
                        loadNonCountableInventoryHistory(today);
                        return;
                    }

                    loadNonCountableInventoryHistory(selectedDate);
                });
            }

            function updateNonCountableInventoryTable(result) {
                const tableBody = document.getElementById('nonCountableDailyTableBody');
                if (!tableBody) return;

                tableBody.innerHTML = '';

                result.items.forEach(item => {
                    const beginning = Number(item.beginning || 0);
                    const actualQuantity = item.actual_quantity !== null ? Number(item.actual_quantity) : '';

                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td><strong>${item.name}</strong></td>
                    <td><span class="system-value">
                        ${beginning.toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })}
                    </span></td>
                    <td><span class="unit-badge">${item.unit || '—'}</span></td>
                    <td>
                        <input
                            type="number"
                            name="actual_quantity[${item.id}]"
                            class="daily-input"
                            min="0" step="0.01"
                            value="${actualQuantity}"
                            placeholder="Enter quantity">
                    </td>
                    <td>
                        <input
                            type="text"
                            name="physical_notes[${item.id}]"
                            class="daily-input daily-notes"
                            maxlength="255"
                            value="${item.notes || ''}"
                            placeholder="Optional note">
                    </td>
                `;
                    tableBody.appendChild(row);
                });

                updateNonCountableEditability(result.is_today);
            }

            function updateNonCountableEditability(isToday) {
                const inputs = document.querySelectorAll('#nonCountableDailyFields .daily-input');
                const saveButton = document.getElementById('dailyInventorySaveButton');

                inputs.forEach(input => {
                    input.disabled = !isToday;
                });
                if (saveButton) saveButton.style.display = isToday ? '' : 'none';
            }

            /* ---------------- drinks ---------------- */
            async function loadDrinkSales() {
                if (!drinksStockDate) return;

                const date = drinksStockDate.value;
                const category = drinksCategoryFilter?.value ?? '';

                try {
                    const url = new URL(
                        `{{ route('inventory.sales-history') }}`,
                        window.location.origin
                    );
                    url.searchParams.set('date', date);
                    if (category) url.searchParams.set('category', category);

                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) throw new Error('Failed to load drink sales.');
                    const result = await response.json();
                    renderDrinkSales(result);

                } catch (error) {
                    console.error(error);
                    alert('Unable to load drink sales.');
                }
            }

            function renderDrinkSales(result) {
                if (!drinksSalesBody) return;
                drinksSalesBody.innerHTML = '';

                if (!result.items.length) {
                    drinksSalesBody.innerHTML = `
                    <tr><td colspan="3" class="empty-state">No drinks sold on this date.</td></tr>
                `;
                    return;
                }

                result.items.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td><strong>${item.name}</strong></td>
                    <td><span class="category-badge">${item.category}</span></td>
                    <td><span class="system-value">${Number(item.sold || 0).toLocaleString()}</span></td>
                `;
                    drinksSalesBody.appendChild(row);
                });
            }

            if (drinksStockDate) drinksStockDate.addEventListener('change', loadDrinkSales);
            if (drinksCategoryFilter) drinksCategoryFilter.addEventListener('change', loadDrinkSales);

            /* ---------------- open modal ---------------- */
            document.querySelectorAll('.inventory-settings-button').forEach(function(button) {
                button.addEventListener('click', function() {
                    const type = this.dataset.settingsType;

                    if (dailyInventoryType) dailyInventoryType.value = type;

                    if (dailyStockDate) {
                        if (type === 'prepped' && preppedStockDate) {
                            dailyStockDate.value = preppedStockDate.value;
                        } else if (type === 'non-countable' && nonCountableStockDate) {
                            dailyStockDate.value = nonCountableStockDate.value;
                        } else if (type === 'drinks' && drinksStockDate) {
                            dailyStockDate.value = drinksStockDate.value;
                        }
                    }

                    preppedFields.hidden = true;
                    nonCountableFields.hidden = true;
                    drinksFields.hidden = true;

                    if (type === 'prepped') {
                        title.textContent = 'Prepped Food — Stock';
                        description.textContent = 'Review stock transactions and add new prepped food stock.';
                        preppedFields.hidden = false;
                    } else if (type === 'non-countable') {
                        title.textContent = 'Non-Countable — Manual Inventory';
                        description.textContent = 'Manually record the actual quantity of ingredients and supplies.';
                        nonCountableFields.hidden = false;
                        if (nonCountableStockDate) loadNonCountableInventoryHistory(nonCountableStockDate.value);
                    } else if (type === 'drinks') {
                        title.textContent = 'Drinks — Daily Sales';
                        description.textContent = 'Drinks are made to order. Sales are automatically tracked from POS.';
                        drinksFields.hidden = false;
                        loadDrinkSales();
                    }

                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('inventoryFilterForm');
            const input = document.getElementById('inventorySearch');

            if (!form || !input) return;

            let timer = null;

            input.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(() => form.submit(), 350); // debounce
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(timer);
                    form.submit();
                }
            });
        });
    </script>
    {{-- EDIT ITEM MODAL --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editItemModal');
            const editForm = document.getElementById('editItemForm');

            document.querySelectorAll('.edit-inventory-button').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;

                    document.getElementById('edit_name').value = this.dataset.name || '';
                    document.getElementById('edit_category_id').value = this.dataset.category || '';
                    document.getElementById('edit_inventory_location_id').value = this.dataset.location || '';
                    document.getElementById('edit_unit_id').value = this.dataset.unit || '';
                    document.getElementById('edit_inventory_type').value = this.dataset.type || '';
                    document.getElementById('edit_price').value = this.dataset.price || '';
                    document.getElementById('edit_description').value = this.dataset.description || '';

                    // Option groups
                    let attached = {};
                    try {
                        attached = JSON.parse(this.dataset.optionGroups || '{}');
                    } catch (e) {
                        attached = {};
                    }

                    document.querySelectorAll('#editItemModal .option-group-row').forEach(row => {
                        const checkbox = row.querySelector('.option-group-checkbox');
                        const select = row.querySelector('.option-required-select');

                        if (!checkbox || !select) return;

                        const groupId = checkbox.name.match(/option_groups\[(\d+)\]/)?.[1];
                        const isOn = groupId && (groupId in attached);

                        checkbox.checked = isOn;
                        select.disabled = !isOn;

                        if (isOn) {
                            select.value = attached[groupId] ? '1' : '0';
                        }
                    });

                    editForm.action = `/inventory/${id}`;
                    editModal.classList.add('is-open');
                    editModal.setAttribute('aria-hidden', 'false');
                });
            });

            document.querySelectorAll('[data-close-edit-modal]').forEach(button => {
                button.addEventListener('click', function() {
                    editModal.classList.remove('is-open');
                    editModal.setAttribute('aria-hidden', 'true');
                });
            });

            // Toggle the required dropdown when a checkbox is ticked
            ['#createItemModal', '#editItemModal'].forEach(scope => {
                document.querySelectorAll(scope + ' .option-group-row').forEach(row => {
                    const checkbox = row.querySelector('.option-group-checkbox');
                    const select = row.querySelector('.option-required-select');

                    if (!checkbox || !select) return;

                    checkbox.addEventListener('change', function() {
                        select.disabled = !this.checked;
                    });
                });
            });
        });
    </script>


    {{-- CREATE ITEM MODAL --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const createModal = document.getElementById('createItemModal');
            const openCreateModal = document.getElementById('openCreateModal');

            if (openCreateModal && createModal) {
                openCreateModal.addEventListener('click', function() {
                    // Reset option group rows
                    document.querySelectorAll('#createItemModal .option-group-row').forEach(row => {
                        const checkbox = row.querySelector('.option-group-checkbox');
                        const select = row.querySelector('.option-required-select');

                        if (!checkbox || !select) return;

                        checkbox.checked = false;
                        select.disabled = true;
                        select.value = '1';
                    });

                    createModal.classList.add('is-open');
                    createModal.setAttribute('aria-hidden', 'false');
                });

                document.querySelectorAll('[data-close-create-modal]').forEach(button => {
                    button.addEventListener('click', function() {
                        createModal.classList.remove('is-open');
                        createModal.setAttribute('aria-hidden', 'true');
                    });
                });
            }
        });
    </script>


    {{-- ARCHIVE MODAL --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const archiveModal = document.getElementById('archiveModal');
            const openArchiveButton = document.getElementById('openArchiveModal');
            const closeArchiveButton = document.getElementById('closeArchiveModal');

            if (openArchiveButton && archiveModal) {
                openArchiveButton.addEventListener('click', function() {
                    archiveModal.classList.add('is-open');
                });
            }

            if (closeArchiveButton && archiveModal) {
                closeArchiveButton.addEventListener('click', function() {
                    archiveModal.classList.remove('is-open');
                });
            }

            if (archiveModal) {
                archiveModal.addEventListener('click', function(event) {
                    if (event.target === archiveModal) {
                        archiveModal.classList.remove('is-open');
                    }
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('posSidebar') || document.querySelector('.pos-sidebar');
            const toggle = document.getElementById('posMobileToggle');
            const backdrop = document.getElementById('posMobileBackdrop');

            if (!sidebar || !toggle) return;

            function openSidebar() {
                sidebar.classList.add('is-open');
                backdrop?.classList.add('is-open');
            }

            function closeSidebar() {
                sidebar.classList.remove('is-open');
                backdrop?.classList.remove('is-open');
            }

            toggle.addEventListener('click', function() {
                sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
            });

            backdrop?.addEventListener('click', closeSidebar);

            // Close when a category is tapped
            sidebar.querySelectorAll('.pos-sidebar-link').forEach(function(link) {
                link.addEventListener('click', closeSidebar);
            });

            // Escape closes
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeSidebar();
            });
        });
    </script>
    @include('inventory.partials.archive-modal')
</x-app-layout>