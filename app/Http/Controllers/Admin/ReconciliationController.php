<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\KitchenOrder;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ReconciliationLog;
use Illuminate\Http\Request;

class ReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $reconciliations = ReconciliationLog::with(['branch', 'creator'])->latest()->paginate(15)->withQueryString();

        $branchId = $request->branch_id ?? null;
        $date     = $request->date ?? today()->toDateString();

        $invoiceQ = Invoice::whereDate('created_at', $date);
        $paymentQ = Payment::whereDate('created_at', $date)->where('status', 'completed');
        if ($branchId) {
            $invoiceQ->where('branch_id', $branchId);
            $paymentQ->where('branch_id', $branchId);
        }

        // POS record for the selected date
        $posRecord = [
            'grossSales'    => (clone $invoiceQ)->sum('subtotal'),
            'discounts'     => (clone $invoiceQ)->sum('discount'),
            'tax'           => (clone $invoiceQ)->sum('tax'),
            'netSales'      => (clone $invoiceQ)->where('status', 'paid')->sum('total'),
            'cashSales'     => (clone $paymentQ)->where('payment_method', 'cash')->sum('amount'),
            'cardSales'     => (clone $paymentQ)->where('payment_method', 'card')->sum('amount'),
            'mobileSales'   => (clone $paymentQ)->where('payment_method', 'm_pesa')->sum('amount'),
            'bankSales'     => (clone $paymentQ)->where('payment_method', 'bank_transfer')->sum('amount'),
            'totalCollected'=> (clone $paymentQ)->sum('amount'),
            'paidCount'     => (clone $invoiceQ)->where('status', 'paid')->count(),
            'pendingCount'  => (clone $invoiceQ)->whereIn('status', ['pending','draft'])->count(),
            'voidCount'     => (clone $invoiceQ)->whereIn('status', ['void','cancelled'])->count(),
        ];
        $posRecord['variance'] = $posRecord['totalCollected'] - $posRecord['netSales'];

        // Totals for summary cards
        $totalSalesQty    = Order::count();
        $totalSalesAmount = Order::sum('total');
        $totalPaidQty     = Invoice::where('status', 'paid')->count();
        $totalPaidAmount  = Invoice::where('status', 'paid')->sum('total');
        $pendingAmount    = Invoice::whereIn('status', ['pending','draft'])->sum('total');
        $missingItems     = $totalSalesQty - $totalPaidQty;
        $missingSales     = $totalSalesAmount - $totalPaidAmount;

        $branches = Branch::where('status', 'active')->get(['id', 'name']);

        return view('admin.reconciliation.index', compact(
            'reconciliations', 'posRecord', 'branches', 'date',
            'totalSalesQty', 'totalSalesAmount', 'totalPaidQty', 'totalPaidAmount',
            'pendingAmount', 'missingItems', 'missingSales'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'notes'     => 'nullable|string',
        ]);

        $reconciliationDate = now();
        $branchId = $validated['branch_id'] ?? null;

        $kitchenQty = KitchenOrder::when($branchId, fn($q) => $q->whereHas('order', fn($q2) => $q2->where('branch_id', $branchId)))->count();
        $kitchenAmount = KitchenOrder::when($branchId, fn($q) => $q->whereHas('order', fn($q2) => $q2->where('branch_id', $branchId)))
            ->join('menu_items', 'kitchen_orders.menu_item_id', '=', 'menu_items.id')
            ->sum('menu_items.selling_price');

        $salesQty = Order::when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $salesAmount = Order::when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('total');

        $paidQty = Invoice::where('status', 'paid')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $paidAmount = Invoice::where('status', 'paid')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('total');

        $pendingPayments = Invoice::where('status', 'unpaid')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('total');

        $data = [
            'branch_id' => $branchId,
            'reconciliation_date' => $reconciliationDate,
            'kitchen_quantity' => $kitchenQty,
            'kitchen_amount' => $kitchenAmount,
            'sales_quantity' => $salesQty,
            'sales_amount' => $salesAmount,
            'paid_quantity' => $paidQty,
            'paid_amount' => $paidAmount,
            'missing_items' => $salesQty - $paidQty,
            'missing_sales' => $salesAmount - $paidAmount,
            'pending_payments' => $pendingPayments,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ];

        ReconciliationLog::create($data);

        return redirect()->route('admin.reconciliation.index')
            ->with('success', 'Reconciliation record created successfully.');
    }
}
