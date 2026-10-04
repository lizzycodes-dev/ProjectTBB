<x-app-layout>

    <div class="dashboard-page">

        {{-- Dashboard Header --}}
        <div class="dashboard-header">

            <div>
                <h1>Manager Overview</h1>

                <p>
                    {{ now()->format('F j, Y') }} &nbsp;|&nbsp; Daily Summary
                </p>
            </div>

        </div>


        {{-- Overview Grid: summary cards, low-stock, best-selling, inventory --}}
        <div class="overview-grid">

            <div class="summary-card g-c1-r1">

                <div class="card-title">
                    Daily Revenue
                </div>

                <div class="card-value">
                    ₱{{ number_format($dailyRevenue, 2) }}
                </div>

            </div>


            <div class="summary-card g-c2-r1">

                <div class="card-title">
                    Cash Sales
                </div>

                <div class="card-value">
                    ₱{{ number_format($cashSales, 2) }}
                </div>

            </div>


            <div class="summary-card g-c3-r1">

                <div class="card-title">
                    GCash Sales
                </div>

                <div class="card-value">
                    ₱{{ number_format($gcashSales, 2) }}
                </div>

            </div>


            <div class="summary-card g-c1-r2">

                <div class="card-title">
                    Discounts Given
                </div>

                <div class="card-value">
                    ₱{{ number_format($discountsGiven, 2) }}
                </div>

            </div>


            <div class="summary-card g-c2-r2">

                <div class="card-title">
                    Avg. Order Value
                </div>

                <div class="card-value">
                    ₱{{ number_format($avgOrderValue, 2) }}
                </div>

            </div>


            <div class="dashboard-panel low-stock-panel">

                <div class="panel-title">
                    Low-Stock Alerts ({{ $lowStockItems->count() }})
                </div>

                @forelse ($lowStockItems->take(2) as $item)

                    <div class="low-stock-item">

                        <strong>{{ $item->name }}</strong>

                        <span>{{ $item->current_stock + 0 }} {{ $item->unit->abbreviation ?? 'pcs' }}</span>

                        <small>
                            Threshold: {{ $lowStockThreshold }} {{ $item->unit->abbreviation ?? 'pcs' }}
                        </small>

                    </div>

                @empty

                    <div class="low-stock-item">

                        <strong>All items are well stocked.</strong>

                    </div>

                @endforelse


                @if ($lowStockItems->count() > 2)
                    <div class="low-stock-more">
                        ...
                    </div>
                @endif

            </div>


            {{-- Best Selling --}}
            <div class="dashboard-panel g-c1-r3">

                <div class="panel-title">
                    Best-Selling Items
                </div>

                <div class="table-header">

                    <span>#</span>
                    <span>Product Name</span>
                    <span>Sold</span>

                </div>


                @forelse ($bestSellers as $bestSeller)

                    <div class="table-row">

                        <span>{{ $loop->iteration }}</span>

                        <span>
                            {{ $bestSeller->InventoryItem->name ?? 'Unknown item' }}
                        </span>

                        <span>
                            {{ (int) $bestSeller->total_sold }} sold
                        </span>

                    </div>

                @empty

                    <div class="empty-row">No sales yet.</div>

                @endforelse

            </div>


            {{-- Current Inventory --}}
            <div class="dashboard-panel g-c2-r3">

                <div class="panel-title">
                    Current Inventory
                </div>


                @forelse ($currentInventory as $item)

                    <div class="inventory-row">

                        <div>
                            <strong>
                                {{ $item->name }}
                            </strong>
                        </div>

                        <span>
                            {{ $item->current_stock + 0 }} {{ $item->unit->abbreviation ?? 'pcs' }}
                        </span>

                    </div>

                @empty

                    <div class="empty-row">No inventory items yet.</div>

                @endforelse

            </div>

        </div>


        {{-- Daily Sales --}}
        <div class="dashboard-panel sales-panel">

            <div class="panel-title">
                Daily Sales Log
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

                    <span>{{ substr($order->order_number, -4) }}</span>
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