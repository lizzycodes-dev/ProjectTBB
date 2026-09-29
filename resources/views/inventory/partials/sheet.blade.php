@php
    $isOpen = $sheet->status === 'open';
    $isSaved = $sheet->status === 'beginning_saved';
    $ro = $sheetReadOnly;

    $areaMeta = [
        'Bar Area' => ['label' => '🍹 Bar Area', 'color' => '#2563eb'],
        'Kitchen Area' => ['label' => '🍳 Kitchen Area', 'color' => '#16a34a'],
    ];
    $groupMeta = [
        'Powder' => ['emoji' => '🧂', 'color' => '#d97706'],
        'Sauce' => ['emoji' => '🫙', 'color' => '#2563eb'],
        'Puree' => ['emoji' => '🍓', 'color' => '#16a34a'],
        'Syrup' => ['emoji' => '🧃', 'color' => '#ec4899'],
        'Other' => ['emoji' => '🧪', 'color' => '#9333ea'],
        'Prepped Food' => ['emoji' => '🍱', 'color' => '#0891b2'],
    ];

    $defaultAction = $isOpen
        ? route('inventory.sheet.beginning', $sheet)
        : route('inventory.sheet.ending', $sheet);
@endphp

@if ($ro)
    {{-- Historical (closed) sheet: read-only --}}
    <div class="inv-head">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="{{ route('inventory.index', ['tab' => 'sheet']) }}" class="btn btn-ghost">← Back</a>
            <div>
                <div class="inv-title">Daily Sheet — {{ $sheet->sheet_date->format('F j, Y') }}</div>
                <div class="inv-sub">Historical record · read-only</div>
            </div>
        </div>
        <div class="inv-actions">
            <button type="button" class="btn" data-export="#sheetTable" data-filename="brewing-bar-daily-sheet-{{ $sheet->sheet_date->format('Y-m-d') }}">📋 Download</button>
        </div>
    </div>
@else
    <form id="sheetForm" method="POST" action="{{ $defaultAction }}">
        @csrf

        <div class="inv-head">
            <div>
                <div class="inv-title">Daily Inventory Sheet</div>
                <div class="inv-sub">{{ $sheet->sheet_date->format('F j, Y') }}</div>
            </div>
            <div class="inv-actions">
                <button type="button" class="btn" data-export="#sheetTable" data-filename="brewing-bar-daily-sheet">📋 Export CSV</button>

                @if ($isOpen)
                    <button type="submit" class="btn" formaction="{{ route('inventory.sheet.use-current', $sheet) }}">Use Current Stock</button>
                    <button type="submit" class="btn btn-blue" formaction="{{ route('inventory.sheet.beginning', $sheet) }}">Save Beginning</button>
                @else
                    <span class="badge badge-ok" style="padding:7px 12px;font-size:12px;border:1px solid #16a34a50;">Beginning saved</span>
                    <button type="submit" class="btn" formaction="{{ route('inventory.sheet.ending', $sheet) }}">Save Ending</button>
                @endif

                <button type="submit" class="btn btn-ghost"
                    formaction="{{ route('inventory.sheet.reset', $sheet) }}"
                    onclick="return confirm('Reset the daily sheet? Counts will be cleared and any stock already deducted will be put back.');">Reset</button>

                <button type="submit" id="closeDayBtn" class="btn btn-primary"
                    formaction="{{ route('inventory.sheet.close', $sheet) }}"
                    @disabled(! $isSaved)
                    onclick="return confirm('Close the day? Stock will be set to the Ending counts.');">Close Day →</button>
            </div>
        </div>

        <div class="inv-card step-card">
            <div class="step-left">
                <span class="step-pill {{ $isSaved ? 's2' : 's1' }}">{{ $isSaved ? 'Step 2 of 2' : 'Step 1 of 2' }}</span>
                <div>
                    <div style="font-size:14px;font-weight:700;">
                        {{ $isSaved ? 'Count ending inventory at close time' : 'Count and save opening inventory' }}
                    </div>
                    <div class="inv-sub">
                        {{ $isSaved
                            ? 'Beginning counts are locked. Enter Ending values; Out is calculated automatically.'
                            : 'Enter each Beginning value, or copy current stock, then select Save Beginning.' }}
                    </div>
                </div>
            </div>
            <span style="font-size:12px;font-weight:600;color:{{ $sheetBeginningCount >= $sheetTotal && $sheetTotal > 0 ? 'var(--green)' : 'var(--muted)' }};">
                {{ $sheetBeginningCount }}/{{ $sheetTotal }} opening counts
            </span>
        </div>
