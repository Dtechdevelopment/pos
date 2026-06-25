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

        $waiters = User::whereHas('roles', fn($q) => $q->where('name', 'waiter'))
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

    public function waiterReport(Request $request, string $type): JsonResponse
    {
        $branchId = $request->user()->branch_id;
        $dateFrom = $request->date_from ?? today()->startOfMonth();
        $dateTo = $request->date_to ?? today();
        $waiterId = $request->waiter_id;

        $scope = fn($q) => $q
            ->when($branchId, fn($q2) => $q2->where('branch_id', $branchId))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        $waiterScope = fn($q) => $q
            ->when($branchId, fn($q2) => $q2->where('branch_id', $branchId))
            ->when($waiterId, fn($q2) => $q2->where('waiter_id', $waiterId))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        $waiters = User::whereHas('roles', fn($q) => $q->where('name', 'waiter'))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($waiterId, fn($q) => $q->where('id', $waiterId))
            ->get(['id', 'name']);

        switch ($type) {
            case 'performance':
                return $this->waiterPerformance($waiters, $dateFrom, $dateTo, $branchId, $waiterId);
            case 'orders':
                return $this->waiterOrders($dateFrom, $dateTo, $branchId, $waiterId);
            case 'pending':
                return $this->waiterPending($dateFrom, $dateTo, $branchId, $waiterId);
            case 'unbilled':
                return $this->waiterUnbilled($dateFrom, $dateTo, $branchId, $waiterId);
            case 'paid':
                return $this->waiterPaid($dateFrom, $dateTo, $branchId, $waiterId);
            case 'cancelled':
                return $this->waiterCancelled($dateFrom, $dateTo, $branchId, $waiterId);
            case 'sales-by-waiter':
                return $this->waiterSalesByWaiter($waiters, $dateFrom, $dateTo, $branchId, $waiterId);
            default:
                return $this->error('Invalid report type. Use: performance, orders, pending, unbilled, paid, cancelled, sales-by-waiter', 422);
        }
    }

    private function getWaiterScope(?int $branchId, ?int $waiterId, string $dateFrom, string $dateTo): \Closure
    {
        return fn($q) => $q
            ->when($branchId, fn($q2) => $q2->where('branch_id', $branchId))
            ->when($waiterId, fn($q2) => $q2->where('waiter_id', $waiterId))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);
    }

    private function waiterPerformance($waiters, $dateFrom, $dateTo, $branchId, $waiterId): JsonResponse
    {
        $items = $waiters->map(function ($waiter) use ($dateFrom, $dateTo, $branchId) {
            $orders = Order::where('waiter_id', $waiter->id)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo);

            $invoices = Invoice::where('waiter_id', $waiter->id)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo);

            $totalOrders = (clone $orders)->count();
            $served = (clone $orders)->whereIn('status', ['delivered', 'closed'])->count();
            $cancelled = (clone $orders)->where('status', 'cancelled')->count();
            $bills = (clone $invoices)->count();
            $paid = (clone $invoices)->where('status', 'paid')->count();
            $sales = (clone $invoices)->where('status', 'paid')->sum('total');

            return [
                'id' => $waiter->id,
                'name' => $waiter->name,
                'total_orders' => $totalOrders,
                'total_served' => $served,
                'total_bills' => $bills,
                'total_paid' => $paid,
                'total_cancelled' => $cancelled,
                'total_sales' => $sales,
            ];
        })->sortByDesc('total_sales')->values();

        return $this->success([
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'summary' => [
                'total_waiters' => $items->count(),
                'total_sales' => $items->sum('total_sales'),
                'avg_sales_per_waiter' => $items->count() > 0 ? round($items->sum('total_sales') / $items->count(), 2) : 0,
            ],
            'items' => $items,
        ]);
    }

    private function waiterOrders($dateFrom, $dateTo, $branchId, $waiterId): JsonResponse
    {
        $orders = Order::with(['waiter', 'restaurantTable', 'invoice'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($waiterId, fn($q) => $q->where('waiter_id', $waiterId))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->latest()
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'table' => $o->restaurantTable->table_number ?? '—',
                'customer_name' => $o->customer_name ?? 'Walk-in',
                'created_at' => $o->created_at->format('Y-m-d H:i'),
                'order_status' => $o->status,
                'bill_status' => $o->invoice?->status ?? 'no bill',
                'payment_status' => $o->invoice?->status === 'paid' ? 'paid' : ($o->invoice ? 'pending' : 'no bill'),
                'total' => $o->total,
                'waiter_name' => $o->waiter->name ?? '—',
            ]);

        $active = $orders->whereIn('order_status', ['pending', 'sent_to_kitchen', 'preparing', 'ready'])->count();
        $delivered = $orders->whereIn('order_status', ['delivered', 'closed'])->count();

        return $this->success([
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'summary' => [
                'total_orders' => $orders->count(),
                'active' => $active,
                'delivered' => $delivered,
                'revenue' => $orders->sum('total'),
            ],
            'items' => $orders,
        ]);
    }

    private function waiterPending($dateFrom, $dateTo, $branchId, $waiterId): JsonResponse
    {
        $invoices = Invoice::with(['waiter', 'order.restaurantTable'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($waiterId, fn($q) => $q->where('waiter_id', $waiterId))
            ->whereIn('status', ['pending', 'partial'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->latest()
            ->get()
            ->map(function ($inv) {
                $ageMinutes = $inv->created_at->diffInMinutes(now());
                $age = $ageMinutes >= 60
                    ? floor($ageMinutes / 60) . 'h ' . ($ageMinutes % 60) . 'm'
                    : $ageMinutes . 'm';
                return [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'order_number' => $inv->order?->order_number ?? '—',
                    'table' => $inv->order?->restaurantTable->table_number ?? '—',
                    'waiter_name' => $inv->waiter->name ?? '—',
                    'total' => $inv->total,
                    'paid_amount' => $inv->paid_amount,
                    'balance' => $inv->total - $inv->paid_amount,
                    'status' => $inv->status,
                    'age' => $age,
                    'age_minutes' => $ageMinutes,
                    'created_at' => $inv->created_at->format('Y-m-d H:i'),
                ];
            });

        $oldestAge = $invoices->isNotEmpty() ? $invoices->max('age_minutes') : 0;
        $oldest = $oldestAge >= 60
            ? floor($oldestAge / 60) . 'h ' . ($oldestAge % 60) . 'm'
            : $oldestAge . 'm';

        return $this->success([
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'summary' => [
                'pending_count' => $invoices->count(),
                'total_pending' => $invoices->sum('balance'),
                'oldest_order' => $oldest,
            ],
            'items' => $invoices,
        ]);
    }

    private function waiterUnbilled($dateFrom, $dateTo, $branchId, $waiterId): JsonResponse
    {
        $billedOrderIds = Invoice::select('order_id')->pluck('order_id');

        $orders = Order::with(['waiter', 'restaurantTable'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($waiterId, fn($q) => $q->where('waiter_id', $waiterId))
            ->whereIn('status', ['delivered', 'closed'])
            ->whereNotIn('id', $billedOrderIds)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->latest()
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'table' => $o->restaurantTable->table_number ?? '—',
                'waiter_name' => $o->waiter->name ?? '—',
                'total' => $o->total,
                'status' => $o->status,
                'created_at' => $o->created_at->format('Y-m-d H:i'),
            ]);

        return $this->success([
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'summary' => [
                'unbilled_count' => $orders->count(),
                'total_value' => $orders->sum('total'),
            ],
            'items' => $orders,
        ]);
    }

    private function waiterPaid($dateFrom, $dateTo, $branchId, $waiterId): JsonResponse
    {
        $payments = Payment::with(['invoice.waiter'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($waiterId, fn($q) => $q->whereHas('invoice', fn($q2) => $q2->where('waiter_id', $waiterId)))
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->latest()
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'invoice_number' => $p->invoice?->invoice_number ?? '—',
                'order_number' => $p->invoice?->order?->order_number ?? '—',
                'waiter_name' => $p->invoice?->waiter->name ?? '—',
                'payment_method' => $p->payment_method,
                'amount' => $p->amount,
                'paid_at' => $p->paid_at ? $p->paid_at->format('Y-m-d H:i') : $p->created_at->format('Y-m-d H:i'),
            ]);

        $byWaiter = $payments->groupBy('waiter_name')->map(fn($pays, $name) => [
            'waiter_name' => $name,
            'paid_count' => $pays->count(),
            'total_collected' => $pays->sum('amount'),
        ])->values();

        return $this->success([
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'summary' => [
                'paid_count' => $payments->count(),
                'total_collected' => $payments->sum('amount'),
                'avg_payment' => $payments->count() > 0 ? round($payments->sum('amount') / $payments->count(), 2) : 0,
            ],
            'items' => $payments,
            'by_waiter' => $byWaiter,
        ]);
    }

    private function waiterCancelled($dateFrom, $dateTo, $branchId, $waiterId): JsonResponse
    {
        $orders = Order::with(['waiter'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($waiterId, fn($q) => $q->where('waiter_id', $waiterId))
            ->where('status', 'cancelled')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->latest()
            ->get();

        $cancelLogs = \App\Models\AuditLog::where('action', 'cancel_order')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->get()
            ->keyBy('order_id');

        $items = $orders->map(function ($o) use ($cancelLogs) {
            $log = $cancelLogs->get($o->id);
            return [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'waiter_name' => $o->waiter->name ?? '—',
                'total' => $o->total,
                'reason' => $log?->new_values['reason'] ?? $log?->description ?? 'No reason',
                'cancelled_by' => $log?->new_values['cancelled_by'] ?? '—',
                'cancelled_at' => $o->updated_at->format('Y-m-d H:i'),
            ];
        });

        $reasons = $items->groupBy('reason')->map(fn($g, $r) => ['reason' => $r, 'count' => $g->count()])->values();

        return $this->success([
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'summary' => [
                'cancelled_count' => $items->count(),
                'cancelled_value' => $items->sum('total'),
                'top_reason' => $reasons->isNotEmpty() ? $reasons->first()['reason'] : '—',
            ],
            'items' => $items,
            'reasons' => $reasons,
        ]);
    }

    private function waiterSalesByWaiter($waiters, $dateFrom, $dateTo, $branchId, $waiterId): JsonResponse
    {
        $items = $waiters->map(function ($waiter) use ($dateFrom, $dateTo, $branchId) {
            $invoices = Invoice::where('waiter_id', $waiter->id)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->where('status', 'paid')
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo);

            $count = (clone $invoices)->count();
            $total = (clone $invoices)->sum('total');

            return [
                'id' => $waiter->id,
                'name' => $waiter->name,
                'bill_count' => $count,
                'total_sales' => $total,
                'avg_bill' => $count > 0 ? round($total / $count, 2) : 0,
            ];
        })->sortByDesc('total_sales')->values();

        $topWaiter = $items->first();
        $bottomWaiter = $items->last();

        return $this->success([
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'summary' => [
                'top_waiter' => $topWaiter['name'] ?? '—',
                'total_revenue' => $items->sum('total_sales'),
                'revenue_gap' => $topWaiter && $bottomWaiter ? $topWaiter['total_sales'] - $bottomWaiter['total_sales'] : 0,
            ],
            'items' => $items,
        ]);
    }
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
