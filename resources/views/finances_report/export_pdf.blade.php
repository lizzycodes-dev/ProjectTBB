<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Finance Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #3b2a20;
        }

        h1 {
            margin: 0 0 4px;
            font-size: 18px;
        }

        h2 {
            margin: 18px 0 6px;
            font-size: 13px;
        }

        .muted {
            color: #7a6a5f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th {
            padding: 5px 6px;
            background: #7a4b3a;
            color: #ffffff;
            font-size: 9px;
            text-align: left;
        }

        td {
            padding: 4px 6px;
            border-bottom: 1px solid #e5dccf;
        }

        .right {
            text-align: right;
        }

        .summary td {
            padding: 6px 8px;
            border: 1px solid #e5dccf;
            font-size: 11px;
        }

        .summary .label {
            width: 60%;
            background: #f5efe7;
        }

        .total-row td {
            border-top: 2px solid #7a4b3a;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <h1>The Brewing Bar - Finance Report</h1>
    <div class="muted">Generated {{ now()->format('m/d/Y h:i A') }}</div>
    <div class="muted">Filters: {{ $filters }}</div>

    <h2>Summary</h2>

    <table class="summary">
        <tr>
            <td class="label">Sales (Completed orders)</td>
            <td class="right">₱{{ number_format($totals['totalRevenue'], 2) }}</td>
        </tr>
        <tr>
            <td class="label">Cash Sales</td>
            <td class="right">₱{{ number_format($totals['cashSales'], 2) }}</td>
        </tr>
        <tr>
            <td class="label">GCash Sales</td>
            <td class="right">₱{{ number_format($totals['gcashSales'], 2) }}</td>
        </tr>
        <tr>
            <td class="label">Discounts Given</td>
            <td class="right">₱{{ number_format($totals['totalDiscounts'], 2) }}</td>
        </tr>
        <tr>
            <td class="label">Total Expenses</td>
            <td class="right">₱{{ number_format($totals['totalExpenses'], 2) }}</td>
        </tr>
        <tr>
            <td class="label"><strong>Total Revenue (Sales - Expenses)</strong></td>
            <td class="right"><strong>{{ $totals['netRevenue'] < 0 ? '-' : '' }}₱{{ number_format(abs($totals['netRevenue']), 2) }}</strong></td>
        </tr>
    </table>

    <h2>Daily Sales Log</h2>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Date</th>
                <th class="right">Items</th>
                <th>Payment</th>
                <th>Reference #</th>
                <th class="right">Discount</th>
                <th>Status</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->ordered_at->format('m/d/Y') }}</td>
                <td class="right">{{ $order->orderItems->sum('quantity') }}</td>
                <td>{{ $order->payment->payment_method ?? '—' }}</td>
                <td>{{ $order->payment->reference_number ?? '' }}</td>
                <td class="right">₱{{ number_format($order->discount_amount, 2) }}</td>
                <td>{{ $order->status }}</td>
                <td class="right">₱{{ number_format($order->total_amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="muted">No transactions found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Expense Report</h2>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Category</th>
                <th>Source</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($expenses as $expense)
            <tr>
                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('m/d/Y') }}</td>
                <td>{{ $expense->description }}</td>
                <td>{{ $expense->category ?? '—' }}</td>
                <td>
                    @if ($expense->purchase)
                        Purchase{{ $expense->purchase->reference_number ? ' · ' . $expense->purchase->reference_number : '' }}
                    @else
                        —
                    @endif
                </td>
                <td class="right">₱{{ number_format($expense->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="muted">No expenses found.</td>
            </tr>
            @endforelse

            <tr class="total-row">
                <td colspan="4" class="right">Total Expenses</td>
                <td class="right">₱{{ number_format($totals['totalExpenses'], 2) }}</td>
            </tr>
        </tbody>
    </table>

</body>

</html>