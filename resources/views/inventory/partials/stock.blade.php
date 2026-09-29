@php
    $lowCount = $lowStockCount;
@endphp

<div class="inv-head">
    <div>
        <div class="inv-title" style="color:var(--text);font-size:24px;">Inventory Management</div>
        <div class="inv-sub">View and monitor stock across your inventory locations.</div>
    </div>
    <div class="inv-actions">
        <button type="button" class="btn btn-primary" id="openStockIn">+ Stock In</button>
        <button type="button" class="btn" data-export="#stockTable" data-filename="brewing-bar-stock">📦 Export Stock Report</button>
    </div>
</div>

<div class="inv-cards">
    <div class="inv-card inv-stat" style="min-width:190px;padding:16px 20px;">
        <div class="inv-stat-label">Active Items</div>
        <div style="font-size:26px;font-weight:700;">{{ $activeItemCount }}</div>
    </div>
    <div class="inv-card inv-stat" style="min-width:190px;padding:16px 20px;">
        <div class="inv-stat-label">Low Stock Records</div>
        <div style="font-size:26px;font-weight:700;">{{ $lowCount }}</div>
    </div>
</div>

<section class="inv-card" style="padding:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
        <h3 style="font-size:18px;font-weight:700;">Inventory Items</h3>
        <div style="display:flex;align-items:center;gap:12px;">
            <input type="text" id="stockSearch" placeholder="Search items…" style="width:220px;">
            <span id="stockCount" style="font-size:12px;color:var(--muted);white-space:nowrap;">{{ $stocks->count() }} item(s)</span>
        </div>
    </div>

    <div class="inv-table-wrap">
        <table class="inv-table is-stock" id="stockTable" style="table-layout:fixed;min-width:1000px;">
            <thead>
                <tr>
                    <th style="width:19%;">Item Name</th>
                    <th style="width:10%;">Type</th>
                    <th style="width:6%;">Unit</th>
                    <th style="width:10%;">Location</th>
                    <th style="width:12%;">Current Stock</th>
                    <th style="width:9%;">Reorder Level</th>
                    <th style="width:9%;">Stock Status</th>
                    <th style="width:11%;">Last Restocked</th>
                    <th style="width:8%;">Item Status</th>
                </tr>
            </thead>
            <tbody id="stockBody">
                @forelse ($stocks as $stock)
                    @php
                        $item = $stock->inventoryItem;
                        $low = (float) $stock->current_quantity <= (float) $stock->reorder_level;
                        $last = $lastRestocked[$stock->id] ?? null;
                    @endphp
                    <tr class="inv-row-click {{ $item->is_active ? '' : 'inv-row-inactive' }}"
                        data-search="{{ strtolower($item->name . ' ' . $item->inventory_type . ' ' . $stock->location?->name) }}"
                        data-item-name="{{ $item->name }}"
                        data-unit-id="{{ $item->unit_id }}"
                        data-cost="{{ $item->cost_per_unit }}"
                        data-reorder="{{ $fmt($stock->reorder_level) }}"
                        data-is-active="{{ $item->is_active ? '1' : '0' }}"
                        data-details-url="{{ route('inventory.update-details', $item) }}"
                        data-toggle-url="{{ route('inventory.toggle-active', $item) }}">
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->inventory_type }}</td>
                        <td>{{ $item->unit?->abbreviation ?? '—' }}</td>
                        <td>{{ $stock->location?->name ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('inventory.update-stock-quantity', $stock) }}" style="margin:0;">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="current_quantity" class="stock-input mono"
                                    value="{{ number_format((float) $stock->current_quantity, 3, '.', '') }}"
                                    min="0" step="0.001"
                                    aria-label="Current stock for {{ $item->name }} at {{ $stock->location?->name }}"
                                    onchange="this.form.requestSubmit()">
                            </form>
                        </td>
                        <td class="mono">{{ number_format((float) $stock->reorder_level, 3) }}</td>
                        <td><span class="badge {{ $low ? 'badge-low' : 'badge-ok' }}">{{ $low ? 'Low Stock' : 'In Stock' }}</span></td>
                        <td style="color:var(--muted);">{{ $last ? \Illuminate\Support\Carbon::parse($last)->format('M j, Y') : '—' }}</td>
                        <td><span class="badge {{ $item->is_active ? 'badge-ok' : 'badge-off' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty">No inventory items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="inv-pager">
        <span id="pagerInfo"></span>
        <div class="inv-pager-btns" id="pagerBtns"></div>
    </div>
