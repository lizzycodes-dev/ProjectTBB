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
                    ₱0,000.00
                </div>

            </div>


            <div class="summary-card g-c2-r1">

                <div class="card-title">
                    Cash Sales
                </div>

                <div class="card-value">
                    ₱0,000.00
                </div>

            </div>


            <div class="summary-card g-c3-r1">

                <div class="card-title">
                    GCash Sales
                </div>

                <div class="card-value">
                    ₱0,000.00
                </div>

            </div>


            <div class="summary-card g-c1-r2">

                <div class="card-title">
                    Discounts Given
                </div>

                <div class="card-value">
                    ₱0,000.00
                </div>

            </div>


            <div class="summary-card g-c2-r2">

                <div class="card-title">
                    Avg. Order Value
                </div>

                <div class="card-value">
                    ₱0,000.00
                </div>

            </div>


            <div class="dashboard-panel low-stock-panel">

                <div class="panel-title">
                    Low-Stock Alerts (2)
                </div>

                <div class="low-stock-item">

                    <strong>Product Name</strong>

                    <span>0 g</span>

                    <small>
                        Threshold: 0 g
                    </small>

                </div>


                <div class="low-stock-item">

                    <strong>Product Name</strong>

                    <span>0 g</span>

                    <small>
                        Threshold: 0 g
                    </small>

                </div>


                <div class="low-stock-more">
                    ...
                </div>

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


                @for ($i = 1; $i <= 5; $i++)

                    <div class="table-row">

                    <span>#</span>

                    <span>
                        Product Name
                    </span>

                    <span>
                        0 sold
                    </span>

            </div>

            @endfor

        </div>


        {{-- Current Inventory --}}
        <div class="dashboard-panel g-c2-r3">

            <div class="panel-title">
                Current Inventory
            </div>


            @for ($i = 1; $i <= 5; $i++)

                <div class="inventory-row">

                <div>
                    <strong>
                        Product Name
                    </strong>
                </div>

                <span>
                    0 g
                </span>

        </div>

        @endfor

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


        @for ($i = 1; $i <= 4; $i++)

            <div class="sales-row">

            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>

    </div>

    @endfor


    <div class="pagination">

        <span class="disabled">
            ← Previous
        </span>

        <span class="page active">
            1
        </span>

        <span class="page">
            2
        </span>

        <span class="page">
            3
        </span>

        <span>
            ...
        </span>

        <span>
            Next →
        </span>

    </div>

    </div>

    </div>




</x-app-layout>