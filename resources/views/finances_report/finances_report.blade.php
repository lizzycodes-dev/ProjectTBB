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

</x-app-layout>