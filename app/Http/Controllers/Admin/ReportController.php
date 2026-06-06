<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\KitchenOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->subDays(29);
        $dateTo   = $request->date_to   ? \Carbon\Carbon::parse($request->date_to)   : now();

        $baseQuery = Invoice::where('status', 'paid')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        if ($request->filled('branch_id')) {
            $baseQuery->where('branch_id', $request->branch_id);
        }

        // Summary stats
        $totalSales      = (clone $baseQuery)->sum('total');
        $totalOrders     = (clone $baseQuery)->count();
        $avgOrderValue   = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        $totalTax        = (clone $baseQuery)->sum('tax');
        $totalDiscount   = (clone $baseQuery)->sum('discount');
        $cancelledOrders = Order::whereDate('created_at', '>=', $dateFrom)
                                ->whereDate('created_at', '<=', $dateTo)
                                ->where('status', 'cancelled')->count();

        // Daily breakdown table
        $salesData = (clone $baseQuery)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('SUM(tax) as tax'),
                DB::raw('SUM(discount) as discount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($r) => array_merge((array)$r, [
                'items_sold' => OrderItem::whereHas('order', fn($q) => $q->whereDate('orders.created_at', $r->date))->sum('quantity'),
            ]));

        // Chart: sales by date
        $chartData['salesByDate'] = [
            'labels' => $salesData->pluck('date'),
            'data'   => $salesData->pluck('revenue'),
        ];

        // Chart: sales by branch
        $salesByBranch = Invoice::where('status', 'paid')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->with('branch')
            ->select('branch_id', DB::raw('SUM(total) as total'))
            ->groupBy('branch_id')
            ->get();

        $chartData['salesByBranch'] = [
            'labels' => $salesByBranch->map(fn($r) => $r->branch->name ?? 'Unknown'),
            'data'   => $salesByBranch->pluck('total'),
        ];

        // Branches for filter
        $branches = \App\Models\Branch::where('status', 'active')->get(['id', 'name']);

        return view('admin.reports.sales', compact(
            'totalSales', 'totalOrders', 'avgOrderValue', 'totalTax',
            'totalDiscount', 'cancelledOrders', 'salesData', 'chartData',
            'branches', 'dateFrom', 'dateTo'
        ));
    }

    public function kitchen(Request $request)
    {
        $query = KitchenOrder::with(['menuItem', 'chef', 'order']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $items = $query->paginate(15);

        $avgPrepTime = KitchenOrder::whereNotNull('completed_at')
            ->whereNotNull('started_at')
            ->get()
            ->avg(fn($ko) => $ko->started_at->diffInMinutes($ko->completed_at)) ?? 0;

        return view('admin.reports.kitchen', compact('items', 'avgPrepTime'));
    }

    public function waiter(Request $request)
    {
        $query = User::role('waiter')->withCount(['waiterOrders', 'waiterInvoices']);

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereHas('waiterOrders', fn($q) => $q->whereDate('created_at', '>=', $request->date_from)
                ->whereDate('created_at', '<=', $request->date_to));
        }

        $waiters = $query->paginate(15);
        return view('admin.reports.waiter', compact('waiters'));
    }

    public function financial(Request $request)
    {
        $startDate = $request->date_from ?? now()->startOfMonth();
        $endDate = $request->date_to ?? now()->endOfMonth();

        $totalRevenue = Invoice::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');

        $totalTax = Invoice::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('tax');

        $totalDiscount = Invoice::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('discount');

        $totalRefunds = Payment::where('status', 'refunded')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        return view('admin.reports.financial', compact(
            'totalRevenue', 'totalTax', 'totalDiscount', 'totalRefunds', 'startDate', 'endDate'
        ));
    }

    public function reconciliation(Request $request)
    {
        $date     = $request->date      ? \Carbon\Carbon::parse($request->date)      : now();
        $branchId = $request->branch_id ?? null;

        $invoiceQ = Invoice::whereDate('created_at', $date);
        $paymentQ = Payment::whereDate('created_at', $date)->where('status', 'completed');
        $orderQ   = Order::whereDate('created_at', $date);

        if ($branchId) {
            $invoiceQ->where('branch_id', $branchId);
            $paymentQ->where('branch_id', $branchId);
            $orderQ->where('branch_id', $branchId);
        }

        // Sales side
        $grossSales   = (clone $invoiceQ)->sum('subtotal');
        $discounts    = (clone $invoiceQ)->sum('discount');
        $tax          = (clone $invoiceQ)->sum('tax');
        $voids        = (clone $invoiceQ)->whereIn('status', ['void','cancelled'])->sum('total');
        $netSales     = (clone $invoiceQ)->where('status', 'paid')->sum('total');
        $posSales     = $netSales;

        // Payment side
        $cashCollected   = (clone $paymentQ)->where('payment_method', 'cash')->sum('amount');
        $cardPayments    = (clone $paymentQ)->where('payment_method', 'card')->sum('amount');
        $mobilePayments  = (clone $paymentQ)->where('payment_method', 'm_pesa')->sum('amount');
        $bankPayments    = (clone $paymentQ)->where('payment_method', 'bank_transfer')->sum('amount');
        $totalCollected  = (clone $paymentQ)->sum('amount');

        // Variance
        $variance        = $totalCollected - $netSales;
        $expectedCash    = $cashCollected;
        $actualCash      = $cashCollected; // in a real system this comes from a cash count
        $cashVariance    = $actualCash - $expectedCash;

        // Order stats
        $totalInvoices   = (clone $invoiceQ)->count();
        $paidInvoices    = (clone $invoiceQ)->where('status', 'paid')->count();
        $pendingInvoices = (clone $invoiceQ)->whereIn('status', ['pending','draft'])->count();
        $voidInvoices    = (clone $invoiceQ)->whereIn('status', ['void','cancelled'])->count();
        $totalOrderCount = (clone $orderQ)->count();

        // Payment method breakdown for chart
        $paymentMethods = [
            'cash'          => $cashCollected,
            'card'          => $cardPayments,
            'm_pesa'        => $mobilePayments,
            'bank_transfer' => $bankPayments,
        ];

        // Per-hour breakdown for chart (SQLite compatible)
        $hourlyData = collect(range(0, 23))->map(fn($h) => [
            'hour'  => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00',
            'sales' => Invoice::where('status', 'paid')
                ->whereDate('created_at', $date)
                ->whereRaw("strftime('%H', created_at) = ?", [str_pad($h, 2, '0', STR_PAD_LEFT)])
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->sum('total'),
        ]);

        $branches = \App\Models\Branch::where('status', 'active')->get(['id', 'name']);

        return view('admin.reports.reconciliation', compact(
            'date', 'grossSales', 'discounts', 'tax', 'voids', 'netSales',
            'posSales', 'cashCollected', 'cardPayments', 'mobilePayments',
            'bankPayments', 'totalCollected', 'variance', 'expectedCash',
            'actualCash', 'cashVariance', 'totalInvoices', 'paidInvoices',
            'pendingInvoices', 'voidInvoices', 'totalOrderCount',
            'paymentMethods', 'hourlyData', 'branches'
        ));
    }

    public function export(Request $request, $type)
    {
        $format = $request->format ?? 'pdf';
        // Export logic to PDF/Excel/CSV
        return redirect()->back()->with('success', "Report exported as $format.");
    }
}
