<x-app-layout>

    <div class="purchase-page">

        {{-- =====================================================
         HEADER
         ===================================================== --}}
        <div class="purchase-header">

            <div>
                <h1>Purchase Management</h1>
                <p>
                    Manage inventory purchases, suppliers, and purchase expenses.
                </p>
            </div>

            <div class="purchase-header-actions">

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
         PURCHASE RECORDS
         ===================================================== --}}
        <div class="purchase-card">

            <div class="purchase-card-header">
                <div>
                    <h2>Purchase Records</h2>
                    <p>
                        View recorded inventory purchases and their expenses.
                    </p>
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


            {{-- Pagination --}}
            @if ($purchases->hasPages())

            <div class="purchase-pagination">
                {{ $purchases->links() }}
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
                                        name="stock_ins[{{ $stockIn->id }}][id]"
                                        value="{{ $stockIn->id }}"
                                        data-stock-id="{{ $stockIn->id }}">

                                </div>


                                <div class="purchase-stock-info">

                                    <strong>
                                        {{ $stockIn->inventoryItem->name ?? 'Unknown Item' }}
                                    </strong>

                                    <span>
                                        {{ number_format($stockIn->quantity, 2) }}
                                        {{ $stockIn->inventoryItem->unit->name ?? '' }}
                                    </span>

                                    <small>
                                        {{ \Carbon\Carbon::parse($stockIn->recorded_at)->format('M d, Y h:i A') }}
                                    </small>

                                </div>


                                <div class="purchase-stock-supplier">

                                    <span>
                                        Current Supplier
                                    </span>

                                    <strong>
                                        {{ $stockIn->supplier->name ?? 'No Supplier' }}
                                    </strong>

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
                                        disabled>

                                </div>

                            </div>

                            @empty

                            <div class="purchase-no-stock">

                                <strong>No available stock-in records.</strong>

                                <span>
                                    Add stock through Manage Stocks first.
                                </span>

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


                {{-- SUPPLIER LIST --}}
                <div class="supplier-list">

                    @forelse ($allSuppliers as $supplier)

                    <div class="supplier-row">

                        <div class="supplier-main">

                            <strong>
                                {{ $supplier->name }}
                            </strong>

                            <span>
                                {{ $supplier->contact_person ?: 'No contact person' }}
                            </span>

                        </div>


                        <div class="supplier-contact">

                            @if ($supplier->phone)
                            <span>{{ $supplier->phone }}</span>
                            @endif

                            @if ($supplier->email)
                            <span>{{ $supplier->email }}</span>
                            @endif

                            @if (!$supplier->phone && !$supplier->email)
                            <span class="supplier-muted">
                                No contact information
                            </span>
                            @endif

                        </div>


                        <div class="supplier-status">

                            @if ($supplier->is_active)

                            <span class="supplier-status-badge active">
                                Active
                            </span>

                            @else

                            <span class="supplier-status-badge inactive">
                                Inactive
                            </span>

                            @endif

                        </div>


                        <div class="supplier-actions">

                            <button
                                type="button"
                                class="supplier-edit-button"
                                data-supplier-id="{{ $supplier->id }}">
                                Edit
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

                    @empty

                    <div class="purchase-no-stock">

                        <strong>No suppliers yet.</strong>

                        <span>
                            Add your first supplier to use it when recording purchases.
                        </span>

                    </div>

                    @endforelse

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
            | Stock Selection
            |--------------------------------------------------------------------------
            */

            const stockCheckboxes =
                document.querySelectorAll('.stock-checkbox');

            const stockCosts =
                document.querySelectorAll('.stock-unit-cost');

            const purchaseTotal =
                document.getElementById('purchaseTotal');


            function updatePurchaseTotal() {

                let total = 0;

                stockCheckboxes.forEach(function(checkbox) {

                    if (!checkbox.checked) {
                        return;
                    }

                    const stockId = checkbox.dataset.stockId;

                    const costInput =
                        document.querySelector(
                            `.stock-unit-cost[data-stock-id="${stockId}"]`
                        );

                    if (!costInput) {
                        return;
                    }

                    const row =
                        checkbox.closest('.purchase-stock-row');

                    const quantityText =
                        row.querySelector('.purchase-stock-info span')?.textContent || '';

                    const quantity =
                        parseFloat(quantityText.replace(/[^0-9.-]+/g, '')) || 0;

                    const unitCost =
                        parseFloat(costInput.value) || 0;

                    total += quantity * unitCost;

                });


                if (purchaseTotal) {

                    purchaseTotal.textContent =
                        '₱' + total.toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                }

            }


            stockCheckboxes.forEach(function(checkbox) {

                checkbox.addEventListener('change', function() {

                    const stockId = this.dataset.stockId;

                    const costInput =
                        document.querySelector(
                            `.stock-unit-cost[data-stock-id="${stockId}"]`
                        );

                    if (costInput) {
                        costInput.disabled = !this.checked;
                    }

                    updatePurchaseTotal();

                });

            });


            stockCosts.forEach(function(input) {

                input.addEventListener('input', updatePurchaseTotal);

            });


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

</x-app-layout>