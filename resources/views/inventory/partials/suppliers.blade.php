@php
    $selected = $selectedSupplier;
@endphp

<div class="inv-head">
    <div>
        <div class="inv-title" style="color:var(--text);font-size:24px;">Suppliers</div>
        <div class="inv-sub">Who supplies what, and what was delivered.</div>
    </div>
    <div class="inv-actions">
        @if ($sv !== 'list')
            <a class="btn btn-ghost" href="{{ route('inventory.index', ['tab' => 'suppliers']) }}">← Suppliers</a>
        @else
            <a class="btn btn-primary" href="{{ route('inventory.index', ['tab' => 'suppliers', 'sv' => 'delivery'] + ($selected ? ['supplier' => $selected->id] : [])) }}">+ Record Delivery</a>
            <a class="btn" href="{{ route('inventory.index', ['tab' => 'suppliers', 'sv' => 'new']) }}">+ New Supplier</a>
            <button type="button" class="btn" data-export="#supplierTable" data-filename="brewing-bar-suppliers">📋 Export</button>
        @endif
    </div>
</div>

@if ($sv === 'new')
    {{-- New supplier --}}
    <form method="POST" action="{{ route('inventory.suppliers.store') }}" class="inv-card" style="padding:20px;display:flex;flex-direction:column;gap:14px;max-width:820px;">
        @csrf
        <div style="font-weight:700;font-size:15px;color:var(--amber-light);">New Supplier</div>
        <div class="form-grid">
            <div><label class="lbl">Supplier name</label><input type="text" name="name" value="{{ old('name') }}" required></div>
            <div><label class="lbl">Contact person</label><input type="text" name="contact_person" value="{{ old('contact_person') }}"></div>
            <div><label class="lbl">Phone</label><input type="text" name="phone" value="{{ old('phone') }}"></div>
            <div><label class="lbl">Delivery frequency</label><input type="text" name="delivery_frequency" placeholder="e.g. Weekly" value="{{ old('delivery_frequency') }}"></div>
        </div>
        <div><label class="lbl">Address</label><input type="text" name="address" value="{{ old('address') }}"></div>
        <div><label class="lbl">Notes</label><textarea name="notes" rows="2">{{ old('notes') }}</textarea></div>
        <div>
            <label class="lbl">Items supplied</label>
            <div class="check-grid inv-card" style="background:var(--surface);">
                @foreach ($supplyItems as $item)
                    <label><input type="checkbox" name="items[]" value="{{ $item->id }}" @checked(in_array($item->id, (array) old('items', [])))> {{ $item->name }}</label>
                @endforeach
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:fit-content;padding:9px 18px;">Save Supplier</button>
    </form>

