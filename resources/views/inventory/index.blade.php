<x-app-layout>
    @php
        // 2.500 -> "2.5", 3.000 -> "3", 0 -> "0"
        $fmt = function ($n) {
            $s = rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.');
            return $s === '' || $s === '-0' ? '0' : $s;
        };

        $tabs = [
            'sheet' => 'Daily Sheet',
            'stock' => 'Stock Management',
            'spoilage' => 'Spoilage Log',
        ];
        if ($isManager) {
            $tabs['suppliers'] = 'Suppliers';
        }
    @endphp

    @include('inventory.partials.styles')

    <div class="inv">

        {{-- Low stock alert (all tabs) --}}
        @if ($lowStocks->isNotEmpty())
            <div class="inv-alert inv-alert-low">
                <span class="inv-alert-icon">⚠️</span>
                <div>
                    <div class="inv-alert-title">Low Stock Alert</div>
                    <div class="inv-alert-text">
                        @foreach ($lowStocks as $low)
                            {{ $low->inventoryItem->name }}
                            ({{ $fmt($low->current_quantity) }} {{ $low->inventoryItem->unit?->abbreviation }}){{ ! $loop->last ? ' · ' : '' }}
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="inv-flash inv-flash-ok">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="inv-flash inv-flash-err">
                <strong>Please check the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Tabs --}}
        <div class="inv-tabs">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('inventory.index', ['tab' => $key]) }}"
                    class="inv-tab {{ $tab === $key ? 'is-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        @if ($tab === 'stock')
            @include('inventory.partials.stock')
        @elseif ($tab === 'spoilage')
            @include('inventory.partials.spoilage')
        @elseif ($tab === 'suppliers')
            @include('inventory.partials.suppliers')
        @else
            @include('inventory.partials.sheet')
        @endif
    </div>

    <script>
        // Download any table marked with data-export-target as a CSV file (Excel friendly).
        document.querySelectorAll('[data-export]').forEach(function (button) {
            button.addEventListener('click', function () {
                var table = document.querySelector(button.dataset.export);
                if (!table) return;

                var rows = [];
                table.querySelectorAll('tr').forEach(function (tr) {
                    if (tr.hidden) return;
                    var cells = [];
                    tr.querySelectorAll('th,td').forEach(function (cell) {
                        var input = cell.querySelector('input:not([type=hidden])');
                        var text = input ? input.value : cell.innerText;
                        cells.push('"' + text.replace(/\s+/g, ' ').trim().replace(/"/g, '""') + '"');
                    });
                    if (cells.length) rows.push(cells.join(','));
                });

                var blob = new Blob(['\ufeff' + rows.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = (button.dataset.filename || 'brewing-bar-inventory') + '-' +
                    new Date().toISOString().slice(0, 10) + '.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            });
        });
    </script>
</x-app-layout>
