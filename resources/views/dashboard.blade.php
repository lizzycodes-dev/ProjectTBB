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


    <style>
        /* =========================
       PAGE + HEADER
    ========================= */

        .dashboard-page {
            min-height: calc(100vh - 70px);
            padding: 30px;
            background: #f5f3ef;
            color: #302b27;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 26px;
        }

        .dashboard-header h1 {
            margin: 0;
            color: #302b27;
            font-family: inherit;
            font-size: 26px;
            font-weight: 750;
            font-style: normal;
            letter-spacing: -0.6px;
        }

        .dashboard-header p {
            margin: 6px 0 0;
            color: #817970;
            font-size: 13px;
            font-weight: 500;
        }

        /* =========================
       DASHBOARD GRID
    ========================= */

        .overview-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-template-rows: auto auto auto;
            gap: 18px;
            margin-bottom: 20px;
            align-items: stretch;
        }

        .g-c1-r1 {
            grid-column: 1;
            grid-row: 1;
        }

        .g-c2-r1 {
            grid-column: 2;
            grid-row: 1;
        }

        .g-c3-r1 {
            grid-column: 3;
            grid-row: 1;
        }

        .g-c1-r2 {
            grid-column: 1;
            grid-row: 2;
        }

        .g-c2-r2 {
            grid-column: 2;
            grid-row: 2;
        }

        .g-c1-r3 {
            grid-column: 1;
            grid-row: 3;
        }

        .g-c2-r3 {
            grid-column: 2;
            grid-row: 3;
        }

        /* =========================
       SUMMARY CARDS
    ========================= */

        .summary-card {
            position: relative;
            min-height: 118px;
            overflow: hidden;
            border: 1px solid #e9e4dc;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(54, 43, 32, 0.045);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 18px rgba(54, 43, 32, 0.08);
        }

        .summary-card::before {
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #a47b5b;
            content: "";
        }

        .summary-card.g-c2-r1::before {
            background: #6f927c;
        }

        .summary-card.g-c3-r1::before {
            background: #7895a8;
        }

        .summary-card.g-c1-r2::before {
            background: #c28a58;
        }

        .summary-card.g-c2-r2::before {
            background: #9184aa;
        }

        .card-title {
            padding: 18px 18px 0 20px;
            background: transparent;
            color: #817970;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            font-style: normal;
        }

        .card-value {
            padding: 10px 18px 18px 20px;
            color: #302b27;
            font-family: inherit;
            font-size: 25px;
            font-weight: 750;
            letter-spacing: -0.7px;
            text-align: left;
        }

        /* =========================
       PANELS
    ========================= */

        .dashboard-panel {
            overflow: hidden;
            border: 1px solid #e9e4dc;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(54, 43, 32, 0.045);
        }

        .panel-title {
            padding: 16px 18px;
            border-bottom: 1px solid #f0ece6;
            background: #fff;
            color: #302b27;
            font-family: inherit;
            font-size: 15px;
            font-weight: 700;
            font-style: normal;
        }

        /* =========================
       LOW STOCK ALERTS
    ========================= */

        .low-stock-panel {
            grid-column: 3;
            grid-row: 2 / span 2;
        }

        .low-stock-item {
            position: relative;
            margin: 12px;
            padding: 13px 14px;
            border: 1px solid #f0d9c6;
            border-left: 4px solid #d18a4d;
            border-radius: 10px;
            background: #fff8f1;
            color: #74685d;
            font-size: 12px;
        }

        .low-stock-item strong {
            display: block;
            padding-right: 60px;
            color: #3b332d;
            font-size: 13px;
            font-weight: 700;
        }

        .low-stock-item span {
            position: absolute;
            top: 13px;
            right: 14px;
            color: #b45f31;
            font-size: 13px;
            font-weight: 750;
        }

        .low-stock-item small {
            display: block;
            margin-top: 7px;
            color: #918477;
            font-size: 11px;
        }

        .low-stock-more {
            padding: 8px 18px 16px;
            color: #9a6b4b;
            font-size: 12px;
            font-weight: 650;
        }

        /* =========================
       BEST-SELLING ITEMS
    ========================= */

        .table-header,
        .table-row {
            display: grid;
            grid-template-columns: 28px minmax(0, 1fr) 70px;
            gap: 8px;
            align-items: center;
        }

        .table-header {
            padding: 10px 16px;
            background: #faf9f6;
            color: #8a8177;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-row {
            min-height: 38px;
            padding: 7px 16px;
            border-top: 1px solid #f1eee9;
            color: #514940;
            font-size: 12px;
        }

        .table-row span:first-child {
            color: #9c9288;
            font-size: 11px;
            font-weight: 650;
        }

        .table-row span:last-child {
            color: #6f927c;
            font-size: 11px;
            font-weight: 700;
            text-align: right;
        }

        /* =========================
       CURRENT INVENTORY
    ========================= */

        .inventory-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 0 16px;
            padding: 11px 0;
            border-bottom: 1px solid #f0ece6;
            color: #514940;
            font-size: 12px;
        }

        .inventory-row:last-child {
            border-bottom: none;
        }

        .inventory-row strong {
            color: #3b332d;
            font-size: 12px;
            font-weight: 650;
        }

        .inventory-row>span {
            color: #6f927c;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* =========================
       DAILY SALES LOG
    ========================= */

        .sales-panel {
            width: 100%;
        }

        .sales-header,
        .sales-row {
            display: grid;
            grid-template-columns: 0.7fr 1fr 1.4fr 1fr 1fr 1fr;
            gap: 10px;
            align-items: center;
        }

        .sales-header {
            padding: 12px 18px;
            background: #faf9f6;
            color: #817970;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.35px;
        }

        .sales-row {
            min-height: 42px;
            padding: 6px 18px;
            border-top: 1px solid #f0ece6;
            color: #514940;
            font-size: 12px;
        }

        /* =========================
       PAGINATION
    ========================= */

        .pagination {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 6px;
            padding: 14px 18px;
            border-top: 1px solid #f0ece6;
            color: #776d63;
            font-size: 12px;
        }

        .pagination .disabled,
        .pagination>span,
        .pagination .page {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            min-height: 32px;
            padding: 0 9px;
            border: 1px solid #e9e4dc;
            border-radius: 8px;
            background: #fff;
            color: #6d6258;
        }

        .pagination .disabled {
            color: #b7afa6;
            background: #faf9f6;
        }

        .pagination .page {
            cursor: pointer;
        }

        .pagination .page:hover {
            background: #f5f2ed;
        }

        .pagination .active {
            border-color: #6b4b36;
            background: #6b4b36;
            color: #fff;
        }

        /* =========================
       RESPONSIVE
    ========================= */

        @media (max-width: 1000px) {
            .overview-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                grid-template-rows: auto;
            }

            .g-c1-r1,
            .g-c2-r1,
            .g-c3-r1,
            .g-c1-r2,
            .g-c2-r2,
            .g-c1-r3,
            .g-c2-r3,
            .low-stock-panel {
                grid-column: auto;
                grid-row: auto;
            }

            .low-stock-panel {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 700px) {
            .dashboard-page {
                padding: 18px 14px;
            }

            .dashboard-header {
                align-items: flex-start;
                flex-direction: column;
                margin-bottom: 20px;
            }

            .dashboard-header h1 {
                font-size: 23px;
            }

            .overview-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .summary-card {
                min-height: 100px;
            }

            .sales-panel {
                overflow-x: auto;
            }

            .sales-header,
            .sales-row {
                min-width: 650px;
            }

            .pagination {
                justify-content: center;
            }
        }
    </style>

</x-app-layout>