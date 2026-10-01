<x-app-layout>
    <div class="begin-day-page">
        <div class="begin-day-header">
            <div>
                <span class="begin-day-eyebrow">INVENTORY MANAGEMENT</span>
                <h1>Begin Day</h1>
                <p>Record the starting quantity of your inventory for the selected day.</p>
            </div>

            <a href="{{ route('inventory.index') }}" class="back-inventory-button">
                <span aria-hidden="true">←</span>
                Back to Inventory
            </a>
        </div>

        @if ($errors->any())
        <div class="begin-day-error">
            <strong>Please check the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <section class="begin-day-card">
            <div class="section-heading">
                <div>
                    <h2>Select an area</h2>
                    <p>Choose where the items are stored to make stock entry easier.</p>
                </div>
            </div>

            <div class="begin-day-area-tabs">
                <a
                    href="{{ route('inventory.begin-day') }}"
                    class="begin-day-tab {{ empty($locationId) ? 'active' : '' }}">
                    All Areas
                </a>

                @foreach ($locations as $location)
                <a
                    href="{{ route('inventory.begin-day', ['location_id' => $location->id]) }}"
                    class="begin-day-tab {{ (string) $locationId === (string) $location->id ? 'active' : '' }}">
                    {{ $location->name }}
                </a>
                @endforeach
            </div>

            <form method="POST" action="{{ route('inventory.begin-day.store') }}">
                @csrf
                <input type="hidden" name="location_id" value="{{ $locationId }}">

                <div class="begin-day-details">
                    <div class="begin-day-field date-field">
                        <label for="stock_date">Stock date</label>
                        <input
                            type="date"
                            id="stock_date"
                            name="stock_date"
                            value="{{ old('stock_date', $stockDate) }}"
                            required>
                    </div>

                    <div class="begin-day-field remarks-field">
                        <label for="remarks">Remarks <span>(optional)</span></label>
                        <input
                            type="text"
                            id="remarks"
                            name="remarks"
                            value="{{ old('remarks') }}"
                            maxlength="255"
                            placeholder="e.g. Opening count before business hours">
                    </div>
                </div>
                {{-- PREPPED FOOD --}}
                <div class="stock-entry-heading">
                    <div>
                        <h2>Prepped Food</h2>
                        <p>
                            Track prepared food stock. Sold quantities are recorded from completed POS orders.
                        </p>
                    </div>

                    <span class="item-count">
                        {{ $preppedItems->count() }} items
                    </span>
                </div>

                <div class="begin-day-item-search">
                    <label for="preppedSearch">Search prepped food</label>
                    <input
                        type="search"
                        id="preppedSearch"
                        placeholder="Search by item name..."
                        autocomplete="off">
                </div>

                <div class="begin-day-table-wrapper">
                    <table class="begin-day-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Beginning</th>
                                <th>Sold</th>
                                <th>Input New</th>
                                <th class="quantity-column">Ending Balance</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($preppedItems as $item)
                            <tr
                                class="prepped-item-row"
                                data-item-name="{{ strtolower($item->name) }}">

                                <td>
                                    <span class="begin-day-item-name">
                                        {{ $item->name }}
                                    </span>
                                </td>

                                <td>
                                    <span class="beginning-value">
                                        {{ number_format($beginningQuantities[$item->id] ?? 0, 3) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="sold-value">
                                        {{ number_format($soldQuantities[$item->id] ?? 0, 3) }}
                                    </span>
                                </td>

                                <td>
                                    <input
                                        class="quantity-input input-new"
                                        type="number"
                                        name="input_new[{{ $item->id }}]"
                                        value="{{ old('input_new.' . $item->id, '') }}"
                                        min="0"
                                        step="0.001"
                                        data-beginning="{{ $beginningQuantities[$item->id] ?? 0 }}"
                                        data-sold="{{ $soldQuantities[$item->id] ?? 0 }}"
                                        aria-label="New stock for {{ $item->name }}">
                                </td>

                                <td class="quantity-column">
                                    <span
                                        class="ending-value"
                                        data-ending-for="{{ $item->id }}">
                                        {{ number_format(
                                            ($beginningQuantities[$item->id] ?? 0)
                                            - ($soldQuantities[$item->id] ?? 0),
                                            3
                                        ) }}
                                    </span>
                                </td>
                            </tr>

                            @empty
                            <tr>
                                <td colspan="5">
                                    <div class="begin-day-empty">
                                        <strong>No prepped food items found</strong>
                                        <p>
                                            Add or activate prepped inventory items in Inventory Management.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


                {{-- PHYSICAL INVENTORY --}}
                <div class="stock-entry-heading physical-inventory-heading">
                    <div>
                        <h2>Physical Inventory</h2>
                        <p>
                            Record the actual quantity counted for physical inventory items.
                        </p>
                    </div>

                    <span class="item-count">
                        {{ $physicalItems->count() }} items
                    </span>
                </div>

                <div class="begin-day-item-search">
                    <label for="physicalSearch">Search physical inventory</label>
                    <input
                        type="search"
                        id="physicalSearch"
                        placeholder="Search by item name..."
                        autocomplete="off">
                </div>

                <div class="begin-day-table-wrapper">
                    <table class="begin-day-table">
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
                            @forelse ($physicalItems as $item)
                            <tr
                                class="physical-item-row"
                                data-item-name="{{ strtolower($item->name) }}">

                                <td>
                                    <span class="begin-day-item-name">
                                        {{ $item->name }}
                                    </span>
                                </td>

                                <td>
                                    <span class="beginning-value">
                                        {{ number_format($beginningQuantities[$item->id] ?? 0, 3) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->unit?->abbreviation ?? $item->unit?->name ?? '—' }}
                                </td>

                                <td>
                                    <input
                                        class="quantity-input"
                                        type="number"
                                        name="actual_quantity[{{ $item->id }}]"
                                        value="{{ old('actual_quantity.' . $item->id, '') }}"
                                        min="0"
                                        step="0.001"
                                        aria-label="Actual quantity for {{ $item->name }}">
                                </td>

                                <td>
                                    <input
                                        type="text"
                                        name="physical_notes[{{ $item->id }}]"
                                        value="{{ old('physical_notes.' . $item->id, '') }}"
                                        maxlength="255"
                                        placeholder="Optional note"
                                        class="physical-note-input">
                                </td>
                            </tr>

                            @empty
                            <tr>
                                <td colspan="5">
                                    <div class="begin-day-empty">
                                        <strong>No physical inventory items found</strong>
                                        <p>
                                            Add or activate physical inventory items in Inventory Management.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


                <div class="begin-day-footer">
                    <p>
                        Review the quantities before saving the daily inventory.
                    </p>

                    <button type="submit" class="save-beginning-button">
                        Save Daily Inventory
                    </button>
                </div>

            </form>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function setupSearch(inputId, rowSelector) {
                const searchInput = document.getElementById(inputId);
                const itemRows = document.querySelectorAll(rowSelector);

                if (!searchInput) {
                    return;
                }

                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.trim().toLowerCase();

                    itemRows.forEach(function(row) {
                        const itemName = row.dataset.itemName || '';

                        row.style.display =
                            itemName.includes(searchTerm) ? '' : 'none';
                    });
                });
            }

            setupSearch('preppedSearch', '.prepped-item-row');
            setupSearch('physicalSearch', '.physical-item-row');

        });
        document.querySelectorAll('.input-new').forEach(function(input) {
            input.addEventListener('input', function() {
                const beginning = parseFloat(this.dataset.beginning) || 0;
                const sold = parseFloat(this.dataset.sold) || 0;
                const inputNew = parseFloat(this.value) || 0;

                const ending = beginning - sold + inputNew;

                const itemId = this.name.match(/\d+/)[0];

                const endingElement = document.querySelector(
                    `[data-ending-for="${itemId}"]`
                );

                if (endingElement) {
                    endingElement.textContent = ending.toFixed(3);
                }
            });
        });
    </script>
    <style>
        .begin-day-item-search {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0 24px 16px;
        }

        .begin-day-item-search label {
            color: #514437;
            font-size: 12px;
            font-weight: 800;
        }

        .begin-day-item-search input {
            width: 100%;
            max-width: 420px;
            min-height: 42px;
            padding: 9px 13px;
            border: 1px solid #ded5cb;
            border-radius: 9px;
            outline: none;
            background: #fff;
            color: #33291f;
            font-size: 13px;
        }

        .begin-day-item-search input:focus {
            border-color: #96704e;
            box-shadow: 0 0 0 3px rgba(150, 112, 78, 0.13);
        }

        @media (max-width: 700px) {
            .begin-day-item-search {
                padding-left: 16px;
                padding-right: 16px;
            }
        }

        .begin-day-page {
            max-width: 1440px;
            margin: 0 auto;
            padding: 32px 24px 48px;
            color: #302820;
        }

        .begin-day-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 26px;
        }

        .begin-day-eyebrow {
            display: inline-block;
            margin-bottom: 8px;
            color: #98704f;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
        }

        .begin-day-header h1 {
            margin: 0;
            color: #302820;
            font-size: 30px;
            font-weight: 800;
            line-height: 1.2;
        }

        .begin-day-header p {
            margin: 8px 0 0;
            color: #786f66;
            font-size: 14px;
        }

        .back-inventory-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border: 1px solid #e5ddd4;
            border-radius: 10px;
            background: #fff;
            color: #5d4634;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            transition: background 0.2s, border-color 0.2s;
        }

        .back-inventory-button:hover {
            background: #f8f4ef;
            border-color: #cbb9a7;
        }

        .begin-day-card {
            overflow: hidden;
            border: 1px solid #e9e2da;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 6px 22px rgba(55, 38, 22, 0.05);
        }

        .section-heading {
            padding: 22px 24px 0;
        }

        .section-heading h2,
        .stock-entry-heading h2 {
            margin: 0;
            color: #33291f;
            font-size: 17px;
            font-weight: 800;
        }

        .section-heading p,
        .stock-entry-heading p {
            margin: 5px 0 0;
            color: #83796f;
            font-size: 13px;
        }

        .begin-day-area-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 18px 24px 20px;
            border-bottom: 1px solid #eee8e1;
        }

        .begin-day-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 8px 15px;
            border: 1px solid #e8dfd6;
            border-radius: 999px;
            background: #fff;
            color: #685747;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.18s ease;
        }

        .begin-day-tab:hover {
            border-color: #b99b7e;
            background: #faf6f1;
        }

        .begin-day-tab.active {
            border-color: #68452c;
            background: #68452c;
            color: #fff;
        }

        .begin-day-details {
            display: grid;
            grid-template-columns: minmax(180px, 0.7fr) minmax(260px, 1.3fr);
            gap: 18px;
            padding: 22px 24px 24px;
            background: #fcfaf8;
            border-bottom: 1px solid #eee8e1;
        }

        .begin-day-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .begin-day-field label {
            color: #514437;
            font-size: 12px;
            font-weight: 800;
        }

        .begin-day-field label span {
            color: #968b80;
            font-weight: 500;
        }

        .begin-day-field input {
            width: 100%;
            min-height: 42px;
            padding: 9px 12px;
            border: 1px solid #ded5cb;
            border-radius: 9px;
            outline: none;
            background: #fff;
            color: #33291f;
            font-size: 13px;
        }

        .begin-day-field input:focus,
        .quantity-input:focus {
            border-color: #96704e;
            box-shadow: 0 0 0 3px rgba(150, 112, 78, 0.13);
        }

        .stock-entry-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 24px 16px;
        }

        .item-count {
            padding: 6px 10px;
            border-radius: 999px;
            background: #f4eee8;
            color: #72543a;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .begin-day-table-wrapper {
            overflow-x: auto;
            padding: 0 24px;
        }

        .begin-day-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
        }

        .begin-day-table thead th {
            padding: 12px 14px;
            border-top: 1px solid #eee8e1;
            border-bottom: 1px solid #e9e2da;
            background: #f8f5f1;
            color: #786b5d;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-align: left;
            white-space: nowrap;
        }

        .begin-day-table thead th:first-child {
            border-top-left-radius: 8px;
        }

        .begin-day-table thead th:last-child {
            border-top-right-radius: 8px;
        }

        .begin-day-table tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #f0ebe6;
            color: #655b51;
            vertical-align: middle;
        }

        .begin-day-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .begin-day-table tbody tr:hover {
            background: #fdfbf9;
        }

        .begin-day-item-name {
            color: #382f27;
            font-weight: 700;
        }

        .begin-day-category {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            background: #f5f1ec;
            color: #78634e;
            font-size: 11px;
            font-weight: 700;
        }

        .quantity-column {
            width: 190px;
            text-align: right !important;
        }

        .quantity-input {
            width: 120px;
            min-height: 38px;
            padding: 7px 10px;
            border: 1px solid #ded5cb;
            border-radius: 8px;
            outline: none;
            background: #fff;
            color: #33291f;
            font-size: 13px;
            text-align: right;
        }

        .begin-day-empty {
            padding: 34px 16px;
            text-align: center;
        }

        .begin-day-empty strong {
            color: #44382d;
            font-size: 14px;
        }

        .begin-day-empty p {
            margin: 6px 0 0;
            color: #8a8076;
            font-size: 13px;
        }

        .begin-day-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 4px;
            padding: 18px 24px;
            border-top: 1px solid #eee8e1;
            background: #fcfaf8;
        }

        .begin-day-footer p {
            margin: 0;
            color: #83796f;
            font-size: 12px;
        }

        .save-beginning-button {
            min-height: 42px;
            padding: 10px 18px;
            border: 0;
            border-radius: 9px;
            background: #68452c;
            color: #fff;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition: background 0.18s ease, transform 0.18s ease;
        }

        .save-beginning-button:hover {
            background: #52351f;
            transform: translateY(-1px);
        }

        .begin-day-error {
            margin-bottom: 18px;
            padding: 14px 18px;
            border: 1px solid #f2c6c2;
            border-radius: 10px;
            background: #fff4f2;
            color: #8d3028;
            font-size: 13px;
        }

        .begin-day-error ul {
            margin: 7px 0 0;
            padding-left: 20px;
        }

        @media (max-width: 700px) {
            .begin-day-page {
                padding: 22px 14px 36px;
            }

            .begin-day-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .begin-day-header h1 {
                font-size: 25px;
            }

            .section-heading,
            .begin-day-area-tabs,
            .stock-entry-heading {
                padding-left: 16px;
                padding-right: 16px;
            }

            .begin-day-details {
                grid-template-columns: 1fr;
                padding: 18px 16px;
            }

            .begin-day-table-wrapper {
                padding: 0 12px;
            }

            .begin-day-table thead th,
            .begin-day-table tbody td {
                padding: 10px 9px;
            }

            .quantity-column {
                width: 145px;
            }

            .quantity-input {
                width: 100px;
            }

            .begin-day-footer {
                align-items: stretch;
                flex-direction: column;
                padding: 16px;
            }

            .save-beginning-button {
                width: 100%;
            }
        }
    </style>
</x-app-layout>