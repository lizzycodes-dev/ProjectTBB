<x-app-layout>

    <div class="dashboard-page">

        {{-- =========================================================
             HEADER
             ========================================================= --}}
        <header class="dashboard-header">
            <div>
                <h1>Manager Overview</h1>
                <p>{{ now()->format('F j, Y') }} &nbsp;·&nbsp; Daily Summary</p>
            </div>


        </header>


        {{-- =========================================================
             OVERVIEW GRID
             ========================================================= --}}
        <div class="overview-grid">

            {{-- Row 1 — primary KPIs --}}
            <div class="summary-card g-c1-r1 tone-clay">
                <div class="summary-card-top">
                    <span class="summary-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3v18" />
                            <path d="M6 8h9a3 3 0 0 1 0 6H9a3 3 0 0 0 0 6h9" />
                        </svg>
                    </span>

                    <span class="summary-card-label">Daily Revenue</span>
                </div>

                <div class="summary-card-value">
                    ₱{{ number_format($dailyRevenue, 2) }}
                </div>

                <div class="summary-card-sub">
                    Total sales for today
                </div>
            </div>

            <div class="summary-card g-c2-r1 tone-sage">
                <div class="summary-card-top">
                    <span class="summary-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="6" width="18" height="12" rx="2" />
                            <path d="M3 10h18" />
                            <circle cx="8" cy="14" r="1" />
                        </svg>
                    </span>

                    <span class="summary-card-label">Cash Sales</span>
                </div>

                <div class="summary-card-value">
                    ₱{{ number_format($cashSales, 2) }}
                </div>

                <div class="summary-card-sub">
                    Paid via cash drawer
                </div>
            </div>

            <div class="summary-card g-c3-r1 tone-slate">
                <div class="summary-card-top">
                    <span class="summary-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <rect x="6" y="3" width="12" height="18" rx="2" />
                            <path d="M11 17h2" />
                        </svg>
                    </span>

                    <span class="summary-card-label">GCash Sales</span>
                </div>

                <div class="summary-card-value">
                    ₱{{ number_format($gcashSales, 2) }}
                </div>

                <div class="summary-card-sub">
                    Digital wallet transactions
                </div>
            </div>

            {{-- Row 2 — secondary KPIs --}}
            <div class="summary-card g-c1-r2 tone-amber">
                <div class="summary-card-top">
                    <span class="summary-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 12a8 8 0 1 1-16 0 8 8 0 0 1 16 0z" />
                            <path d="M8 12h8" />
                        </svg>
                    </span>

                    <span class="summary-card-label">Discounts Given</span>
                </div>

                <div class="summary-card-value">
                    ₱{{ number_format($discountsGiven, 2) }}
                </div>

                <div class="summary-card-sub">
                    Senior · PWD · Promos
                </div>
            </div>

            <div class="summary-card g-c2-r2 tone-plum">
                <div class="summary-card-top">
                    <span class="summary-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 19V5" />
                            <path d="M4 19h16" />
                            <path d="m7 15 4-4 3 2 5-6" />
                        </svg>
                    </span>

                    <span class="summary-card-label">Avg. Order Value</span>
                </div>

                <div class="summary-card-value">
                    ₱{{ number_format($avgOrderValue, 2) }}
                </div>

                <div class="summary-card-sub">
                    Mean revenue per order
                </div>
            </div>


            {{-- =====================================================
                 LOW-STOCK ALERTS
                 ===================================================== --}}
            <div class="dashboard-panel low-stock-panel">

                <div class="panel-title">
                    <span>Low-Stock Alerts</span>
                    <span class="panel-title-count">{{ $lowStockItems->count() }}</span>
                </div>

                @forelse ($lowStockPaginator as $item)

                @php
                $stock = (int) $item->current_stock;
                $threshold = (int) $lowStockThreshold;
                $isZero = $stock <= 0;
                    $statusClass=$isZero ? 'status-out' : 'status-low' ;
                    $label=$isZero ? 'Out of stock' : 'Running low' ;
                    $unit=$item->unit->abbreviation ?? 'pcs';
                    @endphp

                    <div class="low-stock-item {{ $statusClass }}">

                        <div class="low-stock-main">
                            <strong>{{ $item->name }}</strong>

                            <small>
                                {{ $label }} · Threshold {{ $threshold }} {{ $unit }}
                            </small>
                        </div>

                        <div class="low-stock-qty">
                            {{ $stock }}
                            <span>{{ $unit }}</span>
                        </div>

                    </div>

                    @empty

                    <div class="low-stock-empty">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 12l5 5L20 7" />
                        </svg>
                        <strong>All items are well stocked.</strong>
                        <span>No alerts at the moment.</span>
                    </div>

                    @endforelse


                    @if ($lowStockPaginator->hasPages())
                    <div class="pagination-wrap low-stock-pagination">
                        {{ $lowStockPaginator->links() }}
                    </div>
                    @endif

            </div>


            {{-- =====================================================
                 BEST-SELLING ITEMS
                 ===================================================== --}}
            <div class="dashboard-panel g-c1-r3">

                <div class="panel-title">
                    <span>Best-Selling Items</span>
                </div>

                <div class="table-header">
                    <span>#</span>
                    <span>Product Name</span>
                    <span>Sold</span>
                </div>

                @forelse ($bestSellers as $bestSeller)

                <div class="table-row">
                    <span>{{ $loop->iteration }}</span>
                    <span>{{ $bestSeller->InventoryItem->name ?? 'Unknown item' }}</span>
                    <span>{{ (int) $bestSeller->total_sold }} sold</span>
                </div>

                @empty

                <div class="empty-row">No sales yet.</div>

                @endforelse

            </div>


            {{-- =====================================================
                 CURRENT INVENTORY
                 ===================================================== --}}
            <div class="dashboard-panel g-c2-r3">

                <div class="panel-title">
                    <span>Current Inventory</span>
                </div>

                @forelse ($currentInventory as $item)

                @php
                $stock = (int) $item->current_stock;
                $unit = $item->unit->abbreviation ?? 'pcs';
                @endphp

                <div class="inventory-row">
                    <div>
                        <strong>{{ $item->name }}</strong>
                    </div>
                    <span class="inventory-qty">
                        {{ $stock }}
                        <small>{{ $unit }}</small>
                    </span>
                </div>

                @empty

                <div class="empty-row">No inventory items yet.</div>

                @endforelse

            </div>

        </div>


        {{-- =========================================================
             DAILY SALES LOG
             ========================================================= --}}
        <div class="dashboard-panel sales-panel">

            <div class="panel-title">
                <span>Daily Sales Log</span>
                <span class="panel-title-count">{{ $orders->total() }}</span>
            </div>

            <div class="sales-header">
                <span>Queue</span>
                <span>Order</span>
                <span>Items</span>
                <span>Payment</span>
                <span>Discount</span>
                <span>Total</span>
            </div>

            @forelse ($orders as $order)

            <div class="sales-row {{ $order->status !== 'Completed' ? 'muted-row' : '' }}">
                <span>{{ $order->queue_label }}</span>
                <span>{{ $order->order_number }}</span>
                <span>{{ $order->orderItems->sum('quantity') }}</span>
                <span>{{ $order->payment->payment_method ?? '—' }}</span>
                <span>₱{{ number_format($order->discount_amount, 2) }}</span>
                <span>₱{{ number_format($order->total_amount, 2) }}</span>
            </div>

            @empty

            <div class="empty-row">No orders yet today.</div>

            @endforelse

            <div class="pagination-wrap">
                {{ $orders->links() }}
            </div>

        </div>

    </div>

</x-app-layout>