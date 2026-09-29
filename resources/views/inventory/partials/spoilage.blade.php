<div class="inv-head">
    <div>
        <div class="inv-title" style="color:var(--text);font-size:24px;">Spoilage Log</div>
        <div class="inv-sub">Record spoiled or damaged stock. It is taken out of inventory automatically.</div>
    </div>
</div>

<div class="spoil-grid">
    <div class="inv-card" style="padding:16px;">
        <div style="font-weight:700;font-size:14px;color:#fca5a5;margin-bottom:12px;">Log Spoilage / Damage</div>
        <form method="POST" action="{{ route('inventory.spoilage.store') }}" style="display:flex;flex-direction:column;gap:12px;">
            @csrf
            <select name="inventory_item_id" required>
                <option value="">Select ingredient…</option>
                @foreach ($spoilItems as $item)
                    @php $qty = (float) ($item->inventoryStocks->first()?->current_quantity ?? 0); @endphp
                    <option value="{{ $item->id }}" @selected(old('inventory_item_id') == $item->id)>
                        {{ $item->name }} ({{ $fmt($qty) }} {{ $item->unit?->abbreviation }})
                    </option>
                @endforeach
            </select>
            <input type="number" name="quantity" min="0" step="0.001" placeholder="Quantity spoiled / damaged"
                value="{{ old('quantity') }}" class="mono" required>
            <input type="text" name="reason" maxlength="255" placeholder="Reason (unchilled, spilled, expired…)"
                value="{{ old('reason') }}">
            <button type="submit" class="btn btn-danger" style="justify-content:center;padding:9px;font-weight:700;">Log Spoilage</button>
        </form>
    </div>

    <div class="inv-card" style="overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--border);">
            <span style="font-weight:700;font-size:14px;color:var(--amber-light);">Spoilage Records ({{ $spoilRecords->count() }})</span>
            @if ($spoilRecords->isNotEmpty())
                <button type="button" class="btn" data-export="#spoilTable" data-filename="brewing-bar-spoilage">📋 Export</button>
            @endif
        </div>

        @if ($spoilRecords->isEmpty())
            <div class="empty">No spoilage logged</div>
        @else
            <div class="inv-table-wrap">
                <table class="inv-table" id="spoilTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Ingredient</th>
                            <th>Amount</th>
                            <th>Reason</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($spoilRecords as $r)
                            <tr>
                                <td class="mono" style="color:var(--muted);">{{ $r->spoiled_at->format('M j, Y g:i A') }}</td>
                                <td>{{ $r->inventoryItem?->name }}</td>
                                <td class="mono" style="color:#fca5a5;">−{{ $fmt($r->quantity) }} {{ $r->inventoryItem?->unit?->abbreviation }}</td>
                                <td style="color:var(--muted);">{{ $r->reason }}</td>
                                <td style="color:var(--muted);">{{ $r->recordedBy?->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
