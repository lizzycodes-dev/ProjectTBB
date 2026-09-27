<x-app-layout>

    <div class="finance-page">

        {{-- Header --}}
        <div class="finance-header">

            <h1>Finances Report</h1>

            <a
                href="{{ route('finance-report.index', array_merge(request()->query(), ['export' => 'excel'])) }}"
                class="export-btn">
                Download to Excel
            </a>

        </div>

        {{-- Summary Strip --}}
        <div class="summary-grid">

            <div class="summary-card">
                <div class="card-title">Total Revenue</div>
                <div class="card-value">₱{{ number_format($totalRevenue, 2) }}</div>
            </div>

            <div class="summary-card">
                <div class="card-title">Cash Sales</div>
                <div class="card-value">₱{{ number_format($cashSales, 2) }}</div>
            </div>

            <div class="summary-card">
                <div class="card-title">GCash Sales</div>
                <div class="card-value">₱{{ number_format($gcashSales, 2) }}</div>
            </div>

            <div class="summary-card">
                <div class="card-title">Discounts Given</div>
                <div class="card-value">₱{{ number_format($totalDiscounts, 2) }}</div>
            </div>

        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('finance-report.index') }}" class="filters-row">

            <div class="filter-group">
                <select name="year" onchange="this.form.submit()">
                    <option value="">Select Year</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <select name="month" onchange="this.form.submit()">
                    <option value="">Select Month</option>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <select name="status" onchange="this.form.submit()">
                    <option value="">Select Status</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Ready" {{ request('status') == 'Ready' ? 'selected' : '' }}>Ready</option>
                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div class="filter-group date-group">
                <label>From:</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()">
            </div>

            <div class="filter-group date-group">
                <label>To:</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()">
            </div>

            @if (request()->anyFilled(['year', 'month', 'status', 'date_from', 'date_to']))
                <a href="{{ route('finance-report.index') }}" class="clear-filters">Clear</a>
            @endif

        </form>

        {{-- Sales Log Table --}}
        <div class="dashboard-panel sales-panel">

            <div class="panel-title">
                Daily Sales Log
            </div>

            <div class="sales-header">
                <span>Order #</span>
                <span>Date</span>
                <span>Items</span>
                <span>Payment</span>
                <span>Discount</span>
                <span>Status</span>
                <span>Total</span>
            </div>

            @forelse ($orders as $order)

                <div class="sales-row {{ $order->status !== 'Completed' ? 'muted-row' : '' }}">
                    <span>{{ $order->order_number }}</span>
                    <span>{{ $order->ordered_at->format('m/d/Y') }}</span>
                    <span>{{ $order->orderItems->sum('quantity') }}</span>
                    <span>{{ $order->payment->payment_method ?? '—' }}</span>
                    <span>₱{{ number_format($order->discount_amount, 2) }}</span>
                    <span>{{ $order->status }}</span>
                    <span>₱{{ number_format($order->total_amount, 2) }}</span>
                </div>

            @empty

                <div class="empty-row">No transactions found for this filter.</div>

            @endforelse

            <div class="pagination-wrap">
                {{ $orders->links() }}
            </div>

        </div>

    </div>


    <style>
        /* =========================
           FINANCE PAGE
        ========================= */

        .finance-page {
            padding: 28px 30px;
            color: #6b4328;
        }

        .finance-header {
            display: flex;
            align-items: center;
            justify-content: space-between;

            margin-bottom: 18px;
        }

        .finance-header h1 {
            margin: 0;

            font-family: Georgia, serif;
            font-size: 22px;
            font-weight: bold;
            font-style: italic;

            color: #6b4328;
        }

        .export-btn {
            padding: 9px 18px;

            border: 1px solid #9b7658;
            border-radius: 7px;

            background: transparent;

            color: #6b4328;
            text-decoration: none;

            font-family: Georgia, serif;
            font-size: 12px;
            font-weight: bold;
            font-style: italic;

            cursor: pointer;
        }

        .export-btn:hover {
            background: #ead8c4;
        }

        /* =========================
           SUMMARY STRIP
        ========================= */

        .summary-grid {
            display: grid;

            grid-template-columns: repeat(4, minmax(0, 1fr));

            gap: 12px;

            margin-bottom: 18px;
        }

        .summary-card {
            border-radius: 8px;

            padding: 10px 14px;

            background: #c9a98f;

            box-shadow: 0 2px 5px rgba(107, 67, 40, 0.14);
        }

        .summary-card .card-title {
            font-family: Georgia, serif;
            font-size: 12px;
            font-weight: bold;

            color: #5a3a24;
        }

        .summary-card .card-value {
            margin-top: 4px;

            font-family: Georgia, serif;
            font-size: 19px;
            font-weight: bold;

            color: #4a2f1c;
        }

        /* =========================
           FILTERS
        ========================= */

        .filters-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 16px;

            margin-bottom: 18px;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;

            border: 1px solid #cdb59a;
            border-radius: 6px;

            background: #fff;

            color: #6b4328;

            font-size: 13px;
        }

        .date-group {
            display: flex;
            align-items: center;
            gap: 8px;

            font-size: 13px;
            font-weight: 600;
        }

        .clear-filters {
            font-size: 12px;
            font-weight: bold;
            color: #9b7658;
            text-decoration: underline;
        }

        /* =========================
           PANEL / TABLE
        ========================= */

        .dashboard-panel {
            overflow: hidden;

            border-radius: 7px;

            background: #c9a98f;

            box-shadow: 0 2px 5px rgba(107, 67, 40, 0.16);
        }

        .panel-title {
            padding: 8px 14px;

            background: #b18463;

            color: white;

            font-family: Georgia, serif;
            font-size: 16px;
            font-weight: bold;
            font-style: italic;
        }

        .sales-header,
        .sales-row {
            display: grid;

            grid-template-columns: 1fr 1fr 0.7fr 1fr 1fr 0.9fr 1fr;

            gap: 10px;

            align-items: center;
        }

        .sales-header {
            padding: 10px 16px;

            background: #b8916f;

            color: #fff;

            font-size: 12px;
            font-weight: bold;
        }

        .sales-row {
            padding: 9px 16px;

            border-top: 1px solid #9b7658;

            color: #6b4328;

            font-size: 12px;
        }

        .sales-row.muted-row {
            color: #a5876c;
            font-style: italic;
        }

        .empty-row {
            padding: 24px 16px;

            text-align: center;

            color: #76543c;

            font-size: 13px;
        }

        .pagination-wrap {
            padding: 10px 16px;

            border-top: 1px solid #9b7658;

            color: #6b4328;

            font-size: 12px;
        }

        .pagination-wrap nav {
            display: flex;
            justify-content: center;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {

            .finance-page {
                padding: 20px;
            }

            .finance-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .filters-row {
                flex-direction: column;
                align-items: stretch;
            }

            .sales-header,
            .sales-row {
                font-size: 11px;
            }
        }
    </style>

</x-app-layout>