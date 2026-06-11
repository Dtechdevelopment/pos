<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\KitchenOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends ApiController
{
    public function sales(Request $request): JsonResponse
    {
        $branchId = $request->user()->branch_id;
        $dateFrom = $request->date_from ?? today()->startOfMonth();
        $dateTo = $request->date_to ?? today();

        $query = OrderItem::select(
            'menu_item_id',
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('SUM(subtotal) as total_amount')
        )
        ->whereHas('order', fn($q) => $q
            ->when($branchId, fn($q2) => $q2->where('branch_id', $branchId))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
        )
        ->groupBy('menu_item_id');

        $items = $query->get()->map(function ($item) {
            $menuItem = $item->menuItem;
            return [
                'item_name' => $menuItem?->name ?? 'Deleted Item',
                'quantity' => $item->total_qty,
                'amount' => $item->total_amount,
            ];
        })->sortByDesc('amount')->values();

        $totalSales = $items->sum('amount');
        $totalQty = $items->sum('quantity');

        return $this->success([
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'total_sales' => $totalSales,
            'total_quantity' => $totalQty,
            'items' => $items,
        ]);
    }

    public function kitchen(Request $request): JsonResponse
    {
        $branchId = $request->user()->branch_id;
        $dateFrom = $request->date_from ?? today();
        $dateTo = $request->date_to ?? today();

        $kitchenOrders = KitchenOrder::with(['menuItem', 'chef'])
            ->whereHas('order', fn($q) => $q
                ->when($branchId, fn($q2) => $q2->where('branch_id', $branchId))
            )
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->get();

        $preparedQty = $kitchenOrders->whereIn('status', ['ready', 'delivered'])->sum('quantity');
        $pendingQty = $kitchenOrders->where('status', 'pending')->sum('quantity');
        $totalQty = $kitchenOrders->sum('quantity');

        $avgPrepTime = $kitchenOrders->whereNotNull('completed_at')->whereNotNull('started_at')
            ->avg(fn($ko) => $ko->started_at->diffInMinutes($ko->completed_at)) ?? 0;

        return $this->success([
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'total_orders' => $kitchenOrders->count(),
            'total_quantity' => $totalQty,
            'prepared_quantity' => $preparedQty,
            'pending_quantity' => $pendingQty,
            'avg_prep_time_minutes' => round($avgPrepTime, 1),
        ]);
    }

    public function waiter(Request $request): JsonResponse
    {
        $branchId = $request->user()->branch_id;
        $dateFrom = $request->date_from ?? today()->startOfMonth();
        $dateTo = $request->date_to ?? today();

        $waiters = User::role('waiter')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get()
            ->map(function ($waiter) use ($dateFrom, $dateTo) {
                $orders = Order::where('waiter_id', $waiter->id)
                    ->whereDate('created_at', '>=', $dateFrom)
                    ->whereDate('created_at', '<=', $dateTo);

                $invoices = Invoice::where('waiter_id', $waiter->id)
                    ->whereDate('created_at', '>=', $dateFrom)
                    ->whereDate('created_at', '<=', $dateTo);

                return [
                    'id' => $waiter->id,
                    'name' => $waiter->name,
                    'total_orders' => (clone $orders)->count(),
                    'total_bills' => (clone $invoices)->count(),
                    'total_items_sold' => OrderItem::whereHas('order', fn($q) => $q
                        ->where('waiter_id', $waiter->id)
                        ->whereDate('created_at', '>=', $dateFrom)
                        ->whereDate('created_at', '<=', $dateTo)
                    )->sum('quantity'),
                    'total_sales' => (clone $invoices)->where('status', 'paid')->sum('total'),
                    'paid_amount' => (clone $invoices)->where('status', 'paid')->sum('paid_amount'),
                ];
            })
            ->sortByDesc('total_sales')
            ->values();

        return $this->success([
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'waiters' => $waiters,
        ]);
    }

    public function financial(Request $request): JsonResponse
    {
        $branchId = $request->user()->branch_id;
        $dateFrom = $request->date_from ?? today()->startOfMonth();
        $dateTo = $request->date_to ?? today();

        $scope = fn($q) => $q
            ->when($branchId, fn($q2) => $q2->where('branch_id', $branchId))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        $totalOrders = Order::where($scope)->count();
        $totalSales = Invoice::where($scope)->sum('total');
        $totalPaid = Invoice::where($scope)->where('status', 'paid')->sum('paid_amount');
        $totalPending = Invoice::where($scope)->whereIn('status', ['pending', 'partial'])->sum('total');
        $totalRefunded = Payment::where($scope)->where('status', 'refunded')->sum('amount');
        $totalDiscounts = Invoice::where($scope)->sum('discount');

        $methodBreakdown = Payment::where($scope)
            ->where('status', 'completed')
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        return $this->success([
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'total_orders' => $totalOrders,
            'total_sales' => $totalSales,
            'total_paid' => $totalPaid,
            'total_pending' => $totalPending,
            'total_refunded' => $totalRefunded,
            'total_discounts' => $totalDiscounts,
            'net_revenue' => $totalPaid - $totalRefunded,
            'payment_methods' => $methodBreakdown,
        ]);
    }

    public function reconciliation(Request $request): JsonResponse
    {
        $branchId = $request->user()->branch_id;
        $dateFrom = $request->date_from ?? today();
        $dateTo = $request->date_to ?? today();

        $scope = fn($q) => $q
            ->when($branchId, fn($q2) => $q2->where('branch_id', $branchId))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        $kitchenQty = KitchenOrder::whereHas('order', $scope)->sum('quantity');
        $kitchenAmount = Order::where($scope)->sum('total');

        $salesQty = OrderItem::whereHas('order', $scope)->sum('quantity');
        $salesAmount = Invoice::where($scope)->sum('total');

        $paidQty = InvoiceItem::whereHas('invoice', fn($q) => $q
            ->when($branchId, fn($q2) => $q2->where('branch_id', $branchId))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'paid')
        )->sum('quantity');
        $paidAmount = Payment::where($scope)->where('status', 'completed')->sum('amount');

        return $this->success([
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'kitchen' => ['quantity' => $kitchenQty, 'amount' => $kitchenAmount],
            'sales' => ['quantity' => $salesQty, 'amount' => $salesAmount],
            'paid' => ['quantity' => $paidQty, 'amount' => $paidAmount],
            'missing_sales' => [
                'quantity' => $kitchenQty - $salesQty,
                'amount' => $kitchenAmount - $salesAmount,
            ],
            'pending_payments' => [
                'quantity' => $salesQty - $paidQty,
                'amount' => $salesAmount - $paidAmount,
            ],
        ]);
    }
}