@endif

{{-- Summary + history --}}
<div class="inv-cards">
    <div class="inv-card inv-stat">
        <div class="inv-stat-label">Items Tracked</div>
        <div class="inv-stat-value mono">{{ $sheetFilled }} / {{ $sheetTotal }}</div>
    </div>
    <div class="inv-card inv-stat">
        <div class="inv-stat-label">Total Consumed {{ $ro ? 'That Day' : 'Today' }}</div>
        <div class="inv-stat-value mono" style="color:var(--muted);">{{ $sheetTotalOut > 0 ? $fmt($sheetTotalOut) : '—' }}</div>
    </div>
    <div class="inv-card inv-stat">
        <div class="inv-stat-label">Days on Record</div>
        <div class="inv-stat-value mono" style="color:var(--muted);">{{ $sheetHistory->count() }}</div>
    </div>
    @if ($sheetHistory->isNotEmpty())
        <div class="inv-card inv-stat" style="flex:1;min-width:260px;">
            <div class="inv-stat-label" style="margin-bottom:6px;">Previous Days</div>
            <div class="chips">
                @foreach ($sheetHistory as $h)
                    <a class="hist-chip {{ $ro && $sheet->id === $h->id ? 'is-active' : '' }}"
                        href="{{ route('inventory.index', ['tab' => 'sheet', 'sheet' => $h->id]) }}">{{ $h->sheet_date->format('M j, Y') }}</a>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- Both areas, stacked --}}
<table id="sheetTable" style="display:none;"></table>

