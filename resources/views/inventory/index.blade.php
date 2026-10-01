<x-app-layout>

    <div class="inventory-page">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <div class="inventory-header">

            <div>
                <h1>Inventory Management</h1>

                <p>
                    Manage inventory items and monitor their stock classification.
                </p>
            </div>
            <button
                type="button"
                class="inventory-new-button"
                id="openCreateModal">

                <span class="new-button-icon">
                    +
                </span>

                <span>
                    New Item
                </span>

            </button>

        </div>


        {{-- ========================================================= --}}
        {{-- SUMMARY --}}
        {{-- ========================================================= --}}

        <div class="inventory-summary">

            <div class="inventory-summary-card">
                <span class="summary-label">
                    Active Items
                </span>

                <strong>
                    {{ $activeItemCount }}
                </strong>
            </div>


            <div class="inventory-summary-card">
                <span class="summary-label">
                    Prepped Food
                </span>

                <strong>
                    {{ $preppedItems->total() }}
                </strong>
            </div>


            <div class="inventory-summary-card">
                <span class="summary-label">
                    Non-Countable
                </span>

                <strong>
                    {{ $nonCountableItems->total() }}
                </strong>
            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- SEARCH / FILTER --}}
        {{-- ===================================================== --}}

        <form
            method="GET"
            action="{{ route('inventory.index') }}"
            class="inventory-search">

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

                <option value="">
                    All Areas
                </option>

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

                <option value="">
                    All Categories
                </option>

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

            <a href="{{ route('inventory.index') }}">
                Clear
            </a>

            @endif

        </form>


        {{-- ========================================================= --}}
        {{-- SUCCESS --}}
        {{-- ========================================================= --}}

        @if (session('success'))

        <div class="inventory-alert">
            {{ session('success') }}
        </div>

        @endif


        {{-- ========================================================= --}}
        {{-- ERRORS --}}
        {{-- ========================================================= --}}

        @if ($errors->any())

        <div class="inventory-error">

            <strong>
                Please check the following:
            </strong>

            <ul>

                @foreach ($errors->all() as $error)

                <li>
                    {{ $error }}
                </li>

                @endforeach

            </ul>

        </div>

        @endif



        {{-- ========================================================= --}}
        {{-- INVENTORY MANAGEMENT --}}
        {{-- ========================================================= --}}

        <section class="inventory-section">

            {{-- SECTION HEADER --}}

            <div class="section-heading">

                <div>

                    <h2>
                        Inventory Items
                    </h2>

                    <p>
                        Manage item information here. Daily stock is handled in Begin Day.
                    </p>

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- TABLE 1: PREPPED FOOD --}}
            {{-- ===================================================== --}}

            <div class="inventory-table-section">

                <div class="inventory-table-title">

                    <div>

                        <h3>
                            Prepped Food
                        </h3>

                        <p>
                            Countable inventory prepared in advance.
                        </p>

                    </div>

                    <span class="table-type-badge countable">
                        Countable
                    </span>

                </div>


                <div class="inventory-table-wrapper">

                    <table class="inventory-table">

                        <thead>

                            <tr>

                                <th>
                                    Item Name
                                </th>

                                <th>
                                    Category
                                </th>

                                <th>
                                    Location
                                </th>

                                <th>
                                    Unit
                                </th>

                                <th>
                                    Current Stock
                                </th>

                                <th>
                                    Item Status
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse ($preppedItems as $item)

                            @php

                            $stockIn =
                            (float) ($item->total_stock_in ?? 0);

                            $stockOut =
                            (float) ($item->total_stock_out ?? 0);

                            $currentStock =
                            $stockIn - $stockOut;

                            @endphp


                            <tr>

                                {{-- ITEM --}}

                                <td>

                                    <strong class="item-name">
                                        {{ $item->name }}
                                    </strong>

                                </td>


                                {{-- CATEGORY --}}

                                <td>
                                    {{ $item->category?->name ?? '—' }}
                                </td>


                                {{-- LOCATION --}}

                                <td>
                                    {{ $item->inventoryLocation?->name ?? '—' }}
                                </td>


                                {{-- UNIT --}}

                                <td>

                                    @if ($item->unit)

                                    <span class="unit-badge">
                                        {{ $item->unit->abbreviation }}
                                    </span>

                                    @else

                                    <span class="no-value">
                                        —
                                    </span>

                                    @endif

                                </td>


                                {{-- CURRENT STOCK --}}

                                <td>

                                    <strong class="stock-number">
                                        {{ number_format($currentStock, 3) }}
                                    </strong>

                                </td>


                                {{-- STATUS --}}

                                <td>

                                    @if ($item->is_active)

                                    <span class="item-status active">
                                        Active
                                    </span>

                                    @else

                                    <span class="item-status inactive">
                                        Inactive
                                    </span>

                                    @endif

                                </td>


                                {{-- ACTIONS --}}

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

                                <td
                                    colspan="7"
                                    class="empty-state">

                                    No prepped food items found.

                                </td>

                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- PREPPED PAGINATION --}}

                @if ($preppedItems->hasPages())

                <div class="inventory-pagination">

                    {{ $preppedItems->links() }}

                </div>

                @endif

            </div>

            {{-- ===================================================== --}}
            {{-- TABLE 2: JUICE --}}
            {{-- ===================================================== --}}

            <div class="inventory-table-section">

                <div class="inventory-table-title">

                    <div>

                        <h3>
                            Juice
                        </h3>

                        <p>
                            Juice drinks prepared when ordered.
                        </p>

                    </div>

                    <span class="table-type-badge made-to-order">
                        Made to Order
                    </span>

                </div>


                <div class="inventory-table-wrapper">

                    <table class="inventory-table">

                        <thead>

                            <tr>

                                <th>
                                    Item Name
                                </th>

                                <th>
                                    Category
                                </th>

                                <th>
                                    Location
                                </th>

                                <th>
                                    Selling Price
                                </th>

                                <th>
                                    Stock Monitoring
                                </th>

                                <th>
                                    Item Status
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse ($juiceItems as $item)

                            <tr>

                                {{-- ITEM --}}

                                <td>

                                    <strong class="item-name">
                                        {{ $item->name }}
                                    </strong>

                                </td>


                                {{-- CATEGORY --}}

                                <td>

                                    <span class="category-badge">
                                        {{ $item->category?->name ?? 'Juice' }}
                                    </span>

                                </td>


                                {{-- LOCATION --}}

                                <td>
                                    {{ $item->inventoryLocation?->name ?? '—' }}
                                </td>


                                {{-- PRICE --}}

                                <td>

                                    <strong class="price-value">
                                        ₱{{ number_format($item->price, 2) }}
                                    </strong>

                                </td>


                                {{-- STOCK --}}

                                <td>

                                    <span class="manual-stock-badge">
                                        Made to Order
                                    </span>

                                </td>


                                {{-- STATUS --}}

                                <td>

                                    @if ($item->is_active)

                                    <span class="item-status active">
                                        Active
                                    </span>

                                    @else

                                    <span class="item-status inactive">
                                        Inactive
                                    </span>

                                    @endif

                                </td>


                                {{-- ACTIONS --}}

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

                                <td
                                    colspan="7"
                                    class="empty-state">

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

            {{-- ===================================================== --}}
            {{-- TABLE 3: COFFEE --}}
            {{-- ===================================================== --}}

            <div class="inventory-table-section">

                <div class="inventory-table-title">

                    <div>

                        <h3>
                            Coffee
                        </h3>

                        <p>
                            Coffee drinks prepared when ordered.
                        </p>

                    </div>

                    <span class="table-type-badge made-to-order">
                        Made to Order
                    </span>

                </div>


                <div class="inventory-table-wrapper">

                    <table class="inventory-table">

                        <thead>

                            <tr>

                                <th>
                                    Item Name
                                </th>

                                <th>
                                    Category
                                </th>

                                <th>
                                    Location
                                </th>

                                <th>
                                    Selling Price
                                </th>

                                <th>
                                    Stock Monitoring
                                </th>

                                <th>
                                    Item Status
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse ($coffeeItems as $item)

                            <tr>

                                {{-- ITEM --}}

                                <td>

                                    <strong class="item-name">
                                        {{ $item->name }}
                                    </strong>

                                </td>


                                {{-- CATEGORY --}}

                                <td>

                                    <span class="category-badge">
                                        {{ $item->category?->name ?? 'Coffee' }}
                                    </span>

                                </td>


                                {{-- LOCATION --}}

                                <td>
                                    {{ $item->inventoryLocation?->name ?? '—' }}
                                </td>


                                {{-- PRICE --}}

                                <td>

                                    <strong class="price-value">
                                        ₱{{ number_format($item->price, 2) }}
                                    </strong>

                                </td>


                                {{-- STOCK --}}

                                <td>

                                    <span class="manual-stock-badge">
                                        Made to Order
                                    </span>

                                </td>


                                {{-- STATUS --}}

                                <td>

                                    @if ($item->is_active)

                                    <span class="item-status active">
                                        Active
                                    </span>

                                    @else

                                    <span class="item-status inactive">
                                        Inactive
                                    </span>

                                    @endif

                                </td>


                                {{-- ACTIONS --}}

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

                                <td
                                    colspan="7"
                                    class="empty-state">

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

            {{-- ===================================================== --}}
            {{-- TABLE 4: NON-COUNTABLE --}}
            {{-- ===================================================== --}}

            <div class="inventory-table-section non-countable-section">

                <div class="inventory-table-title">

                    <div>

                        <h3>
                            Non-Countable Inventory
                        </h3>

                        <p>
                            Ingredients and supplies that are manually monitored.
                        </p>

                    </div>

                    <span class="table-type-badge non-countable">
                        Non-Countable
                    </span>

                </div>


                <div class="inventory-category-note">

                    <span>
                        Categories:
                    </span>

                    Ingredient · Puree · Sauce · Powder

                </div>


                <div class="inventory-table-wrapper">

                    <table class="inventory-table non-countable-table">

                        <thead>

                            <tr>

                                <th>
                                    Item Name
                                </th>

                                <th>
                                    Category
                                </th>

                                <th>
                                    Location
                                </th>

                                <th>
                                    Unit
                                </th>

                                <th>
                                    Stock Monitoring
                                </th>

                                <th>
                                    Item Status
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse ($nonCountableItems as $item)

                            <tr>

                                {{-- ITEM --}}

                                <td>

                                    <strong class="item-name">
                                        {{ $item->name }}
                                    </strong>

                                </td>


                                {{-- CATEGORY --}}

                                <td>

                                    <span class="category-badge">
                                        {{ $item->category?->name ?? '—' }}
                                    </span>

                                </td>


                                {{-- LOCATION --}}

                                <td>
                                    {{ $item->inventoryLocation?->name ?? '—' }}
                                </td>


                                {{-- UNIT --}}

                                <td>

                                    @if ($item->unit)

                                    <span class="unit-badge">
                                        {{ $item->unit->abbreviation }}
                                    </span>

                                    @else

                                    <span class="no-value">
                                        —
                                    </span>

                                    @endif

                                </td>


                                {{-- STOCK --}}

                                <td>

                                    <span class="manual-stock-badge">
                                        Manual Monitoring
                                    </span>

                                </td>


                                {{-- STATUS --}}

                                <td>

                                    @if ($item->is_active)

                                    <span class="item-status active">
                                        Active
                                    </span>

                                    @else

                                    <span class="item-status inactive">
                                        Inactive
                                    </span>

                                    @endif

                                </td>


                                {{-- ACTIONS --}}

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

                                <td
                                    colspan="7"
                                    class="empty-state">

                                    No non-countable inventory items found.

                                </td>

                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- NON-COUNTABLE PAGINATION --}}

                @if ($nonCountableItems->hasPages())

                <div class="inventory-pagination">

                    {{ $nonCountableItems->links() }}

                </div>

                @endif

            </div>

        </section>

    </div>



    {{-- ========================================================= --}}
    {{-- CREATE ITEM MODAL --}}
    {{-- ========================================================= --}}

    <div
        id="createItemModal"
        class="inventory-modal"
        aria-hidden="true">

        <div
            class="inventory-modal-backdrop"
            data-close-create-modal>
        </div>


        <div
            class="inventory-modal-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="createItemTitle">

            <div class="inventory-modal-header">

                <div>

                    <h2 id="createItemTitle">
                        Add New Inventory Item
                    </h2>

                    <p>
                        Add the item information to your inventory.
                    </p>

                </div>


                <button
                    type="button"
                    class="inventory-modal-close"
                    data-close-create-modal>

                    ×

                </button>

            </div>


            <form
                method="POST"
                action="{{ route('inventory.store') }}">

                @csrf

                <div class="inventory-modal-body">

                    {{-- ITEM NAME --}}

                    <div class="form-group">

                        <label for="create_name">
                            Item Name
                        </label>

                        <input
                            type="text"
                            id="create_name"
                            name="name"
                            class="modal-input"
                            value="{{ old('name') }}"
                            required>

                    </div>


                    {{-- CATEGORY --}}

                    <div class="form-group">

                        <label for="create_category_id">
                            Category
                        </label>

                        <select
                            id="create_category_id"
                            name="category_id"
                            class="modal-input"
                            required>

                            <option value="">
                                Select category
                            </option>

                            @foreach ($categories as $category)

                            <option
                                value="{{ $category->id }}"
                                @selected(old('category_id')==$category->id)>

                                {{ $category->name }}

                            </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- LOCATION --}}

                    <div class="form-group">

                        <label for="create_inventory_location_id">
                            Location
                        </label>

                        <select
                            id="create_inventory_location_id"
                            name="inventory_location_id"
                            class="modal-input"
                            required>

                            <option value="">
                                Select location
                            </option>

                            @foreach ($locations as $location)

                            <option
                                value="{{ $location->id }}"
                                @selected(old('inventory_location_id')==$location->id)>

                                {{ $location->name }}

                            </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- UNIT --}}

                    <div class="form-group">

                        <label for="create_unit_id">

                            Unit

                            <span>
                                (optional)
                            </span>

                        </label>

                        <select
                            id="create_unit_id"
                            name="unit_id"
                            class="modal-input">

                            <option value="">
                                No unit
                            </option>

                            @foreach ($units as $unit)

                            <option
                                value="{{ $unit->id }}"
                                @selected(old('unit_id')==$unit->id)>

                                {{ $unit->name }}
                                ({{ $unit->abbreviation }})

                            </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- INVENTORY TYPE --}}

                    <div class="form-group">

                        <label for="create_inventory_type">
                            Inventory Type
                        </label>

                        <select
                            id="create_inventory_type"
                            name="inventory_type"
                            class="modal-input">

                            <option value="">
                                Select type
                            </option>

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


                    {{-- PRICE --}}

                    <div class="form-group">

                        <label for="create_price">
                            Selling Price
                        </label>

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


                    {{-- DESCRIPTION --}}

                    <div class="form-group">

                        <label for="create_description">
                            Description
                        </label>

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


                    <button
                        type="submit"
                        class="modal-primary-button">

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

        <div
            class="inventory-modal-backdrop"
            data-close-edit-modal>
        </div>


        <div
            class="inventory-modal-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="editItemTitle">

            <div class="inventory-modal-header">

                <div>

                    <h2 id="editItemTitle">
                        Edit Inventory Item
                    </h2>

                    <p>
                        Update the item's information.
                    </p>

                </div>


                <button
                    type="button"
                    class="inventory-modal-close"
                    data-close-edit-modal>

                    ×

                </button>

            </div>


            <form
                method="POST"
                id="editItemForm">

                @csrf
                @method('PUT')

                <div class="inventory-modal-body">

                    {{-- ITEM NAME --}}

                    <div class="form-group">

                        <label for="edit_name">
                            Item Name
                        </label>

                        <input
                            type="text"
                            id="edit_name"
                            name="name"
                            class="modal-input"
                            required>

                    </div>


                    {{-- CATEGORY --}}

                    <div class="form-group">

                        <label for="edit_category_id">
                            Category
                        </label>

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


                    {{-- LOCATION --}}

                    <div class="form-group">

                        <label for="edit_inventory_location_id">
                            Location
                        </label>

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


                    {{-- UNIT --}}

                    <div class="form-group">

                        <label for="edit_unit_id">

                            Unit

                            <span>
                                (optional)
                            </span>

                        </label>

                        <select
                            id="edit_unit_id"
                            name="unit_id"
                            class="modal-input">

                            <option value="">
                                No unit
                            </option>

                            @foreach ($units as $unit)

                            <option value="{{ $unit->id }}">

                                {{ $unit->name }}
                                ({{ $unit->abbreviation }})

                            </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- INVENTORY TYPE --}}

                    <div class="form-group">

                        <label for="edit_inventory_type">
                            Inventory Type
                        </label>

                        <select
                            id="edit_inventory_type"
                            name="inventory_type"
                            class="modal-input">

                            <option value="">
                                Select type
                            </option>

                            <option value="prepped">
                                Prepped / Countable
                            </option>

                            <option value="physical">
                                Physical / Non-Countable
                            </option>

                        </select>

                    </div>


                    {{-- PRICE --}}

                    <div class="form-group">

                        <label for="edit_price">
                            Selling Price
                        </label>

                        <input
                            type="number"
                            id="edit_price"
                            name="price"
                            class="modal-input"
                            min="0"
                            step="0.01"
                            required>

                    </div>


                    {{-- DESCRIPTION --}}

                    <div class="form-group">

                        <label for="edit_description">
                            Description
                        </label>

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


                    <button
                        type="submit"
                        class="modal-primary-button">

                        Save Changes

                    </button>

                </div>

            </form>

        </div>

    </div>



    {{-- ========================================================= --}}
    {{-- CSS --}}
    {{-- ========================================================= --}}

    <style>
        /* =========================================================
           PAGE
        ========================================================= */

        .inventory-page {
            padding: 28px;
            color: #3f3028;
        }


        .inventory-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 24px;
        }


        .inventory-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }


        .inventory-header p {
            margin: 6px 0 0;
            color: #817168;
        }



        /* =========================================================
           SUMMARY
        ========================================================= */

        .inventory-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit,
                    minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }


        .inventory-summary-card {
            padding: 18px 20px;
            border: 1px solid #eee5df;
            border-radius: 12px;
            background: #fff;
            box-shadow:
                0 2px 8px rgba(60, 40, 25, 0.04);
        }


        .summary-label {
            display: block;
            margin-bottom: 8px;
            color: #817168;
            font-size: 13px;
        }


        .inventory-summary-card strong {
            font-size: 26px;
        }



        /* =========================================================
           NEW BUTTON
        ========================================================= */

        .inventory-new-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            padding: 10px 18px;

            border: 0;
            border-radius: 7px;

            background: #6b4226;
            color: #fff;

            font-size: 14px;
            font-weight: 700;

            cursor: pointer;
        }


        .inventory-new-button:hover {
            background: #52321e;
        }


        .new-button-icon {
            font-size: 20px;
            line-height: 1;
        }



        /* =========================================================
           ALERTS
        ========================================================= */

        .inventory-alert,
        .inventory-error {
            margin-bottom: 18px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
        }


        .inventory-alert {
            border: 1px solid #b8dfc2;
            background: #eaf5ed;
            color: #287344;
        }


        .inventory-error {
            border: 1px solid #e8b7a7;
            background: #fff0e8;
            color: #a84718;
        }


        .inventory-error ul {
            margin: 6px 0 0;
            padding-left: 20px;
        }



        /* =========================================================
           MAIN SECTION
        ========================================================= */

        .inventory-section {
            padding: 20px;

            border: 1px solid #eee5df;
            border-radius: 12px;

            background: #fff;

            box-shadow:
                0 2px 8px rgba(60, 40, 25, 0.04);
        }


        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 12px;
            margin-bottom: 18px;
        }


        .section-heading h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }


        .section-heading p {
            margin: 5px 0 0;
            color: #817168;
            font-size: 13px;
        }



        /* =========================================================
           SEARCH
        ========================================================= */

        .inventory-search {
            display: flex;
            align-items: center;
            flex-wrap: wrap;

            gap: 8px;
            margin-bottom: 24px;
        }


        .inventory-search input,
        .inventory-search select {
            min-height: 38px;

            padding: 8px 10px;

            border: 1px solid #d9cec6;
            border-radius: 7px;

            background: #fff;
            color: #3f3028;

            font-size: 13px;
        }


        .inventory-search input {
            width: 100%;
            max-width: 380px;
        }


        .inventory-search input:focus,
        .inventory-search select:focus {
            outline: 2px solid #a98568;
            outline-offset: 1px;
        }


        .inventory-search a {
            display: inline-flex;
            align-items: center;

            min-height: 38px;

            padding: 8px 14px;

            border-radius: 7px;

            background: #eee5df;
            color: #5b4032;

            font-size: 13px;
            font-weight: 600;

            text-decoration: none;
        }



        /* =========================================================
           TABLE SECTIONS
        ========================================================= */

        .inventory-table-section {
            margin-top: 24px;

            padding: 16px;

            border: 1px solid #eee5df;
            border-radius: 10px;

            background: #fff;
        }


        .inventory-table-section:first-of-type {
            margin-top: 0;
        }


        .inventory-table-title {
            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 12px;

            margin-bottom: 14px;
        }


        .inventory-table-title h3 {
            margin: 0;

            color: #3f3028;

            font-size: 16px;
            font-weight: 700;
        }


        .inventory-table-title p {
            margin: 4px 0 0;

            color: #817168;

            font-size: 12px;
        }


        .table-type-badge {
            display: inline-flex;
            align-items: center;

            padding: 5px 9px;

            border-radius: 20px;

            font-size: 10px;
            font-weight: 700;

            white-space: nowrap;
        }


        .table-type-badge.countable {
            background: #eaf5ed;
            color: #287344;
        }


        .table-type-badge.non-countable {
            background: #f4eee8;
            color: #72543a;
        }


        .inventory-category-note {
            margin-bottom: 12px;

            padding: 8px 10px;

            border-radius: 7px;

            background: #faf7f4;

            color: #817168;

            font-size: 12px;
        }


        .inventory-category-note span {
            color: #5b4032;
            font-weight: 700;
        }



        /* =========================================================
           TABLE
        ========================================================= */

        .inventory-table-wrapper {
            overflow-x: auto;
        }


        .inventory-table {
            width: 100%;
            min-width: 900px;

            border-collapse: collapse;

            table-layout: fixed;

            font-size: 12px;
        }


        .inventory-table th,
        .inventory-table td {
            padding: 8px;

            border: 1px solid #e8e1dc;

            vertical-align: middle;
        }


        .inventory-table th {
            background: #f5f0eb;

            color: #75645a;

            font-size: 11px;
            font-weight: 700;

            text-align: left;
            text-transform: uppercase;
        }


        .inventory-table td {
            color: #4f4239;
        }


        .inventory-table tbody tr:hover {
            background: #fdfaf7;
        }


        .item-name {
            color: #3f3028;
        }


        .stock-number {
            color: #4d3425;
        }


        .unit-badge,
        .category-badge {
            display: inline-block;

            padding: 3px 7px;

            border-radius: 6px;

            font-size: 10px;
            font-weight: 700;
        }


        .unit-badge {
            background: #f4eee8;
            color: #72543a;
        }


        .category-badge {
            background: #f8f1eb;
            color: #76533c;
        }


        .no-value {
            color: #aaa09a;
        }


        .manual-stock-badge {
            display: inline-flex;
            align-items: center;

            padding: 4px 8px;

            border-radius: 20px;

            background: #f4eee8;
            color: #76533c;

            font-size: 10px;
            font-weight: 600;
        }



        /* =========================================================
           STATUS
        ========================================================= */

        .item-status {
            display: inline-block;

            padding: 3px 7px;

            border-radius: 20px;

            font-size: 10px;
            font-weight: 600;
        }


        .item-status.active {
            background: #eaf5ed;
            color: #287344;
        }


        .item-status.inactive {
            background: #f1eeec;
            color: #75645a;
        }



        /* =========================================================
           ACTIONS
        ========================================================= */

        .action-buttons {
            display: flex;
            align-items: center;

            flex-wrap: wrap;

            gap: 5px;
        }


        .edit-inventory-button,
        .toggle-button {
            padding: 5px 8px;

            border: 0;
            border-radius: 6px;

            font-size: 11px;
            font-weight: 600;

            cursor: pointer;
        }


        .edit-inventory-button {
            background: #eee5df;
            color: #5b4032;
        }


        .edit-inventory-button:hover {
            background: #e1d3c8;
        }


        .toggle-button.deactivate {
            background: #fff0e8;
            color: #a84718;
        }


        .toggle-button.activate {
            background: #eaf5ed;
            color: #287344;
        }



        /* =========================================================
           PAGINATION
        ========================================================= */

        .inventory-pagination {
            display: flex;

            justify-content: center;
            align-items: center;

            padding-top: 16px;
        }




        /* =========================================================
           EMPTY
        ========================================================= */

        .empty-state {
            padding: 28px !important;

            color: #817168;

            text-align: center;
        }



        /* =========================================================
           MODALS
        ========================================================= */

        .inventory-modal {
            display: none;

            position: fixed;

            inset: 0;

            z-index: 1000;

            align-items: center;
            justify-content: center;

            padding: 20px;
        }


        .inventory-modal.is-open {
            display: flex;
        }


        .inventory-modal-backdrop {
            position: absolute;

            inset: 0;

            background: rgba(35, 25, 20, 0.55);
        }


        .inventory-modal-content {
            position: relative;

            z-index: 1;

            width: 100%;
            max-width: 520px;

            max-height: 90vh;

            overflow-y: auto;

            border-radius: 12px;

            background: #fff;

            box-shadow:
                0 18px 55px rgba(30, 20, 15, 0.25);
        }


        .inventory-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;

            gap: 16px;

            padding: 18px 20px;

            border-bottom: 1px solid #eee5df;
        }


        .inventory-modal-header h2 {
            margin: 0;

            color: #3f3028;

            font-size: 18px;
        }


        .inventory-modal-header p {
            margin: 5px 0 0;

            color: #817168;

            font-size: 13px;
        }


        .inventory-modal-close {
            border: 0;

            background: transparent;

            color: #75645a;

            font-size: 25px;

            cursor: pointer;
        }


        .inventory-modal-body {
            display: flex;
            flex-direction: column;

            gap: 15px;

            padding: 20px;
        }


        .form-group {
            display: flex;
            flex-direction: column;

            gap: 6px;
        }


        .form-group label {
            color: #5b4032;

            font-size: 12px;
            font-weight: 700;
        }


        .form-group label span {
            color: #968b80;

            font-weight: 500;
        }


        .modal-input {
            width: 100%;

            padding: 9px 10px;

            border: 1px solid #d9cec6;
            border-radius: 7px;

            background: #fff;
            color: #3f3028;

            font-size: 13px;
        }


        .modal-input:focus {
            outline: 2px solid #a98568;
            outline-offset: 1px;
        }


        .inventory-modal-footer {
            display: flex;
            justify-content: flex-end;

            gap: 8px;

            padding: 15px 20px;

            border-top: 1px solid #eee5df;

            background: #faf7f4;
        }


        .modal-primary-button,
        .modal-secondary-button {
            padding: 9px 14px;

            border: 0;
            border-radius: 7px;

            font-size: 13px;
            font-weight: 700;

            cursor: pointer;
        }


        .modal-primary-button {
            background: #6b4226;
            color: #fff;
        }


        .modal-primary-button:hover {
            background: #52321e;
        }


        .modal-secondary-button {
            background: #eee5df;
            color: #5b4032;
        }



        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 640px) {

            .inventory-page {
                padding: 16px;
            }


            .inventory-header {
                align-items: flex-start;
                flex-direction: column;
            }


            .inventory-new-button {
                width: 100%;
            }


            .inventory-section {
                padding: 14px;
            }


            .section-heading {
                align-items: flex-start;
                flex-direction: column;
            }


            .inventory-table-section {
                padding: 10px;
            }


            .inventory-table-title {
                align-items: flex-start;
                flex-direction: column;
            }


            .inventory-modal {
                padding: 10px;
            }

        }
    </style>



    {{-- ========================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ========================================================= --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {


            /* =====================================================
               SEARCH
            ===================================================== */

            const inventorySearch =
                document.getElementById('inventorySearch');


            if (inventorySearch) {

                let searchTimer;


                inventorySearch.addEventListener(
                    'input',
                    function() {

                        clearTimeout(searchTimer);


                        searchTimer = setTimeout(
                            () => {
                                this.form.submit();
                            },
                            400
                        );

                    }
                );

            }



            /* =====================================================
               CREATE MODAL
            ===================================================== */

            const createModal =
                document.getElementById('createItemModal');


            const openCreateButton =
                document.getElementById('openCreateModal');


            function openCreateModal() {

                createModal.classList.add('is-open');


                createModal.setAttribute(
                    'aria-hidden',
                    'false'
                );


                document
                    .getElementById('create_name')
                    ?.focus();

            }


            function closeCreateModal() {

                createModal.classList.remove('is-open');


                createModal.setAttribute(
                    'aria-hidden',
                    'true'
                );

            }


            if (openCreateButton) {

                openCreateButton.addEventListener(
                    'click',
                    openCreateModal
                );

            }


            createModal
                .querySelectorAll(
                    '[data-close-create-modal]'
                )
                .forEach(function(element) {

                    element.addEventListener(
                        'click',
                        closeCreateModal
                    );

                });



            /* =====================================================
               EDIT MODAL
            ===================================================== */

            const editModal =
                document.getElementById('editItemModal');


            const editForm =
                document.getElementById('editItemForm');


            function openEditModal(item) {

                document.getElementById(
                        'edit_name'
                    ).value =
                    item.name ?? '';


                document.getElementById(
                        'edit_category_id'
                    ).value =
                    item.category_id ?? '';


                document.getElementById(
                        'edit_inventory_location_id'
                    ).value =
                    item.inventory_location_id ?? '';


                document.getElementById(
                        'edit_unit_id'
                    ).value =
                    item.unit_id ?? '';


                document.getElementById(
                        'edit_inventory_type'
                    ).value =
                    item.inventory_type ?? '';


                document.getElementById(
                        'edit_price'
                    ).value =
                    item.price ?? 0;


                document.getElementById(
                        'edit_description'
                    ).value =
                    item.description ?? '';


                editForm.action =
                    "{{ url('/inventory') }}/" +
                    item.id;


                editModal.classList.add('is-open');


                editModal.setAttribute(
                    'aria-hidden',
                    'false'
                );

            }


            function closeEditModal() {

                editModal.classList.remove('is-open');


                editModal.setAttribute(
                    'aria-hidden',
                    'true'
                );

            }


            document
                .querySelectorAll(
                    '.edit-inventory-button'
                )
                .forEach(function(button) {

                    button.addEventListener(
                        'click',
                        function() {

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

                        }
                    );

                });


            editModal
                .querySelectorAll(
                    '[data-close-edit-modal]'
                )
                .forEach(function(element) {

                    element.addEventListener(
                        'click',
                        closeEditModal
                    );

                });



            /* =====================================================
               ESCAPE
            ===================================================== */

            document.addEventListener(
                'keydown',
                function(event) {

                    if (event.key !== 'Escape') {
                        return;
                    }


                    closeCreateModal();

                    closeEditModal();

                }
            );

        });
    </script>

</x-app-layout>