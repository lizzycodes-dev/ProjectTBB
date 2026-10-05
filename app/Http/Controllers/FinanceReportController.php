<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FinanceReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['payment', 'cashier', 'orderItems']);

        // --- Filters ---
        if ($request->filled('year')) {
            $query->whereYear('ordered_at', $request->input('year'));
        }

        if ($request->filled('month')) {
            $query->whereMonth('ordered_at', $request->input('month'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('ordered_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('ordered_at', '<=', $request->input('date_to'));
        }

        // --- Totals (Completed orders only, to keep Pending/Ready out of revenue) ---
        $completed = (clone $query)->where('status', 'Completed')->get();

        $totalRevenue = $completed->sum('total_amount');
        $totalDiscounts = $completed->sum('discount_amount');

        $cashSales = $completed
            ->filter(fn ($order) => $order->payment?->payment_method === 'Cash')
            ->sum('total_amount');

        $gcashSales = $completed
            ->filter(fn ($order) => $order->payment?->payment_method === 'GCash')
            ->sum('total_amount');

        // --- Card filter (clicking a summary card filters the table only) ---
        $card = $request->input('card');
        $card = in_array($card, ['cash', 'gcash', 'discounts'], true) ? $card : null;

        $tableQuery = clone $query;

        if ($card !== null) {
            if (! $request->filled('status')) {
                $tableQuery->where('status', 'Completed');
            }

            if ($card === 'cash') {
                $tableQuery->whereHas('payment', fn ($q) => $q->where('payment_method', 'Cash'));
            } elseif ($card === 'gcash') {
                $tableQuery->whereHas('payment', fn ($q) => $q->where('payment_method', 'GCash'));
            } else {
                $tableQuery->where('discount_amount', '>', 0);
            }
        }

        // --- Expense report (same period filters; status and card filters don't apply) ---
        $expenseQuery = Expense::query();

        if ($request->filled('year')) {
            $expenseQuery->whereYear('expense_date', $request->input('year'));
        }

        if ($request->filled('month')) {
            $expenseQuery->whereMonth('expense_date', $request->input('month'));
        }

        if ($request->filled('date_from')) {
            $expenseQuery->whereDate('expense_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $expenseQuery->whereDate('expense_date', '<=', $request->input('date_to'));
        }

        $totalExpenses = (clone $expenseQuery)->sum('amount');

        // Total Revenue card = completed sales minus expenses
        $netRevenue = $totalRevenue - $totalExpenses;

        // --- Export (Excel / PDF): same filters, all rows, no pagination ---
        $export = $request->input('export');

        if (in_array($export, ['excel', 'pdf'], true)) {
            return $this->exportReport(
                $export,
                $request,
                $tableQuery,
                $expenseQuery,
                compact('totalRevenue', 'cashSales', 'gcashSales', 'totalDiscounts', 'totalExpenses', 'netRevenue')
            );
        }

        // --- Table rows ---
        $orders = $tableQuery->orderByDesc('ordered_at')->paginate(10)->withQueryString();

        $expenses = (clone $expenseQuery)
            ->with('purchase')
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'expense_page')
            ->withQueryString();

        $years = Order::selectRaw('DISTINCT YEAR(ordered_at) as year')->pluck('year')
            ->merge(Expense::selectRaw('DISTINCT YEAR(expense_date) as year')->pluck('year'))
            ->unique()
            ->sortDesc()
            ->values();

        return view('finances_report.finances_report', compact(
            'orders',
            'totalRevenue',
            'totalDiscounts',
            'cashSales',
            'gcashSales',
            'years',
            'card',
            'totalExpenses',
            'netRevenue',
            'expenses'
        ));
    }

    private function describeFilters(Request $request): string
    {
        $parts = [];

        if ($request->filled('year')) {
            $parts[] = 'Year: ' . $request->input('year');
        }

        if ($request->filled('month')) {
            $parts[] = 'Month: ' . \Carbon\Carbon::create()->month((int) $request->input('month'))->format('F');
        }

        if ($request->filled('status')) {
            $parts[] = 'Status: ' . $request->input('status');
        }

        if ($request->filled('date_from')) {
            $parts[] = 'From: ' . $request->input('date_from');
        }

        if ($request->filled('date_to')) {
            $parts[] = 'To: ' . $request->input('date_to');
        }

        $cards = [
            'cash' => 'Cash sales only',
            'gcash' => 'GCash sales only',
            'discounts' => 'Orders with discounts',
        ];

        $card = $request->input('card');

        if (is_string($card) && isset($cards[$card])) {
            $parts[] = $cards[$card];
        }

        return $parts ? implode(' | ', $parts) : 'All records';
    }

    private function exportReport(string $type, Request $request, $tableQuery, $expenseQuery, array $totals)
    {
        $orders = (clone $tableQuery)->orderByDesc('ordered_at')->get();

        $expenses = (clone $expenseQuery)
            ->with('purchase')
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->get();

        $filters = $this->describeFilters($request);
        $filename = 'finance-report-' . now()->format('Ymd-His');

        if ($type === 'pdf') {
            return Pdf::loadView('finances_report.export_pdf', compact('orders', 'expenses', 'totals', 'filters'))
                ->setPaper('a4', 'portrait')
                ->download($filename . '.pdf');
        }

        $moneyFormat = '"₱"#,##0.00';

        $spreadsheet = new Spreadsheet();

        // --- Summary sheet ---
        $summary = $spreadsheet->getActiveSheet();
        $summary->setTitle('Summary');
        $summary->fromArray([
            ['The Brewing Bar - Finance Report'],
            ['Generated', now()->format('m/d/Y h:i A')],
            ['Filters', $filters],
            [],
            ['Sales (Completed orders)', (float) $totals['totalRevenue']],
            ['Cash Sales', (float) $totals['cashSales']],
            ['GCash Sales', (float) $totals['gcashSales']],
            ['Discounts Given', (float) $totals['totalDiscounts']],
            ['Total Expenses', (float) $totals['totalExpenses']],
            ['Total Revenue (Sales - Expenses)', (float) $totals['netRevenue']],
        ], null, 'A1');
        $summary->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $summary->getStyle('A10:B10')->getFont()->setBold(true);
        $summary->getStyle('B5:B10')->getNumberFormat()->setFormatCode($moneyFormat);
        $summary->getColumnDimension('A')->setWidth(36);
        $summary->getColumnDimension('B')->setWidth(34);

        // --- Sales sheet ---
        $sales = $spreadsheet->createSheet();
        $sales->setTitle('Sales');

        $salesRows = [['Order #', 'Date', 'Items', 'Payment', 'Reference #', 'Discount', 'Status', 'Total']];

        foreach ($orders as $order) {
            $salesRows[] = [
                $order->order_number,
                $order->ordered_at->format('m/d/Y'),
                (int) $order->orderItems->sum('quantity'),
                $order->payment->payment_method ?? '',
                '',
                (float) $order->discount_amount,
                $order->status,
                (float) $order->total_amount,
            ];
        }

        $sales->fromArray($salesRows, null, 'A1');

        // Reference numbers must stay text (13 digits would turn into 1.23E+12).
        foreach ($orders as $i => $order) {
            $sales->setCellValueExplicit(
                'E' . ($i + 2),
                (string) ($order->payment->reference_number ?? ''),
                DataType::TYPE_STRING
            );
        }

        $lastSalesRow = max(2, count($salesRows));
        $sales->getStyle('A1:H1')->getFont()->setBold(true);
        $sales->getStyle('F2:F' . $lastSalesRow)->getNumberFormat()->setFormatCode($moneyFormat);
        $sales->getStyle('H2:H' . $lastSalesRow)->getNumberFormat()->setFormatCode($moneyFormat);

        foreach (range('A', 'H') as $col) {
            $sales->getColumnDimension($col)->setAutoSize(true);
        }

        // --- Expenses sheet ---
        $expenseSheet = $spreadsheet->createSheet();
        $expenseSheet->setTitle('Expenses');

        $expenseRows = [['Date', 'Description', 'Category', 'Source', 'Amount', 'Notes']];

        foreach ($expenses as $expense) {
            $expenseRows[] = [
                \Carbon\Carbon::parse($expense->expense_date)->format('m/d/Y'),
                $expense->description,
                $expense->category ?? '',
                $expense->purchase
                    ? 'Purchase' . ($expense->purchase->reference_number ? ' ' . $expense->purchase->reference_number : '')
                    : '',
                (float) $expense->amount,
                $expense->notes ?? '',
            ];
        }

        $expenseRows[] = ['', '', '', 'Total', (float) $totals['totalExpenses'], ''];

        $expenseSheet->fromArray($expenseRows, null, 'A1');

        $lastExpenseRow = count($expenseRows);
        $expenseSheet->getStyle('A1:F1')->getFont()->setBold(true);
        $expenseSheet->getStyle('D' . $lastExpenseRow . ':E' . $lastExpenseRow)->getFont()->setBold(true);
        $expenseSheet->getStyle('E2:E' . $lastExpenseRow)->getNumberFormat()->setFormatCode($moneyFormat);

        foreach (range('A', 'F') as $col) {
            $expenseSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}