</section>

{{-- Item modal: unit, cost, reorder level, status --}}
<div id="itemModal" class="inv-modal" aria-hidden="true">
    <div class="inv-modal-bg" data-close></div>
    <div class="inv-modal-box" role="dialog" aria-modal="true" aria-labelledby="itemModalTitle">
        <div class="inv-modal-head">
            <div>
                <h3 id="itemModalTitle">Inventory Item</h3>
                <p>Update this item’s unit, cost, reorder level or status.</p>
            </div>
            <button type="button" class="inv-modal-x" aria-label="Close" data-close>&times;</button>
        </div>
        <div class="inv-modal-body">
            <div>
                <div class="inv-sub">Selected item</div>
                <strong id="modalItemName" style="font-size:16px;"></strong>
            </div>

            <form id="modalDetailsForm" method="POST" style="display:flex;flex-direction:column;gap:12px;">
                @csrf
                @method('PATCH')
                <div>
                    <label class="lbl" for="modalUnit">Unit of measurement</label>
                    <select id="modalUnit" name="unit_id">
                        <option value="">Not set</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label class="lbl" for="modalCost">Cost per unit (₱)</label>
                        <input type="number" id="modalCost" name="cost_per_unit" min="0" step="0.01">
                    </div>
                    <div>
                        <label class="lbl" for="modalReorder">Reorder level</label>
                        <input type="number" id="modalReorder" name="reorder_level" min="0" step="0.001">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:fit-content;">Save Changes</button>
            </form>

            <div class="modal-sep">
                <div>
                    <div class="inv-sub">Item status</div>
                    <strong id="modalStatus"></strong>
                </div>
                <form id="modalToggleForm" method="POST">
                    @csrf
                    <button type="submit" id="modalToggleBtn" class="btn"></button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Stock In modal --}}
