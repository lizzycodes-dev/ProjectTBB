<x-app-layout>

    <div class="purchase-page">

            {{-- =====================================================
            HEADER
            ===================================================== --}}
        <div class="purchase-header">

            <div>
                <h1>Purchases &amp; Expenses</h1>
                <p>Manage inventory purchases, suppliers, and purchase expenses.</p>
            </div>

        </div>


        {{-- =====================================================
            SUCCESS MESSAGE
            ===================================================== --}}
        @if (session('success'))
        <div class="purchase-success-message">
            {{ session('success') }}
        </div>
        @endif


        {{-- =====================================================
            TABS
            ===================================================== --}}
        @php
            $activeTab = $tab ?? request('tab', 'purchases');
        @endphp

        <div class="purchase-tabs" role="tablist">

            <a
                href="{{ route('purchases.index', ['tab' => 'purchases']) }}"
                class="purchase-tab {{ $activeTab === 'purchases' ? 'is-active' : '' }}"
                role="tab"
                aria-selected="{{ $activeTab === 'purchases' ? 'true' : 'false' }}">
                Purchases
                <span class="purchase-tab-count">{{ $purchases->total() }}</span>
            </a>

            <a
                href="{{ route('purchases.index', ['tab' => 'expenses']) }}"
                class="purchase-tab {{ $activeTab === 'expenses' ? 'is-active' : '' }}"
                role="tab"
                aria-selected="{{ $activeTab === 'expenses' ? 'true' : 'false' }}">
                Expenses
                <span class="purchase-tab-count">{{ $expenses->total() }}</span>
            </a>

        </div>


        {{-- =====================================================
            TAB PANEL: PURCHASES
            ===================================================== --}}
        <div class="purchase-tab-panel" data-panel="purchases" @if($activeTab !== 'purchases') hidden @endif>

            <div class="purchase-tab-actions">

                <button
                    type="button"
                    class="purchase-secondary-button"
                    id="openSupplierModal">
                    Manage Suppliers
                </button>

                <button
                    type="button"
                    class="purchase-primary-button"
                    id="openPurchaseModal">
                    <span>+</span>
                    Add Purchase
                </button>

            </div>

            <div class="purchase-card">

                <div class="purchase-card-header">
                    <div>
                        <h2>Purchase Records</h2>
                        <p>View recorded inventory purchases and their expenses.</p>
                    </div>
                </div>

                <div class="purchase-table-wrapper">

                    <table class="purchase-table">

                        <thead>
                            <tr>
                                <th>DATE</th>
                                <th>REFERENCE</th>
                                <th>SUPPLIER</th>
                                <th>ITEMS</th>
                                <th>TOTAL</th>
                                <th>EXPENSE</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($purchases as $purchase)

                            <tr
                                class="purchase-record-row"
                                data-purchase-id="{{ $purchase->id }}">

                                <td>
                                    {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('M d, Y') }}
                                </td>

                                <td>
                                    {{ $purchase->reference_number ?? '—' }}
                                </td>

                                <td>
                                    {{ $purchase->supplier->name ?? '—' }}
                                </td>

                                <td>
                                    {{ $purchase->purchaseItems->count() }}
                                    {{ $purchase->purchaseItems->count() === 1 ? 'item' : 'items' }}
                                </td>

                                <td>
                                    ₱{{ number_format(
                                        $purchase->purchaseItems->sum('subtotal'),
                                        2
                                    ) }}
                                </td>

                                <td>
                                    @if ($purchase->expense)
                                    ₱{{ number_format($purchase->expense->amount, 2) }}
                                    @else
                                    —
                                    @endif
                                </td>

                            </tr>

                            @empty

                            <tr>
                                <td colspan="6" class="purchase-empty">
                                    No purchase records yet.
                                </td>
                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                @if ($purchases->hasPages())

                <div class="purchase-pagination">
                    {{ $purchases->links() }}
                </div>

                @endif

            </div>

        </div>


        {{-- =====================================================
            TAB PANEL: EXPENSES
            ===================================================== --}}
        <div class="purchase-tab-panel" data-panel="expenses" @if($activeTab !== 'expenses') hidden @endif>

            <div class="purchase-tab-actions">

                <div class="expense-total-badge">
                    Total Expenses: <strong>₱{{ number_format($totalExpenses, 2) }}</strong>
                </div>

                <button
                    type="button"
                    class="purchase-primary-button"
                    id="openExpenseModal">
                    <span>+</span>
                    Add Expense
                </button>

            </div>

            <div class="purchase-card">

                <div class="purchase-card-header">
                    <div>
                        <h2>Expense Records</h2>
                        <p>Expenses from purchases and other operating costs.</p>
                    </div>
                </div>

                <div class="purchase-table-wrapper">

                    <table class="purchase-table">

                        <thead>
                            <tr>
                                <th>DATE</th>
                                <th>DESCRIPTION</th>
                                <th>CATEGORY</th>
                                <th>SOURCE</th>
                                <th>NOTES</th>
                                <th>AMOUNT</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($expenses as $expense)

                            <tr>

                                <td>
                                    {{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}
                                </td>

                                <td>
                                    {{ $expense->description }}
                                </td>

                                <td>
                                    @if ($expense->category)
                                        <span class="purchase-category-badge">
                                            {{ $expense->category }}
                                        </span>
                                    @else
                                        <span class="supplier-muted">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($expense->purchase)
                                        Purchase · {{ $expense->purchase->reference_number ?? ('PO #' . $expense->purchase->id) }}
                                        @if ($expense->purchase->supplier)
                                            <br>
                                            <small class="supplier-muted">
                                                {{ $expense->purchase->supplier->name }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="supplier-muted">Manual entry</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $expense->notes ?: '—' }}
                                </td>

                                <td>
                                    ₱{{ number_format($expense->amount, 2) }}
                                </td>

                            </tr>

                            @empty

                            <tr>
                                <td colspan="6" class="purchase-empty">
                                    No expense records yet.
                                </td>
                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                @if ($expenses->hasPages())

                <div class="purchase-pagination">
                    {{ $expenses->links() }}
                </div>

                @endif

            </div>

        </div>


    {{-- =====================================================
     ADD PURCHASE MODAL
     ===================================================== --}}
    <div
        id="purchaseModal"
        class="purchase-modal"
        aria-hidden="true">

        <div
            class="purchase-modal-backdrop"
            id="closePurchaseModalBackdrop">
        </div>

        <div
            class="purchase-modal-content"
            role="dialog"
            aria-modal="true">

            <div class="purchase-modal-header">

                <div>
                    <h2>Add Purchase</h2>
                    <p>
                        Select recent stock-in records and record their purchase details.
                    </p>
                </div>

                <button
                    type="button"
                    class="purchase-modal-close"
                    id="closePurchaseModal">
                    &times;
                </button>

            </div>


            <form
                method="POST"
                action="{{ route('purchases.store') }}"
                id="purchaseForm">

                @csrf

                <div class="purchase-modal-body">


                    {{-- =================================================
                     PURCHASE DETAILS
                     ================================================= --}}
                    <div class="purchase-section">

                        <div class="purchase-section-title">
                            Purchase Details
                        </div>

                        <div class="purchase-form-grid">

                            <div class="purchase-form-group">

                                <label for="supplier_id">
                                    Supplier
                                </label>

                                <select
                                    name="supplier_id"
                                    id="supplier_id"
                                    required>

                                    <option value="">
                                        Select supplier
                                    </option>

                                    @foreach ($activeSuppliers as $supplier)

                                    <option value="{{ $supplier->id }}">
                                        {{ $supplier->name }}
                                    </option>

                                    @endforeach

                                </select>

                            </div>


                            <div class="purchase-form-group">

                                <label for="purchase_date">
                                    Purchase Date
                                </label>

                                <input
                                    type="date"
                                    name="purchase_date"
                                    id="purchase_date"
                                    value="{{ now()->toDateString() }}"
                                    required>

                            </div>


                            <div class="purchase-form-group">

                                <label for="reference_number">
                                    Reference Number
                                </label>

                                <input
                                    type="text"
                                    name="reference_number"
                                    id="reference_number"
                                    placeholder="Optional">

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                     RECENT STOCK-IN
                     ================================================= --}}
                    <div class="purchase-section">

                        <div class="purchase-section-heading">

                            <div>
                                <div class="purchase-section-title">
                                    Recent Stock In
                                </div>

                                <p>
                                    Select the stock that belongs to this purchase.
                                </p>
                            </div>

                        </div>


                        <div class="purchase-stock-list">

                            @forelse ($stockIns as $stockIn)

                                <div
                                    class="purchase-stock-row"
                                    data-stock-row>

                                    <div class="purchase-stock-select">
                                        <input
                                            type="checkbox"
                                            class="stock-checkbox"
                                            data-stock-id="{{ $stockIn->id }}">
                                    </div>


                                    <div class="purchase-stock-info">

                                        <strong>
                                            {{ $stockIn->inventoryItem->name ?? 'Unknown Item' }}
                                        </strong>

                                        <small>
                                            Recorded:
                                            {{ number_format($stockIn->quantity) }}
                                            {{ $stockIn->inventoryItem->unit->name ?? '' }}
                                            · {{ \Carbon\Carbon::parse($stockIn->recorded_at)->format('M d, Y h:i A') }}
                                        </small>

                                    </div>


                                    <div class="purchase-stock-supplier">

                                        <span>Current Supplier</span>

                                        <strong>
                                            {{ $stockIn->supplier->name ?? 'No Supplier' }}
                                        </strong>

                                    </div>


                                    <div class="purchase-stock-cost">

                                        <label>
                                            Quantity
                                        </label>

                                        <input
                                            type="number"
                                            name="stock_ins[{{ $stockIn->id }}][quantity]"
                                            class="stock-quantity"
                                            data-stock-id="{{ $stockIn->id }}"
                                            min="0"
                                            step="1"
                                            value="{{ (int) $stockIn->quantity }}"
                                            placeholder="0"
                                            disabled>

                                    </div>


                                    <div class="purchase-stock-cost">

                                        <label>
                                            Unit Cost
                                        </label>

                                        <input
                                            type="number"
                                            name="stock_ins[{{ $stockIn->id }}][unit_cost]"
                                            class="stock-unit-cost"
                                            data-stock-id="{{ $stockIn->id }}"
                                            min="0"
                                            step="0.01"
                                            value="0"
                                            placeholder="0.00"
                                            disabled>

                                        <input
                                            type="hidden"
                                            name="stock_ins[{{ $stockIn->id }}][id]"
                                            value="{{ $stockIn->id }}"
                                            disabled>

                                    </div>

                                </div>

                                @empty

                                <div class="purchase-no-stock">
                                    <strong>No available stock-in records.</strong>
                                    <span>Add stock through Manage Stocks first.</span>
                                </div>

                            @endforelse

                        </div>

                    </div>


                    {{-- =================================================
                     EXPENSE
                     ================================================= --}}
                    <div class="purchase-section">

                        <div class="purchase-section-title">
                            Expense Information
                        </div>

                        <div class="purchase-form-grid">

                            <div class="purchase-form-group">

                                <label for="expense_category">
                                    Expense Category
                                </label>

                                <input
                                    type="text"
                                    name="expense_category"
                                    id="expense_category"
                                    value="Purchase"
                                    placeholder="Purchase">

                            </div>


                            <div class="purchase-form-group purchase-form-full">

                                <label for="expense_notes">
                                    Expense Notes
                                </label>

                                <textarea
                                    name="expense_notes"
                                    id="expense_notes"
                                    rows="3"
                                    placeholder="Optional notes about this expense"></textarea>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                     TOTAL
                     ================================================= --}}
                    <div class="purchase-total-box">

                        <span>
                            Total Purchase Amount
                        </span>

                        <strong id="purchaseTotal">
                            ₱0.00
                        </strong>

                    </div>

                </div>


                {{-- =================================================
                 FOOTER
                 ================================================= --}}
                <div class="purchase-modal-footer">

                    <button
                        type="button"
                        class="purchase-cancel-button"
                        id="cancelPurchaseButton">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="purchase-save-button">
                        Save Purchase
                    </button>

                </div>

            </form>

        </div>

    </div>

    {{-- =====================================================
     SUPPLIER MODAL
     ===================================================== --}}
    <div
        id="supplierModal"
        class="purchase-modal"
        aria-hidden="true">

        <div
            class="purchase-modal-backdrop"
            id="closeSupplierModalBackdrop">
        </div>

        <div
            class="purchase-modal-content supplier-modal-content">

            {{-- HEADER --}}
            <div class="purchase-modal-header">

                <div>
                    <h2>Manage Suppliers</h2>
                    <p>
                        Manage suppliers used for inventory purchases.
                    </p>
                </div>

                <button
                    type="button"
                    class="purchase-modal-close"
                    id="closeSupplierModal">
                    &times;
                </button>

            </div>


            {{-- BODY --}}
            <div class="purchase-modal-body">

                {{-- ADD SUPPLIER BUTTON --}}
                <div class="supplier-toolbar">

                    <div>
                        <strong>Suppliers</strong>
                        <span>
                            {{ $allSuppliers->count() }}
                            {{ $allSuppliers->count() === 1 ? 'supplier' : 'suppliers' }}
                        </span>
                    </div>

                    <button
                        type="button"
                        class="purchase-primary-button"
                        id="openAddSupplierForm">
                        <span>+</span>
                        Add Supplier
                    </button>

                </div>


                {{-- ADD SUPPLIER FORM --}}
                <div
                    id="addSupplierForm"
                    class="supplier-form-panel"
                    hidden>

                    <div class="supplier-form-header">

                        <div>
                            <strong>Add Supplier</strong>
                            <span>Enter the supplier information below.</span>
                        </div>

                        <button
                            type="button"
                            class="supplier-form-close"
                            id="closeAddSupplierForm">
                            &times;
                        </button>

                    </div>

                    <form
                        method="POST"
                        action="{{ route('suppliers.store') }}">

                        @csrf

                        <div class="supplier-form-grid">

                            <div class="supplier-form-group">

                                <label for="supplier_name">
                                    Supplier Name
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    id="supplier_name"
                                    placeholder="e.g. Marketplace"
                                    required>

                            </div>


                            <div class="supplier-form-group">

                                <label for="supplier_contact_person">
                                    Contact Person
                                </label>

                                <input
                                    type="text"
                                    name="contact_person"
                                    id="supplier_contact_person"
                                    placeholder="Optional">

                            </div>


                            <div class="supplier-form-group">

                                <label for="supplier_phone">
                                    Phone
                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    id="supplier_phone"
                                    placeholder="Optional">

                            </div>


                            <div class="supplier-form-group">

                                <label for="supplier_email">
                                    Email
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    id="supplier_email"
                                    placeholder="Optional">

                            </div>


                            <div class="supplier-form-group supplier-form-full">

                                <label for="supplier_address">
                                    Address
                                </label>

                                <textarea
                                    name="address"
                                    id="supplier_address"
                                    rows="2"
                                    placeholder="Optional"></textarea>

                            </div>

                        </div>

                        <div class="supplier-form-actions">

                            <button
                                type="button"
                                class="purchase-cancel-button"
                                id="cancelAddSupplier">
                                Cancel
                            </button>

                            <button
                                type="submit"
                                class="purchase-save-button">
                                Save Supplier
                            </button>

                        </div>

                    </form>

                </div>


                {{-- SUPPLIER FILTER BAR --}}
                <div class="supplier-filters" id="supplierFilterBar">

                    <div class="supplier-search-wrapper">
                        <input
                            type="search"
                            id="supplierSearch"
                            class="supplier-search-input"
                            placeholder="Search suppliers by name, contact, phone, or email..."
                            autocomplete="off">
                    </div>

                    <select id="supplierStatusFilter" class="supplier-filter-select">
                        <option value="all">All Suppliers</option>
                        <option value="active">Active only</option>
                        <option value="archived">Archived only</option>
                    </select>

                    <button
                        type="button"
                        id="supplierResetFilters"
                        class="supplier-reset-button">
                        Reset
                    </button>

                </div>


                {{-- SUPPLIER LIST --}}
                <div class="supplier-list" id="supplierList">

                    @forelse ($allSuppliers as $supplier)

                    @php
                        $searchBlob = strtolower(trim(
                            ($supplier->name ?? '') . ' ' .
                            ($supplier->contact_person ?? '') . ' ' .
                            ($supplier->phone ?? '') . ' ' .
                            ($supplier->email ?? '')
                        ));

                        $statusKey = $supplier->is_active ? 'active' : 'archived';
                    @endphp

                    <div
                        class="supplier-entry"
                        data-supplier-entry
                        data-name="{{ $searchBlob }}"
                        data-status="{{ $statusKey }}">

                        {{-- Visible supplier row --}}
                        <div class="supplier-row">

                            <div class="supplier-main">
                                <strong>{{ $supplier->name }}</strong>
                                <span>{{ $supplier->contact_person ?: 'No contact person' }}</span>
                            </div>

                            <div class="supplier-contact">
                                @if ($supplier->phone)
                                    <span>{{ $supplier->phone }}</span>
                                @endif

                                @if ($supplier->email)
                                    <span>{{ $supplier->email }}</span>
                                @endif

                                @if (!$supplier->phone && !$supplier->email)
                                    <span class="supplier-muted">No contact information</span>
                                @endif
                            </div>

                            <div class="supplier-status">
                                @if ($supplier->is_active)
                                    <span class="supplier-status-badge active">Active</span>
                                @else
                                    <span class="supplier-status-badge inactive">Inactive</span>
                                @endif
                            </div>

                            <div class="supplier-actions">

                                <button
                                    type="button"
                                    class="supplier-edit-button toggle-supplier"
                                    data-target="supplier-details-{{ $supplier->id }}"
                                    aria-expanded="false">
                                    <span class="toggle-label">Edit</span>
                                    <span class="toggle-icon">＋</span>
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('suppliers.toggle-active', $supplier->id) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="supplier-toggle-button">
                                        {{ $supplier->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                            </div>

                        </div>


                        {{-- Hidden edit panel --}}
                        <div
                            id="supplier-details-{{ $supplier->id }}"
                            class="supplier-details"
                            hidden>

                            <div class="supplier-details-panel">

                                <div class="supplier-details-header">
                                    <h3>{{ $supplier->name }} — Supplier Details</h3>
                                    <p>Update the supplier information or change its status.</p>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('suppliers.update', $supplier->id) }}">

                                    @csrf
                                    @method('PUT')

                                    <div class="supplier-form-grid">

                                        <div class="supplier-form-group">
                                            <label for="edit_supplier_name_{{ $supplier->id }}">
                                                Supplier Name
                                            </label>
                                            <input
                                                type="text"
                                                id="edit_supplier_name_{{ $supplier->id }}"
                                                name="name"
                                                value="{{ $supplier->name }}"
                                                required>
                                        </div>

                                        <div class="supplier-form-group">
                                            <label for="edit_supplier_contact_{{ $supplier->id }}">
                                                Contact Person
                                            </label>
                                            <input
                                                type="text"
                                                id="edit_supplier_contact_{{ $supplier->id }}"
                                                name="contact_person"
                                                value="{{ $supplier->contact_person }}"
                                                placeholder="Optional">
                                        </div>

                                        <div class="supplier-form-group">
                                            <label for="edit_supplier_phone_{{ $supplier->id }}">
                                                Phone
                                            </label>
                                            <input
                                                type="text"
                                                id="edit_supplier_phone_{{ $supplier->id }}"
                                                name="phone"
                                                value="{{ $supplier->phone }}"
                                                placeholder="Optional">
                                        </div>

                                        <div class="supplier-form-group">
                                            <label for="edit_supplier_email_{{ $supplier->id }}">
                                                Email
                                            </label>
                                            <input
                                                type="email"
                                                id="edit_supplier_email_{{ $supplier->id }}"
                                                name="email"
                                                value="{{ $supplier->email }}"
                                                placeholder="Optional">
                                        </div>

                                        <div class="supplier-form-group supplier-form-full">
                                            <label for="edit_supplier_address_{{ $supplier->id }}">
                                                Address
                                            </label>
                                            <textarea
                                                id="edit_supplier_address_{{ $supplier->id }}"
                                                name="address"
                                                rows="2"
                                                placeholder="Optional">{{ $supplier->address }}</textarea>
                                        </div>

                                    </div>

                                    <div class="supplier-form-actions">
                                        <button
                                            type="button"
                                            class="purchase-cancel-button toggle-supplier-close"
                                            data-target="supplier-details-{{ $supplier->id }}">
                                            Cancel
                                        </button>

                                        <button type="submit" class="purchase-save-button">
                                            Save Changes
                                        </button>
                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>

                    @empty

                    <div class="purchase-no-stock">
                        <strong>No suppliers yet.</strong>
                        <span>Add your first supplier to use it when recording purchases.</span>
                    </div>

                    @endforelse

                </div>

                {{-- Message shown when the filters hide every supplier --}}
                <div id="supplierFilterEmpty" class="purchase-no-stock" hidden>
                    <strong>No suppliers match your filters.</strong>
                    <span>Try a different search term or clear the filter.</span>
                </div>

            </div>

        </div>

    </div>
    {{-- =====================================================
     PURCHASE DETAILS MODAL
     ===================================================== --}}
    <div id="purchaseDetailsModal" class="purchase-modal" aria-hidden="true">
        <div class="purchase-modal-backdrop" id="closePurchaseDetailsBackdrop"></div>

        <div class="purchase-modal-content purchase-details-modal-content">
            <div class="purchase-modal-header">
                <div>
                    <h2>Purchase Details</h2>
                    <p id="purchaseDetailsSubtitle">Purchase information</p>
                </div>

                <button
                    type="button"
                    class="purchase-modal-close"
                    id="closePurchaseDetails">
                    &times;
                </button>
            </div>

            <div class="purchase-modal-body">

                <div class="purchase-details-summary">
                    <div>
                        <span>Supplier</span>
                        <strong id="detailsSupplier">—</strong>
                    </div>

                    <div>
                        <span>Purchase Date</span>
                        <strong id="detailsDate">—</strong>
                    </div>

                    <div>
                        <span>Reference</span>
                        <strong id="detailsReference">—</strong>
                    </div>
                </div>

                <div class="purchase-details-section">
                    <div class="purchase-details-section-header">
                        <div>
                            <h3>Purchase Items</h3>
                            <p>Items included in this purchase.</p>
                        </div>
                    </div>

                    <div class="purchase-table-wrapper">
                        <table class="purchase-table purchase-details-table">
                            <thead>
                                <tr>
                                    <th>ITEM</th>
                                    <th>QUANTITY</th>
                                    <th>UNIT COST</th>
                                    <th>SUBTOTAL</th>
                                </tr>
                            </thead>

                            <tbody id="purchaseDetailsItems">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="purchase-details-total">
                    <span>Purchase Total</span>
                    <strong id="detailsTotal">₱0.00</strong>
                </div>

                <div class="purchase-details-expense">
                    <div class="purchase-details-expense-header">
                        <h3>Expense</h3>
                    </div>

                    <div class="purchase-details-expense-grid">
                        <div>
                            <span>Category</span>
                            <strong id="detailsExpenseCategory">—</strong>
                        </div>

                        <div>
                            <span>Amount</span>
                            <strong id="detailsExpenseAmount">₱0.00</strong>
                        </div>

                        <div class="purchase-details-full">
                            <span>Notes</span>
                            <strong id="detailsExpenseNotes">—</strong>
                        </div>
                    </div>
                </div>

                <div class="purchase-details-notes">
                    <span>Purchase Notes</span>
                    <strong id="detailsPurchaseNotes">—</strong>
                </div>

            </div>

            <div class="purchase-modal-footer">
                <button
                    type="button"
                    class="purchase-cancel-button"
                    id="closePurchaseDetailsButton">
                    Close
                </button>
            </div>
        </div>
    </div>
    {{-- =====================================================
     JAVASCRIPT
     ===================================================== --}}
    <script>
        const purchaseDetailsData = @json($purchaseDetails);
    </script>
    <script>
        const purchaseDetailsModal = document.getElementById('purchaseDetailsModal');
        const closePurchaseDetails = document.getElementById('closePurchaseDetails');
        const closePurchaseDetailsButton = document.getElementById('closePurchaseDetailsButton');
        const closePurchaseDetailsBackdrop = document.getElementById('closePurchaseDetailsBackdrop');

        function openPurchaseDetails(purchaseId) {
            if (!purchaseDetailsModal) return;

            const purchase = purchaseDetailsData[purchaseId];

            if (!purchase) {
                console.error('Purchase details not found:', purchaseId);
                return;
            }

            document.getElementById('purchaseDetailsSubtitle').textContent =
                `${purchase.date} · ${purchase.reference}`;

            document.getElementById('detailsSupplier').textContent =
                purchase.supplier;

            document.getElementById('detailsDate').textContent =
                purchase.date;

            document.getElementById('detailsReference').textContent =
                purchase.reference;

            const itemsBody = document.getElementById('purchaseDetailsItems');

            itemsBody.innerHTML = '';

            purchase.items.forEach(item => {
                const row = document.createElement('tr');

                const quantity = Number(item.quantity || 0);
                const unitCost = Number(item.unit_cost || 0);
                const subtotal = Number(item.subtotal || 0);

                row.innerHTML = `
            <td>
                <strong>${item.name}</strong>
            </td>

            <td>
                ${quantity.toLocaleString(undefined, {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 3
                })}
                ${item.unit ? item.unit : ''}
            </td>

            <td>
                ₱${unitCost.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}
            </td>

            <td>
                ₱${subtotal.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}
            </td>
        `;

                itemsBody.appendChild(row);
            });

            document.getElementById('detailsTotal').textContent =
                `₱${Number(purchase.total || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}`;

            if (purchase.expense) {
                document.getElementById('detailsExpenseCategory').textContent =
                    purchase.expense.category || 'Purchase';

                document.getElementById('detailsExpenseAmount').textContent =
                    `₱${Number(purchase.expense.amount || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`;

                document.getElementById('detailsExpenseNotes').textContent =
                    purchase.expense.notes || '—';
            } else {
                document.getElementById('detailsExpenseCategory').textContent = '—';
                document.getElementById('detailsExpenseAmount').textContent = '₱0.00';
                document.getElementById('detailsExpenseNotes').textContent = '—';
            }

            document.getElementById('detailsPurchaseNotes').textContent =
                purchase.notes || '—';

            purchaseDetailsModal.classList.add('is-open');
            purchaseDetailsModal.setAttribute('aria-hidden', 'false');
        }

        function closePurchaseDetailsModal() {
            if (!purchaseDetailsModal) return;

            purchaseDetailsModal.classList.remove('is-open');
            purchaseDetailsModal.setAttribute('aria-hidden', 'true');
        }

        document.querySelectorAll('.purchase-record-row').forEach(row => {
            row.addEventListener('click', function() {
                const purchaseId = this.dataset.purchaseId;

                if (purchaseId) {
                    openPurchaseDetails(purchaseId);
                }
            });
        });

        if (closePurchaseDetails) {
            closePurchaseDetails.addEventListener('click', closePurchaseDetailsModal);
        }

        if (closePurchaseDetailsButton) {
            closePurchaseDetailsButton.addEventListener('click', closePurchaseDetailsModal);
        }

        if (closePurchaseDetailsBackdrop) {
            closePurchaseDetailsBackdrop.addEventListener('click', closePurchaseDetailsModal);
        }
        document.addEventListener('DOMContentLoaded', function() {

            /*
            |--------------------------------------------------------------------------
            | Purchase Modal
            |--------------------------------------------------------------------------
            */

            const purchaseModal = document.getElementById('purchaseModal');
            const openPurchaseModal = document.getElementById('openPurchaseModal');
            const closePurchaseModal = document.getElementById('closePurchaseModal');
            const cancelPurchaseButton = document.getElementById('cancelPurchaseButton');
            const closePurchaseModalBackdrop =
                document.getElementById('closePurchaseModalBackdrop');


            function openPurchase() {

                if (!purchaseModal) return;

                purchaseModal.classList.add('is-open');
                purchaseModal.setAttribute('aria-hidden', 'false');

                // Reset stock-in rows to a clean state every time
                resetPurchaseRows();

            }


            function closePurchase() {

                if (!purchaseModal) return;

                purchaseModal.classList.remove('is-open');
                purchaseModal.setAttribute('aria-hidden', 'true');

            }


            if (openPurchaseModal) {
                openPurchaseModal.addEventListener('click', openPurchase);
            }

            if (closePurchaseModal) {
                closePurchaseModal.addEventListener('click', closePurchase);
            }

            if (cancelPurchaseButton) {
                cancelPurchaseButton.addEventListener('click', closePurchase);
            }

            if (closePurchaseModalBackdrop) {
                closePurchaseModalBackdrop.addEventListener('click', closePurchase);
            }


            /*
            |--------------------------------------------------------------------------
            | Supplier Modal
            |--------------------------------------------------------------------------
            */

            const supplierModal = document.getElementById('supplierModal');
            const openSupplierModal = document.getElementById('openSupplierModal');
            const closeSupplierModal = document.getElementById('closeSupplierModal');
            const closeSupplierModalBackdrop =
                document.getElementById('closeSupplierModalBackdrop');


            function openSupplier() {

                if (!supplierModal) return;

                supplierModal.classList.add('is-open');
                supplierModal.setAttribute('aria-hidden', 'false');

            }


            function closeSupplier() {

                if (!supplierModal) return;

                supplierModal.classList.remove('is-open');
                supplierModal.setAttribute('aria-hidden', 'true');

            }

            /*
            |--------------------------------------------------------------------------
            | Expense Modal
            |--------------------------------------------------------------------------
            */

            const expenseModal = document.getElementById('expenseModal');
            const openExpenseModal = document.getElementById('openExpenseModal');
            const closeExpenseModal = document.getElementById('closeExpenseModal');
            const cancelExpenseButton = document.getElementById('cancelExpenseButton');
            const closeExpenseModalBackdrop = document.getElementById('closeExpenseModalBackdrop');
            const expenseForm = document.getElementById('expenseForm');

            function openExpense() {
                if (!expenseModal) return;

                // Reset form fields to a fresh state each time the modal opens
                if (expenseForm) {
                    expenseForm.reset();

                    const dateField = document.getElementById('expense_date');
                    if (dateField) dateField.value = new Date().toISOString().split('T')[0];
                }

                expenseModal.classList.add('is-open');
                expenseModal.setAttribute('aria-hidden', 'false');
            }

            function closeExpense() {
                if (!expenseModal) return;

                expenseModal.classList.remove('is-open');
                expenseModal.setAttribute('aria-hidden', 'true');
            }

            if (openExpenseModal) {
                openExpenseModal.addEventListener('click', openExpense);
            }

            if (closeExpenseModal) {
                closeExpenseModal.addEventListener('click', closeExpense);
            }

            if (cancelExpenseButton) {
                cancelExpenseButton.addEventListener('click', closeExpense);
            }

            if (closeExpenseModalBackdrop) {
                closeExpenseModalBackdrop.addEventListener('click', closeExpense);
            }

            // Escape closes any open purchase/expense modal
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (expenseModal && expenseModal.classList.contains('is-open')) {
                        closeExpense();
                    }
                }
            });

            /*
            |--------------------------------------------------------------------------
            | Supplier Edit Dropdowns (toggle open / close)
            |--------------------------------------------------------------------------
            */

            document.querySelectorAll('.toggle-supplier').forEach(function(button) {
                button.addEventListener('click', function() {
                    const panel = document.getElementById(button.dataset.target);
                    if (!panel) return;

                    const isOpen = !panel.hidden;

                    // Close any other open supplier panel first
                    document.querySelectorAll('.supplier-details').forEach(function(other) {
                        if (other !== panel) other.hidden = true;
                    });
                    document.querySelectorAll('.toggle-supplier').forEach(function(otherBtn) {
                        if (otherBtn !== button) {
                            otherBtn.setAttribute('aria-expanded', 'false');
                            const lbl = otherBtn.querySelector('.toggle-label');
                            const icn = otherBtn.querySelector('.toggle-icon');
                            if (lbl) lbl.textContent = 'Edit';
                            if (icn) icn.textContent = '＋';
                        }
                    });

                    panel.hidden = isOpen;
                    button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');

                    const label = button.querySelector('.toggle-label');
                    const icon = button.querySelector('.toggle-icon');

                    if (label) label.textContent = isOpen ? 'Edit' : 'Close';
                    if (icon) icon.textContent = isOpen ? '＋' : '−';

                    if (!isOpen) {
                        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            });

            document.querySelectorAll('.toggle-supplier-close').forEach(function(button) {
                button.addEventListener('click', function() {
                    const panel = document.getElementById(button.dataset.target);
                    if (!panel) return;

                    panel.hidden = true;

                    const opener = document.querySelector(
                        '.toggle-supplier[data-target="' + button.dataset.target + '"]'
                    );

                    if (opener) {
                        opener.setAttribute('aria-expanded', 'false');

                        const label = opener.querySelector('.toggle-label');
                        const icon = opener.querySelector('.toggle-icon');

                        if (label) label.textContent = 'Edit';
                        if (icon) icon.textContent = '＋';
                    }
                });
            });


            /*
            |--------------------------------------------------------------------------
            | Supplier Filters (search + status)
            |--------------------------------------------------------------------------
            */

            const supplierSearch = document.getElementById('supplierSearch');
            const supplierStatusFilter = document.getElementById('supplierStatusFilter');
            const supplierResetFilters = document.getElementById('supplierResetFilters');
            const supplierEntries = document.querySelectorAll('[data-supplier-entry]');
            const supplierFilterEmpty = document.getElementById('supplierFilterEmpty');

            function applySupplierFilters() {
                const search = (supplierSearch?.value || '').trim().toLowerCase();
                const status = supplierStatusFilter?.value || 'all';

                let visibleCount = 0;

                supplierEntries.forEach(function(entry) {
                    const name = entry.dataset.name || '';
                    const entryStatus = entry.dataset.status || 'active';

                    const matchesSearch = search === '' || name.includes(search);
                    const matchesStatus = status === 'all' || entryStatus === status;

                    const visible = matchesSearch && matchesStatus;
                    entry.hidden = !visible;

                    if (visible) visibleCount++;
                });

                if (supplierFilterEmpty) {
                    supplierFilterEmpty.hidden = visibleCount !== 0;
                }
            }

            if (supplierSearch) supplierSearch.addEventListener('input', applySupplierFilters);
            if (supplierStatusFilter) supplierStatusFilter.addEventListener('change', applySupplierFilters);

            if (supplierResetFilters) {
                supplierResetFilters.addEventListener('click', function() {
                    if (supplierSearch) supplierSearch.value = '';
                    if (supplierStatusFilter) supplierStatusFilter.value = 'all';
                    applySupplierFilters();
                });
            }

            // Make sure the filter bar resets whenever the modal is opened fresh
            if (openSupplierModal) {
                openSupplierModal.addEventListener('click', function() {
                    if (supplierSearch) supplierSearch.value = '';
                    if (supplierStatusFilter) supplierStatusFilter.value = 'all';
                    applySupplierFilters();

                    // Also close any open edit dropdowns
                    document.querySelectorAll('.supplier-details').forEach(function(p) {
                        p.hidden = true;
                    });
                    document.querySelectorAll('.toggle-supplier').forEach(function(b) {
                        b.setAttribute('aria-expanded', 'false');
                        const lbl = b.querySelector('.toggle-label');
                        const icn = b.querySelector('.toggle-icon');
                        if (lbl) lbl.textContent = 'Edit';
                        if (icn) icn.textContent = '＋';
                    });
                });
            }


            if (openSupplierModal) {
                openSupplierModal.addEventListener('click', openSupplier);
            }

            if (closeSupplierModal) {
                closeSupplierModal.addEventListener('click', closeSupplier);
            }

            if (closeSupplierModalBackdrop) {
                closeSupplierModalBackdrop.addEventListener('click', closeSupplier);
            }


            /*
            |--------------------------------------------------------------------------
            | Stock Selection + Purchase Total
            |--------------------------------------------------------------------------
            */

            const purchaseTotal = document.getElementById('purchaseTotal');

            function updatePurchaseTotal() {

                let total = 0;

                document.querySelectorAll('.purchase-stock-row').forEach(function(row) {

                    const checkbox      = row.querySelector('.stock-checkbox');
                    const quantityInput = row.querySelector('.stock-quantity');
                    const costInput     = row.querySelector('.stock-unit-cost');

                    if (!checkbox || !checkbox.checked) return;
                    if (!quantityInput || !costInput) return;

                    const quantity = parseFloat(quantityInput.value) || 0;
                    const unitCost = parseFloat(costInput.value) || 0;

                    if (quantity > 0 && unitCost > 0) {
                        total += quantity * unitCost;
                    }
                });

                if (purchaseTotal) {
                    purchaseTotal.textContent =
                        '₱' + total.toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                }
            }

            // Toggle row enabled state when the checkbox changes
            document.querySelectorAll('.stock-checkbox').forEach(function(checkbox) {

                checkbox.addEventListener('change', function() {

                    const row = this.closest('.purchase-stock-row');
                    if (!row) return;

                    const quantityInput = row.querySelector('.stock-quantity');
                    const costInput     = row.querySelector('.stock-unit-cost');
                    const hiddenId      = row.querySelector('input[type="hidden"][name*="[id]"]');

                    const enabled = this.checked;

                    if (quantityInput) quantityInput.disabled = !enabled;
                    if (costInput)     costInput.disabled = !enabled;
                    if (hiddenId)      hiddenId.disabled = !enabled;

                    // Reset unit cost when unchecking
                    if (!enabled && costInput) {
                        costInput.value = 0;
                    }

                    updatePurchaseTotal();
                });
            });

            // Live update while typing
            document.querySelectorAll('.stock-quantity, .stock-unit-cost').forEach(function(input) {
                input.addEventListener('input', updatePurchaseTotal);
            });

            // Reset every row when the modal opens
            function resetPurchaseRows() {
                document.querySelectorAll('.purchase-stock-row').forEach(function(row) {

                    const checkbox      = row.querySelector('.stock-checkbox');
                    const quantityInput = row.querySelector('.stock-quantity');
                    const costInput     = row.querySelector('.stock-unit-cost');
                    const hiddenId      = row.querySelector('input[type="hidden"][name*="[id]"]');

                    if (checkbox)      checkbox.checked = false;
                    if (quantityInput) quantityInput.disabled = true;
                    if (costInput) {
                        costInput.disabled = true;
                        costInput.value = 0;
                    }
                    if (hiddenId)      hiddenId.disabled = true;
                });

                updatePurchaseTotal();
            }

            updatePurchaseTotal();

            /*
            |--------------------------------------------------------------------------
            | Client-side guard — checked rows need quantity AND unit cost > 0
            |--------------------------------------------------------------------------
            */

            const purchaseForm = document.getElementById('purchaseForm');

            if (purchaseForm) {
                purchaseForm.addEventListener('submit', function(e) {

                    const checkedRows = Array.from(
                        document.querySelectorAll('.purchase-stock-row')
                    ).filter(row => row.querySelector('.stock-checkbox')?.checked);

                    if (checkedRows.length === 0) {
                        e.preventDefault();
                        alert('Please select at least one stock-in record.');
                        return;
                    }

                    for (const row of checkedRows) {

                        const quantityInput = row.querySelector('.stock-quantity');
                        const costInput     = row.querySelector('.stock-unit-cost');

                        const quantity = parseFloat(quantityInput?.value) || 0;
                        const unitCost = parseFloat(costInput?.value) || 0;

                        if (quantity <= 0 || unitCost <= 0) {
                            e.preventDefault();

                            const name = row.querySelector('.purchase-stock-info strong')
                                ?.textContent?.trim() || 'an item';

                            alert(
                                'Enter a quantity and unit cost greater than 0 for: ' + name
                            );
                            return;
                        }
                    }
                });
            }

        });

        /*
        |--------------------------------------------------------------------------
        | Add Supplier Form
        |--------------------------------------------------------------------------
        */

        const addSupplierForm =
            document.getElementById('addSupplierForm');

        const openAddSupplierForm =
            document.getElementById('openAddSupplierForm');

        const closeAddSupplierForm =
            document.getElementById('closeAddSupplierForm');

        const cancelAddSupplier =
            document.getElementById('cancelAddSupplier');


        function showAddSupplierForm() {

            if (!addSupplierForm) return;

            addSupplierForm.hidden = false;

            addSupplierForm.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });

        }


        function hideAddSupplierForm() {

            if (!addSupplierForm) return;

            addSupplierForm.hidden = true;

        }


        if (openAddSupplierForm) {
            openAddSupplierForm.addEventListener(
                'click',
                showAddSupplierForm
            );
        }

        if (closeAddSupplierForm) {
            closeAddSupplierForm.addEventListener(
                'click',
                hideAddSupplierForm
            );
        }

        if (cancelAddSupplier) {
            cancelAddSupplier.addEventListener(
                'click',
                hideAddSupplierForm
            );
        }
    </script>

    {{-- =====================================================
     ADD EXPENSE MODAL
     ===================================================== --}}
    <div
        id="expenseModal"
        class="purchase-modal"
        aria-hidden="true">

        <div
            class="purchase-modal-backdrop"
            id="closeExpenseModalBackdrop">
        </div>

        <div
            class="purchase-modal-content"
            role="dialog"
            aria-modal="true">

            <div class="purchase-modal-header">

                <div>
                    <h2>Add Expense</h2>
                    <p>Record an operating expense or any non-purchase cost.</p>
                </div>

                <button
                    type="button"
                    class="purchase-modal-close"
                    id="closeExpenseModal">
                    &times;
                </button>

            </div>


            <form
                method="POST"
                action="{{ route('expenses.store') }}"
                id="expenseForm">

                @csrf

                <div class="purchase-modal-body">

                    <div class="purchase-section">

                        <div class="purchase-section-title">
                            Expense Details
                        </div>

                        <div class="purchase-form-grid">

                            <div class="purchase-form-group purchase-form-full">

                                <label for="expense_description">
                                    Description
                                </label>

                                <input
                                    type="text"
                                    name="description"
                                    id="expense_description"
                                    maxlength="255"
                                    placeholder="e.g. Electricity bill, Cleaning supplies"
                                    required>

                            </div>


                            <div class="purchase-form-group">

                                <label for="expense_category_input">
                                    Category
                                </label>

                                <input
                                    type="text"
                                    name="category"
                                    id="expense_category_input"
                                    maxlength="255"
                                    placeholder="e.g. Utilities, Supplies">

                            </div>


                            <div class="purchase-form-group">

                                <label for="expense_amount">
                                    Amount
                                </label>

                                <input
                                    type="number"
                                    name="amount"
                                    id="expense_amount"
                                    min="0.01"
                                    step="0.01"
                                    placeholder="0.00"
                                    required>

                            </div>


                            <div class="purchase-form-group">

                                <label for="expense_date">
                                    Expense Date
                                </label>

                                <input
                                    type="date"
                                    name="expense_date"
                                    id="expense_date"
                                    value="{{ now()->toDateString() }}"
                                    max="{{ now()->toDateString() }}"
                                    required>

                            </div>


                            <div class="purchase-form-group">

                                <label for="expense_purchase_id">
                                    Link to Purchase <span style="color: var(--muted-foreground); font-weight: 400;">(optional)</span>
                                </label>

                                <select name="purchase_id" id="expense_purchase_id">
                                    <option value="">— No purchase —</option>

                                    @foreach ($linkablePurchases as $purchase)
                                        <option value="{{ $purchase->id }}">
                                            {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('M d, Y') }}
                                            ·
                                            {{ $purchase->reference_number ?? ('PO #' . $purchase->id) }}
                                            @if ($purchase->supplier)
                                                · {{ $purchase->supplier->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>

                            </div>


                            <div class="purchase-form-group purchase-form-full">

                                <label for="expense_notes">
                                    Notes
                                </label>

                                <textarea
                                    name="notes"
                                    id="expense_notes"
                                    rows="3"
                                    placeholder="Optional notes about this expense"></textarea>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="purchase-modal-footer">

                    <button
                        type="button"
                        class="purchase-cancel-button"
                        id="cancelExpenseButton">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="purchase-save-button">
                        Save Expense
                    </button>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>