@forelse ($sheetAreas as $area => $groups)
    @php $am = $areaMeta[$area] ?? ['label' => $area, 'color' => '#a08060']; @endphp
    <section class="area">
        <div class="area-head">
            <span class="area-title" style="color:{{ $am['color'] }};">{{ $am['label'] }}</span>
            <span class="area-count">{{ collect($groups)->flatten(1)->filter(fn ($e) => $e->beginning !== null || $e->ending !== null)->count() }}/{{ collect($groups)->flatten(1)->count() }} filled</span>
        </div>

        @foreach ($groups as $group => $entries)
            @php
                $gm = $groupMeta[$group] ?? ['emoji' => '📦', 'color' => '#a08060'];
                $groupOut = $entries->sum(fn ($e) => $e->out() ?? 0);
            @endphp
            <details class="group" open style="border-color:{{ $gm['color'] }}30;">
                <summary style="color:{{ $gm['color'] }};">
                    <span>{{ $gm['emoji'] }} {{ $group }} <span class="group-meta">· {{ $entries->count() }} items</span></span>
                    <span style="display:flex;align-items:center;gap:12px;">
                        @if ($groupOut > 0)
                            <span class="group-meta">{{ $fmt($groupOut) }} consumed today</span>
                        @endif
                        <span class="chev">▶</span>
                    </span>
                </summary>

                <div class="inv-table-wrap">
                    <table class="inv-table sheet-table" data-area="{{ $area }}" data-group="{{ $group }}">
                        <thead>
                            <tr>
                                <th style="width:32%;">Item</th>
                                <th class="num" style="width:16%;">Beginning</th>
                                <th class="num" style="width:16%;">Ending</th>
                                <th class="num" style="width:12%;">Out</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entries as $entry)
                                @php
                                    $item = $entry->inventoryItem;
                                    $unit = $item->unit?->abbreviation;
                                    $out = $entry->out();
                                @endphp
                                <tr data-entry>
                                    <td>
                                        {{ $item->name }}
                                        @if ($unit)
                                            <span style="color:var(--dim);font-size:11px;">({{ $unit }})</span>
                                        @endif
                                    </td>
                                    <td class="num">
                                        @if ($ro)
                                            <span class="mono">{{ $entry->beginning !== null ? $fmt($entry->beginning) : '—' }}</span>
                                        @else
                                            <input type="number" min="0" step="0.001" class="cell-input js-beg"
                                                name="entries[{{ $item->id }}][beginning]"
                                                value="{{ $entry->beginning !== null ? $fmt($entry->beginning) : '' }}"
                                                @readonly(! $isOpen)>
                                        @endif
                                    </td>
                                    <td class="num">
                                        @if ($ro)
                                            <span class="mono">{{ $entry->ending !== null ? $fmt($entry->ending) : '—' }}</span>
                                        @else
                                            <input type="number" min="0" step="0.001" class="cell-input js-end"
                                                name="entries[{{ $item->id }}][ending]"
                                                value="{{ $entry->ending !== null ? $fmt($entry->ending) : '' }}"
                                                @readonly($isOpen)>
                                        @endif
                                    </td>
                                    <td class="num mono js-out">
                                        @if ($out === null)
                                            <span style="color:var(--dim);">—</span>
                                        @else
                                            <span class="{{ $out >= 0 ? 'out-pos' : 'out-neg' }}">{{ $fmt($out) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($ro)
                                            <span style="color:var(--muted);">{{ $entry->remarks }}</span>
                                        @else
                                            <input type="text" maxlength="255" placeholder="Remarks"
                                                name="entries[{{ $item->id }}][remarks]"
                                                value="{{ $entry->remarks }}">
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @endforeach
    </section>
@empty
    <div class="inv-card empty">No active stock items to count. Activate items in Stock Management first.</div>
@endforelse

@unless ($ro)
    </form>
@endunless

<script>
    (function () {
        var fmt = function (n) { return String(Math.round(n * 1000) / 1000); };

        function refreshRow(tr) {
            var beg = tr.querySelector('.js-beg'), end = tr.querySelector('.js-end'), out = tr.querySelector('.js-out');
            if (!beg || !end || !out) return;
            if (beg.value === '' || end.value === '') {
                out.innerHTML = '<span style="color:var(--dim);">—</span>';
                return;
            }
            var v = parseFloat(beg.value) - parseFloat(end.value);
            out.innerHTML = '<span class="' + (v >= 0 ? 'out-pos' : 'out-neg') + '">' + fmt(v) + '</span>';
        }

        function refreshClose() {
            var btn = document.getElementById('closeDayBtn');
            if (!btn) return;
            var ends = document.querySelectorAll('.js-end');
            var saved = {{ $isSaved ? 'true' : 'false' }};
            var complete = ends.length > 0 && Array.prototype.every.call(ends, function (i) { return i.value !== ''; });
            btn.disabled = !(saved && complete);
        }

        document.querySelectorAll('[data-entry]').forEach(function (tr) {
            tr.addEventListener('input', function () { refreshRow(tr); refreshClose(); });
        });
        refreshClose();

        // Build a flat table for CSV export (the visible sheet is split into grouped tables).
        // Rebuilt on every click so it always contains what is currently typed.
        var flat = document.getElementById('sheetTable');
        function buildFlat() {
            var html = '<tr><th>Area</th><th>Group</th><th>Item</th><th>Beginning</th><th>Ending</th><th>Out</th><th>Remarks</th></tr>';
            document.querySelectorAll('.sheet-table').forEach(function (t) {
                t.querySelectorAll('tbody tr').forEach(function (tr) {
                    var beg = tr.querySelector('.js-beg'), end = tr.querySelector('.js-end'), rem = tr.querySelector('input[type=text]');
                    var cells = tr.querySelectorAll('td');
                    var esc = function (v) { return String(v).replace(/&/g, '&amp;').replace(/</g, '&lt;'); };
                    html += '<tr><td>' + esc(t.dataset.area) + '</td><td>' + esc(t.dataset.group) + '</td><td>' +
                        esc(cells[0].innerText.replace(/\s+/g, ' ').trim()) + '</td><td>' +
                        esc(beg ? beg.value : cells[1].innerText.trim()) + '</td><td>' +
                        esc(end ? end.value : cells[2].innerText.trim()) + '</td><td>' +
                        esc(cells[3].innerText.trim()) + '</td><td>' +
                        esc(rem ? rem.value : cells[4].innerText.trim()) + '</td></tr>';
                });
            });
            flat.innerHTML = html;
        }
        document.querySelectorAll('[data-export="#sheetTable"]').forEach(function (b) {
            b.addEventListener('click', buildFlat, true);
        });
    })();
</script>
