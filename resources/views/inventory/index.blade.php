<x-app-layout>
    <div class="inventory-page">
        <div class="inventory-header">
            <div>
                <h1>Inventory Management</h1>
                <p>View and monitor stock across your inventory locations.</p>
            </div>
        </div>

        <div class="inventory-summary">
            <div class="inventory-summary-card">
                <span class="summary-label">Active Items</span>
                <strong>{{ $activeItemCount }}</strong>
            </div>

            <div class="inventory-summary-card">
                <span class="summary-label">Low Stock Records</span>
                <strong>{{ $lowStockCount }}</strong>
            </div>
        </div>

        @if (session('success'))
        <div class="inventory-alert">
            {{ session('success') }}
        </div>
        @endif

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

        <section class="inventory-section">
            <div class="section-heading">
                <h2>Inventory Items</h2>
                <span>{{ $items->total() }} item(s)</span>
            </div>

            <form method="GET" action="{{ route('inventory.index') }}" class="inventory-search">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by item name or type..."
                    aria-label="Search inventory items">

                <button type="submit">Search</button>

                @if (request('search'))
                <a href="{{ route('inventory.index') }}">Clear</a>
                @endif
            </form>

            <div class="inventory-table-wrapper">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Type</th>
                            <th>Unit</th>
                            <th>Location</th>
                            <th>Current Stock</th>
                            <th>Reorder Level</th>
                            <th>Stock Status</th>
                            <th>Item Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($items as $item)
                        @forelse ($item->inventoryStocks as $stock)
                        <tr
                            class="inventory-clickable-row"
                            data-item-name="{{ $item->menu_name ?: $item->name }}"
                            data-unit-id="{{ $item->unit_id }}"
                            data-is-active="{{ $item->is_active ? '1' : '0' }}"
                            data-unit-url="{{ route('inventory.update-unit', $item) }}"
                            data-toggle-url="{{ route('inventory.toggle-active', $item) }}">
                            <td>{{ $item->menu_name ?: $item->name }}</td>
                            <td>{{ $item->inventory_type }}</td>
                            <td>{{ $item->unit?->abbreviation ?? '—' }}</td>
                            <td>{{ $stock->location?->name ?? '—' }}</td>
                            <td>
                                <form
                                    method="POST"
                                    action="{{ route('inventory.update-stock-quantity', $stock) }}"
                                    class="stock-quantity-form">
                                    @csrf
                                    @method('PATCH')

                                    <input
                                        type="number"
                                        name="current_quantity"
                                        class="stock-quantity-input"
                                        value="{{ number_format((float) $stock->current_quantity, 3, '.', '') }}"
                                        min="0"
                                        step="0.001"
                                        aria-label="Current stock for {{ $item->menu_name ?: $item->name }} at {{ $stock->location?->name }}"
                                        onchange="this.form.requestSubmit()">
                                </form>
                            </td>
                            <td>{{ number_format((float) $stock->reorder_level, 3) }}</td>
                            <td>
                                @if ($stock->current_quantity <= $stock->reorder_level)
                                    <span class="stock-status low">Low Stock</span>
                                    @else
                                    <span class="stock-status okay">In Stock</span>
                                    @endif
                            </td>

                            @if ($loop->first)
                            <td rowspan="{{ $item->inventoryStocks->count() }}">
                                @if ($item->is_active)
                                <span class="item-status active">Active</span>
                                @else
                                <span class="item-status inactive">Inactive</span>
                                @endif
                            </td>

                            @endif
                        </tr>
                        @empty
                        <tr
                            class="inventory-clickable-row"
                            data-item-name="{{ $item->menu_name ?: $item->name }}"
                            data-unit-id="{{ $item->unit_id }}"
                            data-is-active="{{ $item->is_active ? '1' : '0' }}"
                            data-unit-url="{{ route('inventory.update-unit', $item) }}"
                            data-toggle-url="{{ route('inventory.toggle-active', $item) }}">
                            <td>{{ $item->menu_name ?: $item->name }}</td>
                            <td>{{ $item->inventory_type }}</td>
                            <td>{{ $item->unit?->abbreviation ?? '—' }}</td>
                            <td colspan="4" class="no-stock-cell">No stock record yet</td>

                            <td>
                                @if ($item->is_active)
                                <span class="item-status active">Active</span>
                                @else
                                <span class="item-status inactive">Inactive</span>
                                @endif
                            </td>

                            <td>
                                <div class="action-stack">
                                    <form
                                        method="POST"
                                        action="{{ route('inventory.update-unit', $item) }}"
                                        class="unit-form">
                                        @csrf
                                        @method('PATCH')

                                        <label for="unit-{{ $item->id }}">Unit</label>
                                        <select
                                            id="unit-{{ $item->id }}"
                                            name="unit_id"
                                            class="unit-select">
                                            <option value="">Not set</option>

                                            @foreach ($units as $unit)
                                            <option
                                                value="{{ $unit->id }}"
                                                @selected($item->unit_id == $unit->id)>
                                                {{ $unit->name }} ({{ $unit->abbreviation }})
                                            </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="unit-save-button">
                                            Save Unit
                                        </button>
                                    </form>

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
                        @endforelse
                        @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                No inventory items found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="inventory-pagination">
                {{ $items->links() }}
            </div>
        </section>
    </div>
    <div id="inventoryModal" class="inventory-modal" aria-hidden="true">
        <div class="inventory-modal-backdrop" data-close-modal></div>

        <div
            class="inventory-modal-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="inventoryModalTitle">
            <div class="inventory-modal-header">
                <div>
                    <h2 id="inventoryModalTitle">Inventory Item</h2>
                    <p>Update this item’s unit or status.</p>
                </div>

                <button
                    type="button"
                    class="inventory-modal-close"
                    aria-label="Close"
                    data-close-modal>&times;</button>
            </div>

            <div class="inventory-modal-body">
                <div class="modal-item-name">
                    <span>Selected item</span>
                    <strong id="modalItemName"></strong>
                </div>

                <form id="modalUnitForm" method="POST">
                    @csrf
                    @method('PATCH')

                    <label for="modalUnitSelect">Unit of measurement</label>
                    <select id="modalUnitSelect" name="unit_id" class="modal-input">
                        <option value="">Not set</option>
                        @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">
                            {{ $unit->name }} ({{ $unit->abbreviation }})
                        </option>
                        @endforeach
                    </select>

                    <button type="submit" class="modal-primary-button">
                        Save Unit
                    </button>
                </form>

                <div class="modal-status-section">
                    <div>
                        <span class="modal-status-label">Item status</span>
                        <strong id="modalItemStatus"></strong>
                    </div>

                    <form id="modalToggleForm" method="POST">
                        @csrf
                        <button
                            type="submit"
                            id="modalToggleButton"
                            class="modal-toggle-button"></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <style>
        /* Pagination container */
        .inventory-pagination {
            display: flex;
            justify-content: right;
            margin-top: 22px;
        }

        /* Laravel pagination navigation */
        .inventory-pagination nav {
            display: flex;
            align-items: right;
            justify-content: right;
            gap: 6px;
        }

        /* Pagination links and current page */
        .inventory-pagination nav a,
        .inventory-pagination nav span[aria-current="page"] span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border: 1px solid #e8e1dc;
            border-radius: 8px;
            background: #fff;
            color: #5b4032;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        /* Hover state */
        .inventory-pagination nav a:hover {
            background: #f5f0eb;
            border-color: #cbb9aa;
        }

        /* Active page */
        .inventory-pagination nav span[aria-current="page"] span {
            background: #6f4e37;
            border-color: #6f4e37;
            color: #fff;
        }

        /* Disabled previous/next controls */
        .inventory-pagination nav span[aria-disabled="true"] span {
            display: inline-flex;
            align-items: right;
            justify-content: right;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border: 1px solid #eee5df;
            border-radius: 8px;
            background: #faf7f4;
            color: #b8aaa0;
            font-size: 13px;
        }

        /* Small screen spacing */
        @media (max-width: 480px) {
            .inventory-pagination nav {
                gap: 3px;
            }

            .inventory-pagination nav a,
            .inventory-pagination nav span[aria-current="page"] span,
            .inventory-pagination nav span[aria-disabled="true"] span {
                min-width: 32px;
                height: 32px;
                padding: 0 7px;
                font-size: 12px;
            }
        }

        .inventory-search button,
        .inventory-search a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            background: #6f4e37;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .inventory-search button:hover {
            background: #5b4032;
            transform: translateY(-1px);
        }

        .inventory-search a {
            background: #eee5df;
            color: #5b4032;
        }

        .inventory-search a:hover {
            background: #e3d5cb;
        }

        .inventory-search {
            margin-bottom: 16px;
        }

        .inventory-search input {
            width: 100%;
            max-width: 380px;
            padding: 10px 12px;
            border: 1px solid #d9cec6;
            border-radius: 8px;
            background: #fff;
            color: #3f3028;
            font-size: 13px;
        }

        .inventory-search input:focus {
            outline: 2px solid #a98568;
            outline-offset: 1px;
        }

        .stock-quantity-form {
            margin: 0;
        }

        .stock-quantity-input {
            width: 100px;
            max-width: 100%;
            padding: 5px 6px;
            border: 1px solid #d9cec6;
            border-radius: 6px;
            background: #fff;
            color: #3f3028;
            font-size: 12px;
        }

        .stock-quantity-input:focus {
            outline: 2px solid #a98568;
            outline-offset: 1px;
        }

        .inventory-clickable-row {
            cursor: pointer;
        }

        .inventory-clickable-row:hover td {
            background: #f8f1eb;
        }

        .inventory-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 16px;
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
            max-width: 420px;
            overflow: hidden;
            border: 1px solid #eee5df;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 16px 48px rgba(30, 20, 15, 0.2);
        }

        .inventory-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 20px;
            border-bottom: 1px solid #eee5df;
        }

        .inventory-modal-header h2 {
            margin: 0;
            color: #3f3028;
            font-size: 18px;
            font-weight: 700;
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
            line-height: 1;
            cursor: pointer;
        }

        .inventory-modal-body {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 20px;
        }

        .modal-item-name {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .modal-item-name span,
        .modal-status-label {
            color: #817168;
            font-size: 12px;
        }

        .modal-item-name strong {
            color: #3f3028;
            font-size: 16px;
        }

        #modalUnitForm {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        #modalUnitForm label {
            color: #5b4032;
            font-size: 13px;
            font-weight: 600;
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

        .modal-primary-button,
        .modal-toggle-button {
            width: fit-content;
            padding: 8px 12px;
            border: 0;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .modal-primary-button {
            background: #6f4e37;
            color: #fff;
        }

        .modal-primary-button:hover {
            background: #5b4032;
        }

        .modal-status-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-top: 16px;
            border-top: 1px solid #eee5df;
        }

        .modal-status-section>div {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        #modalItemStatus {
            color: #3f3028;
            font-size: 14px;
        }

        .modal-toggle-button.deactivate {
            background: #fff0e8;
            color: #a84718;
        }

        .modal-toggle-button.activate {
            background: #eaf5ed;
            color: #287344;
        }

        .inventory-page {
            padding: 28px;
            color: #3f3028;
        }

        .inventory-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .inventory-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .inventory-summary-card,
        .inventory-section {
            background: #fff;
            border: 1px solid #eee5df;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(60, 40, 25, 0.04);
        }

        .inventory-summary-card {
            padding: 18px 20px;
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

        .inventory-section {
            padding: 20px;
        }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .section-heading h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }

        .section-heading span {
            color: #817168;
            font-size: 13px;
        }

        .inventory-table-wrapper {
            overflow-x: auto;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .inventory-table th,
        .inventory-table td {
            padding: 13px 12px;
            border-bottom: 1px solid #f0eae5;
            white-space: nowrap;
            vertical-align: middle;
        }

        .inventory-table th {
            background: #faf7f4;
            color: #75645a;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .inventory-table td {
            font-size: 14px;
        }

        .action-stack {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .unit-form {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            margin-bottom: 2px;
        }

        .unit-form label {
            color: #75645a;
            font-size: 12px;
            font-weight: 600;
        }

        .unit-select {
            max-width: 180px;
            padding: 7px 8px;
            border: 1px solid #d9cec6;
            border-radius: 6px;
            background: #fff;
            color: #3f3028;
            font-size: 12px;
        }

        .unit-save-button {
            width: fit-content;
            padding: 7px 10px;
            border: 0;
            border-radius: 6px;
            background: #eee5df;
            color: #5b4032;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .unit-save-button:hover {
            background: #e3d5cb;
        }

        .item-status,
        .stock-status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .item-status.active,
        .stock-status.okay {
            background: #eaf5ed;
            color: #287344;
        }

        .item-status.inactive {
            background: #f1eeec;
            color: #75645a;
        }

        .stock-status.low {
            background: #fff0e8;
            color: #a84718;
        }

        .toggle-button {
            padding: 7px 11px;
            border: 0;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .toggle-button.deactivate {
            background: #fff0e8;
            color: #a84718;
        }

        .toggle-button.activate {
            background: #eaf5ed;
            color: #287344;
        }

        .no-stock-cell {
            color: #817168;
        }

        .inventory-alert {
            margin-bottom: 18px;
            padding: 12px 16px;
            border: 1px solid #b8dfc2;
            border-radius: 8px;
            background: #eaf5ed;
            color: #287344;
            font-size: 14px;
        }

        .inventory-error {
            margin-bottom: 18px;
            padding: 12px 16px;
            border: 1px solid #e8b7a7;
            border-radius: 8px;
            background: #fff0e8;
            color: #a84718;
            font-size: 14px;
        }

        .inventory-error ul {
            margin: 6px 0 0;
            padding-left: 20px;
        }

        .inventory-pagination {
            margin-top: 18px;
        }

        .empty-state {
            padding: 28px !important;
            color: #817168;
            text-align: center;
        }

        @media (max-width: 640px) {
            .inventory-page {
                padding: 16px;
            }

            .inventory-section {
                padding: 14px;
            }
        }

        /* Compact, spreadsheet-style inventory table */
        .inventory-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            font-size: 12px;
        }

        .inventory-table th,
        .inventory-table td {
            padding: 7px 8px;
            border: 1px solid #e8e1dc;
            white-space: normal;
            vertical-align: middle;
            line-height: 1.25;
        }

        .inventory-table th {
            font-size: 11px;
            padding: 8px 6px;
            background: #f5f0eb;
        }

        /* Keep action controls compact and in one row */
        .action-stack {
            display: flex;
            flex-direction: row;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
        }

        .unit-form {
            display: flex;
            flex-direction: row;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
            margin: 0;
        }

        .unit-form label {
            display: none;
        }

        .unit-select {
            width: 105px;
            max-width: 105px;
            padding: 4px 5px;
            font-size: 11px;
        }

        .unit-save-button,
        .toggle-button {
            padding: 5px 7px;
            font-size: 11px;
            line-height: 1.2;
        }

        .inventory-table .item-status,
        .inventory-table .stock-status {
            padding: 3px 6px;
            font-size: 10px;
            white-space: nowrap;
        }

        /* Give the action column enough room for the controls */
        .inventory-table th:nth-child(9),
        .inventory-table td:nth-child(9) {
            min-width: 190px;
        }

        /* Keep the table usable on smaller screens */
        .inventory-table-wrapper {
            overflow-x: auto;
        }

        @media (max-width: 900px) {
            .inventory-table {
                min-width: 950px;
            }
        }
    </style>
    <script>
        const inventorySearch = document.getElementById('inventorySearch');
        const inventoryItemCount = document.getElementById('inventoryItemCount');

        inventorySearch.addEventListener('input', function() {
            const searchTerm = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('.inventory-table tbody tr.inventory-clickable-row');
            const matchingItems = new Set();

            rows.forEach(function(row) {
                const itemName = (row.dataset.itemName || '').toLowerCase();
                const rowText = row.textContent.toLowerCase();

                const matches = itemName.includes(searchTerm) || rowText.includes(searchTerm);
                row.style.display = matches ? '' : 'none';

                if (matches && itemName) {
                    matchingItems.add(itemName);
                }
            });

            inventoryItemCount.textContent =
                matchingItems.size + (matchingItems.size === 1 ? ' item' : ' items');
        });
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('inventoryModal');
            const modalItemName = document.getElementById('modalItemName');
            const modalUnitForm = document.getElementById('modalUnitForm');
            const modalUnitSelect = document.getElementById('modalUnitSelect');
            const modalToggleForm = document.getElementById('modalToggleForm');
            const modalItemStatus = document.getElementById('modalItemStatus');
            const modalToggleButton = document.getElementById('modalToggleButton');

            function openModal(row) {
                const itemName = row.dataset.itemName;
                const unitId = row.dataset.unitId || '';
                const isActive = row.dataset.isActive === '1';

                modalItemName.textContent = itemName;
                modalUnitSelect.value = unitId;

                modalUnitForm.action = row.dataset.unitUrl;
                modalToggleForm.action = row.dataset.toggleUrl;

                modalItemStatus.textContent = isActive ? 'Active' : 'Inactive';
                modalToggleButton.textContent = isActive ? 'Deactivate Item' : 'Activate Item';
                modalToggleButton.classList.toggle('deactivate', isActive);
                modalToggleButton.classList.toggle('activate', !isActive);

                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            }

            function closeModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            }

            document.querySelectorAll('.inventory-clickable-row').forEach(function(row) {
                row.addEventListener('click', function(event) {
                    if (event.target.closest('input, button, form, select, a')) {
                        return;
                    }

                    openModal(row);
                });
            });

            modal.querySelectorAll('[data-close-modal]').forEach(function(element) {
                element.addEventListener('click', closeModal);
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }
            });

            modalToggleForm.addEventListener('submit', function(event) {
                const currentlyActive = modalToggleButton.classList.contains('deactivate');
                const action = currentlyActive ? 'deactivate' : 'activate';

                if (!confirm('Are you sure you want to ' + action + ' this inventory item?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
</x-app-layout>