@elseif ($sv === 'delivery')
    {{-- Record a delivery --}}
    @php
        $oldLines = old('lines', [['inventory_item_id' => '', 'quantity' => '', 'unit_cost' => '']]);
    @endphp
    <form method="POST" action="{{ route('inventory.suppliers.deliveries.store') }}" class="inv-card" style="padding:20px;display:flex;flex-direction:column;gap:14px;max-width:980px;">
        @csrf
        <div style="font-weight:700;font-size:15px;color:var(--amber-light);">Record Delivery</div>
        <div class="form-grid">
            <div>
                <label class="lbl">Supplier</label>
                <select name="supplier_id" required>
                    <option value="">Select supplier…</option>
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->id }}" @selected(old('supplier_id', $selected?->id) == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="lbl">Delivery date</label><input type="date" name="delivery_date" value="{{ old('delivery_date', now()->toDateString()) }}" required></div>
            <div><label class="lbl">Received by</label><input type="text" name="received_by" value="{{ old('received_by', auth()->user()->name) }}"></div>
        </div>

        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                <label class="lbl" style="margin:0;">Items received</label>
                <button type="button" class="btn" id="addLine">+ Add item</button>
            </div>
            <div class="inv-table-wrap">
                <table class="inv-table" id="lineTable">
                    <thead><tr><th style="width:46%;">Item</th><th>Quantity</th><th>Unit cost (₱)</th><th>Line total</th><th></th></tr></thead>
                    <tbody id="lineBody">
                        @foreach ($oldLines as $i => $line)
                            <tr class="line-row">
                                <td>
                                    <select name="lines[{{ $i }}][inventory_item_id]" class="js-item">
                                        <option value="">Select item…</option>
                                        @foreach ($supplyItems as $item)
                                            <option value="{{ $item->id }}" data-cost="{{ $item->cost_per_unit }}" @selected(($line['inventory_item_id'] ?? '') == $item->id)>
                                                {{ $item->name }}{{ $item->unit ? ' ('.$item->unit->abbreviation.')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" min="0" step="0.001" name="lines[{{ $i }}][quantity]" class="js-qty mono" value="{{ $line['quantity'] ?? '' }}"></td>
                                <td><input type="number" min="0" step="0.01" name="lines[{{ $i }}][unit_cost]" class="js-cost mono" value="{{ $line['unit_cost'] ?? '' }}"></td>
                                <td class="mono js-line-total" style="color:var(--amber-light);">₱0.00</td>
                                <td><button type="button" class="btn btn-ghost js-remove" aria-label="Remove line">✕</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="text-align:right;margin-top:8px;font-size:13px;">Estimated total: <strong class="mono" id="grandTotal" style="color:var(--amber-light);">₱0.00</strong></div>
        </div>

        <div><label class="lbl">Notes</label><textarea name="notes" rows="2">{{ old('notes') }}</textarea></div>
        <button type="submit" class="btn btn-primary" style="width:fit-content;padding:9px 18px;">Save Delivery &amp; Add to Stock</button>
    </form>

    <script>
        (function () {
            var body = document.getElementById('lineBody');
            var peso = function (n) { return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

            function renumber() {
                Array.prototype.forEach.call(body.querySelectorAll('.line-row'), function (tr, i) {
                    tr.querySelectorAll('[name]').forEach(function (el) {
                        el.name = el.name.replace(/lines\[\d+\]/, 'lines[' + i + ']');
                    });
                });
            }

            function totals() {
                var grand = 0;
                body.querySelectorAll('.line-row').forEach(function (tr) {
                    var t = (parseFloat(tr.querySelector('.js-qty').value) || 0) * (parseFloat(tr.querySelector('.js-cost').value) || 0);
                    tr.querySelector('.js-line-total').textContent = peso(t);
                    grand += t;
                });
                document.getElementById('grandTotal').textContent = peso(grand);
            }

            body.addEventListener('change', function (e) {
                if (e.target.classList.contains('js-item')) {
                    var opt = e.target.options[e.target.selectedIndex];
                    var cost = e.target.closest('tr').querySelector('.js-cost');
                    if (opt && opt.dataset.cost && !cost.value) cost.value = opt.dataset.cost;
                }
                totals();
            });
            body.addEventListener('input', totals);
            body.addEventListener('click', function (e) {
                if (!e.target.classList.contains('js-remove')) return;
                if (body.querySelectorAll('.line-row').length > 1) e.target.closest('tr').remove();
                renumber(); totals();
            });

            document.getElementById('addLine').addEventListener('click', function () {
                var clone = body.querySelector('.line-row').cloneNode(true);
                clone.querySelectorAll('input').forEach(function (i) { i.value = ''; });
                clone.querySelector('select').selectedIndex = 0;
                body.appendChild(clone);
                renumber(); totals();
            });

            totals();
        })();
    </script>

@else
    {{-- List + detail --}}
    <div class="sup-grid">
        <div style="display:flex;flex-direction:column;gap:8px;">
            @forelse ($suppliers as $s)
                <a class="sup-item {{ $selected?->id === $s->id ? 'is-active' : '' }}"
                    href="{{ route('inventory.index', ['tab' => 'suppliers', 'supplier' => $s->id]) }}">
                    <div class="sup-name">{{ $s->name }}</div>
                    <div class="sup-meta">
                        {{ $s->delivery_frequency ?: 'No schedule' }} ·
                        Last delivery: {{ $s->last_delivery_date?->format('M j, Y') ?? '—' }}
                    </div>
                </a>
            @empty
                <div class="inv-card empty">No suppliers yet.</div>
            @endforelse
        </div>

        <div>
            @if ($selected)
                <div class="inv-card" style="padding:20px;margin-bottom:16px;">
                    <div style="font-size:17px;font-weight:700;margin-bottom:12px;">{{ $selected->name }}</div>
                    <dl class="kv">
                        <dt>Contact</dt><dd>{{ $selected->contact_person ?: '—' }}</dd>
                        <dt>Phone</dt><dd class="mono">{{ $selected->phone ?: '—' }}</dd>
                        <dt>Address</dt><dd>{{ $selected->address ?: '—' }}</dd>
                        <dt>Delivery frequency</dt><dd>{{ $selected->delivery_frequency ?: '—' }}</dd>
                        <dt>Last delivery</dt><dd>{{ $selected->last_delivery_date?->format('F j, Y') ?? '—' }}</dd>
                        <dt>Notes</dt><dd style="color:var(--muted);">{{ $selected->notes ?: '—' }}</dd>
                        <dt>Supplies</dt>
                        <dd>
                            <div class="chips">
                                @forelse ($selected->inventoryItems->sortBy('name') as $item)
                                    <span class="chip">{{ $item->name }}</span>
                                @empty
                                    <span style="color:var(--muted);">—</span>
                                @endforelse
                            </div>
                        </dd>
                    </dl>
                    <div style="margin-top:14px;">
                        <a class="btn btn-primary" href="{{ route('inventory.index', ['tab' => 'suppliers', 'sv' => 'delivery', 'supplier' => $selected->id]) }}">+ Record Delivery</a>
                    </div>
                </div>

                <div class="inv-card" style="overflow:hidden;">
                    <div style="padding:12px 16px;border-bottom:1px solid var(--border);font-weight:700;font-size:14px;color:var(--amber-light);">
                        Recent Deliveries ({{ $selectedDeliveries->count() }})
                    </div>
                    @forelse ($selectedDeliveries as $d)
                        <div style="padding:12px 16px;border-bottom:1px solid var(--border);">
                            <div style="display:flex;justify-content:space-between;gap:12px;">
                                <strong>{{ $d->delivery_date->format('F j, Y') }}</strong>
                                <span class="mono" style="color:var(--amber-light);">₱{{ number_format((float) $d->total_cost, 2) }}</span>
                            </div>
                            <div class="inv-sub">Received by {{ $d->received_by ?: '—' }}{{ $d->notes ? ' · '.$d->notes : '' }}</div>
                            <div class="chips" style="margin-top:8px;">
                                @foreach ($d->items as $line)
                                    <span class="chip">{{ $line->inventoryItem?->name }} · {{ $fmt($line->quantity) }} {{ $line->inventoryItem?->unit?->abbreviation }}</span>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="empty">No deliveries recorded yet</div>
                    @endforelse
                </div>
            @else
                <div class="inv-card empty">Select a supplier to see details and delivery history.</div>
            @endif
        </div>
    </div>

    {{-- Flat table used only for the CSV export --}}
    <table id="supplierTable" style="display:none;">
        <thead><tr><th>Supplier</th><th>Contact</th><th>Phone</th><th>Address</th><th>Frequency</th><th>Last Delivery</th><th>Items</th><th>Notes</th></tr></thead>
        <tbody>
            @foreach ($suppliers as $s)
                <tr>
                    <td>{{ $s->name }}</td><td>{{ $s->contact_person }}</td><td>{{ $s->phone }}</td><td>{{ $s->address }}</td>
                    <td>{{ $s->delivery_frequency }}</td><td>{{ $s->last_delivery_date?->format('Y-m-d') }}</td>
                    <td>{{ $s->inventoryItems->pluck('name')->sort()->implode(' | ') }}</td><td>{{ $s->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
