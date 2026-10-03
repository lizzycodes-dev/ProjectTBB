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
           DASHBOARD
        ========================= */

        .dashboard-page {
            min-height: calc(100vh - 70px);
            padding: 28px 30px;
            background: #f8f1e8;
            color: #6b4328;
        }


        /* =========================
           HEADER
        ========================= */

        .dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 18px;
        }


        .dashboard-header h1 {
            margin: 0;

            font-family: Georgia, serif;
            font-size: 22px;
            font-weight: bold;
            font-style: italic;

            color: #6b4328;
        }


        .dashboard-header p {
            margin: 3px 0 0;

            color: #8b6a50;

            font-size: 12px;
            font-weight: 600;
        }


        /* =========================
           SUMMARY
        ========================= */

        .overview-grid {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            grid-template-rows: auto auto 1fr;

            gap: 14px;

            margin-bottom: 14px;
        }


        .g-c1-r1 { grid-column: 1; grid-row: 1; }
        .g-c2-r1 { grid-column: 2; grid-row: 1; }
        .g-c3-r1 { grid-column: 3; grid-row: 1; }
        .g-c1-r2 { grid-column: 1; grid-row: 2; }
        .g-c2-r2 { grid-column: 2; grid-row: 2; }
        .g-c1-r3 { grid-column: 1; grid-row: 3; }
        .g-c2-r3 { grid-column: 2; grid-row: 3; }


        .summary-card {
            min-height: 80px;

            overflow: hidden;

            border-radius: 7px;

            background: #c9a98f;

            box-shadow:
                0 2px 5px rgba(107, 67, 40, 0.16);
        }


        .card-title {
            padding: 5px 10px;

            background: #b18463;

            color: white;

            font-family: Georgia, serif;
            font-size: 14px;
            font-weight: bold;
            font-style: italic;
        }


        .card-value {
            padding: 18px 14px;

            text-align: right;

            color: #76543c;

            font-family: Georgia, serif;
            font-size: 20px;
            font-weight: bold;
        }


        /* =========================
           PANELS
        ========================= */

        .dashboard-panel {
            overflow: hidden;

            border-radius: 7px;

            background: #c9a98f;

            box-shadow:
                0 2px 5px rgba(107, 67, 40, 0.12);
        }


        .panel-title {
            padding: 5px 10px;

            background: #b18463;

            color: white;

            font-family: Georgia, serif;
            font-size: 15px;
            font-weight: bold;
            font-style: italic;
        }


        /* =========================
           LOW STOCK
        ========================= */

        .low-stock-panel {
            grid-column: 3;
            grid-row: 2 / span 2;
        }


        .low-stock-item {
            position: relative;

            margin: 10px;

            padding: 9px;

            border-radius: 6px;

            background: #b18463;

            color: #76543c;

            font-size: 11px;
        }


        .low-stock-item strong {
            display: block;

            color: #76543c;
        }


        .low-stock-item span {
            position: absolute;

            top: 9px;
            right: 9px;

            font-weight: bold;
        }


        .low-stock-item small {
            display: block;

            margin-top: 3px;
        }


        .low-stock-more {
            padding: 18px 10px;

            color: #76543c;

            font-weight: bold;
        }


        /* =========================
           TABLE / INVENTORY
        ========================= */

        .table-header,
        .table-row {
            display: grid;

            grid-template-columns:
                25px 1fr 70px;

            gap: 5px;

            align-items: center;
        }


        .table-header {
            padding: 6px 10px;

            color: #76543c;

            font-size: 10px;
            font-weight: bold;
        }


        .table-row {
            padding: 4px 10px;

            color: #76543c;

            font-size: 12px;
        }


        .table-row span:last-child {
            text-align: right;
        }


        /* =========================
           INVENTORY
        ========================= */

        .inventory-row {
            display: flex;

            align-items: center;
            justify-content: space-between;

            margin: 0 10px;

            padding: 6px 0;

            border-bottom: 2px solid #8b5e3c;

            color: #76543c;

            font-size: 11px;
        }


        .inventory-row:last-child {
            border-bottom: none;
        }


        /* =========================
           SALES LOG
        ========================= */

        .sales-panel {
            width: 100%;
        }


        .sales-header,
        .sales-row {
            display: grid;

            grid-template-columns:
                0.7fr 1fr 1.4fr 1fr 1fr 1fr;

            gap: 10px;

            align-items: center;
        }


        .sales-header {
            padding: 7px 12px;

            background: #c9a98f;

            color: #76543c;

            font-size: 12px;
            font-weight: bold;
        }


        .sales-row {
            height: 20px;

            border-top: 1px solid #9b7658;
        }


        /* =========================
           PAGINATION
        ========================= */

        .pagination {
            display: flex;

            align-items: center;

            gap: 18px;

            padding: 7px 12px;

            border-top: 1px solid #9b7658;

            color: #b18463;

            font-size: 12px;
        }


        .pagination .disabled {
            color: #b49b88;
        }


        .pagination .page {
            cursor: pointer;
        }


        .pagination .active {
            padding: 6px 10px;

            border-radius: 6px;

            background: #b18463;

            color: white;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1000px) {

            .overview-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .g-c1-r1, .g-c2-r1, .g-c3-r1,
            .g-c1-r2, .g-c2-r2,
            .g-c1-r3, .g-c2-r3 {
                grid-column: auto;
                grid-row: auto;
            }

            .low-stock-panel {
                grid-column: auto;
                grid-row: auto;
            }

        }


        @media (max-width: 700px) {

            .dashboard-page {
                padding: 20px;
            }

            .dashboard-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .overview-grid {
                grid-template-columns: 1fr;
            }

        }
    </style>

</x-app-layout>