<div id="stockInModal" class="inv-modal {{ $errors->has('inventory_item_id') || $errors->has('location_id') || $errors->has('quantity') ? 'is-open' : '' }}" aria-hidden="true">
    <div class="inv-modal-bg" data-close></div>
    <div class="inv-modal-box" role="dialog" aria-modal="true">
        <div class="inv-modal-head">
            <div>
                <h3>Stock In</h3>
                <p>Record incoming stock for an active item.</p>
            </div>
            <button type="button" class="inv-modal-x" aria-label="Close" data-close>&times;</button>
        </div>
        <form method="POST" action="{{ route('inventory.stock-in.store') }}" class="inv-modal-body">
            @csrf
            <div>
                <label class="lbl" for="siItem">Item</label>
                <select id="siItem" name="inventory_item_id" required>
                    <option value="">Select item…</option>
                    @foreach ($stocks->filter(fn ($s) => $s->inventoryItem->is_active) as $s)
                        <option value="{{ $s->inventory_item_id }}" data-location="{{ $s->location_id }}" data-cost="{{ $s->inventoryItem->cost_per_unit }}"
                            @selected(old('inventory_item_id') == $s->inventory_item_id)>
                            {{ $s->inventoryItem->name }} — {{ $s->location?->name }} ({{ $s->inventoryItem->unit?->abbreviation ?? 'no unit' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="lbl" for="siLocation">Location</label>
                <select id="siLocation" name="location_id" required>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(old('location_id') == $location->id)>{{ $location->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-grid" style="grid-template-columns:1fr 1fr;">
                <div>
                    <label class="lbl" for="siQty">Quantity</label>
                    <input type="number" id="siQty" name="quantity" min="0" step="0.001" value="{{ old('quantity') }}" required>
                </div>
                <div>
                    <label class="lbl" for="siCost">Unit cost (optional)</label>
                    <input type="number" id="siCost" name="unit_cost" min="0" step="0.01" value="{{ old('unit_cost') }}">
                </div>
            </div>
            <div>
                <label class="lbl" for="siReason">Reason (optional)</label>
                <input type="text" id="siReason" name="reason" maxlength="255" value="{{ old('reason') }}">
            </div>
            <button type="submit" class="btn btn-primary" style="justify-content:center;padding:9px;">Add Stock</button>
        </form>
    </div>
</div>

<script>
    (function () {
        var PAGE_SIZE = 15;
        var rows = Array.prototype.slice.call(document.querySelectorAll('#stockBody tr[data-search]'));
        var search = document.getElementById('stockSearch');
        var page = 1;

        function render() {
            var q = search.value.trim().toLowerCase();
            var matched = rows.filter(function (r) { return r.dataset.search.indexOf(q) !== -1; });
            var pages = Math.max(1, Math.ceil(matched.length / PAGE_SIZE));
            if (page > pages) page = pages;

            // `hidden` = filtered out by search (skipped by CSV export);
            // display:none = only on another page (still exported).
            rows.forEach(function (r) { r.hidden = matched.indexOf(r) === -1; r.style.display = 'none'; });
            matched.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE).forEach(function (r) { r.style.display = ''; });

            document.getElementById('stockCount').textContent = matched.length + ' item(s)';
            var from = matched.length ? (page - 1) * PAGE_SIZE + 1 : 0;
            document.getElementById('pagerInfo').textContent =
                'Showing ' + from + '–' + Math.min(page * PAGE_SIZE, matched.length) + ' of ' + matched.length;

            var box = document.getElementById('pagerBtns');
            box.innerHTML = '';
            if (pages <= 1) return;
            var add = function (label, target, active, disabled) {
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'btn' + (active ? ' btn-primary' : '');
                b.textContent = label;
                b.disabled = !!disabled;
                b.onclick = function () { page = target; render(); };
                box.appendChild(b);
            };
            add('‹ Previous', page - 1, false, page === 1);
            for (var i = 1; i <= pages; i++) add(String(i), i, i === page, false);
            add('Next ›', page + 1, false, page === pages);
        }

        search.addEventListener('input', function () { page = 1; render(); });
        render();

        // ── Item modal
        var itemModal = document.getElementById('itemModal');
        var detailsForm = document.getElementById('modalDetailsForm');
        var toggleForm = document.getElementById('modalToggleForm');
        var toggleBtn = document.getElementById('modalToggleBtn');

        function open(modal) { modal.classList.add('is-open'); modal.setAttribute('aria-hidden', 'false'); }
        function close(modal) { modal.classList.remove('is-open'); modal.setAttribute('aria-hidden', 'true'); }

        rows.forEach(function (row) {
            row.addEventListener('click', function (e) {
                if (e.target.closest('input, button, form, select, a')) return;
                var active = row.dataset.isActive === '1';
                document.getElementById('modalItemName').textContent = row.dataset.itemName;
                document.getElementById('modalUnit').value = row.dataset.unitId || '';
                document.getElementById('modalCost').value = row.dataset.cost || '';
                document.getElementById('modalReorder').value = row.dataset.reorder || '';
                detailsForm.action = row.dataset.detailsUrl;
                toggleForm.action = row.dataset.toggleUrl;
                document.getElementById('modalStatus').textContent = active ? 'Active' : 'Inactive';
                toggleBtn.textContent = active ? 'Deactivate Item' : 'Activate Item';
                toggleBtn.className = 'btn ' + (active ? 'btn-danger' : 'btn-ok');
                open(itemModal);
            });
        });

        toggleForm.addEventListener('submit', function (e) {
            var deactivating = toggleBtn.classList.contains('btn-danger');
            if (!confirm('Are you sure you want to ' + (deactivating ? 'deactivate' : 'activate') + ' this inventory item?')) {
                e.preventDefault();
            }
        });

        // ── Stock-in modal
        var stockInModal = document.getElementById('stockInModal');
        document.getElementById('openStockIn').addEventListener('click', function () { open(stockInModal); });

        document.getElementById('siItem').addEventListener('change', function () {
            var opt = this.options[this.selectedIndex];
            if (opt && opt.dataset.location) document.getElementById('siLocation').value = opt.dataset.location;
            if (opt && opt.dataset.cost && !document.getElementById('siCost').value) {
                document.getElementById('siCost').value = opt.dataset.cost;
            }
        });

        document.querySelectorAll('.inv-modal').forEach(function (modal) {
            modal.querySelectorAll('[data-close]').forEach(function (el) {
                el.addEventListener('click', function () { close(modal); });
            });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') document.querySelectorAll('.inv-modal.is-open').forEach(close);
        });
    })();
</script>
