<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\KitchenOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $today = Carbon::today();
        $branchId = $request->user()->branch_id;

        $scope = fn($q) => $branchId ? $q->where('branch_id', $branchId) : $q;

        $stats = [
            'totalOrders' => Order::where($scope)->whereDate('created_at', $today)->count(),
            'totalKitchenOrders' => KitchenOrder::whereHas('order', $scope)->whereDate('created_at', $today)->count(),
            'totalBillsGenerated' => Invoice::where($scope)->whereDate('created_at', $today)->count(),
            'totalRevenue' => Invoice::where($scope)->whereDate('created_at', $today)->where('status', 'paid')->sum('total'),
            'totalPaymentsCollected' => Payment::where($scope)->whereDate('created_at', $today)->where('status', 'completed')->sum('amount'),
            'pendingPayments' => Invoice::where($scope)->where('status', 'pending')->sum('total'),
            'activeTables' => RestaurantTable::where($scope)->where('status', 'occupied')->count(),
            'activeWaiters' => User::role('waiter')->where('status', 'active')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'activeKitchenStaff' => User::role('kitchen_staff')->where('status', 'active')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'activeCashiers' => User::role('cashier')->where('status', 'active')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'avgOrderValue' => Order::where($scope)->whereDate('created_at', $today)->avg('total') ?? 0,
            'totalItemsSold' => OrderItem::whereHas('order', fn($q) => $q->where($scope)->whereDate('created_at', $today))->sum('quantity'),
        ];

        $analytics = [
            'salesToday' => Invoice::where($scope)->whereDate('created_at', $today)->where('status', 'paid')->sum('total'),
            'salesThisWeek' => Invoice::where($scope)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->where('status', 'paid')->sum('total'),
            'salesThisMonth' => Invoice::where($scope)->whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->where('status', 'paid')->sum('total'),
            'outstandingPayments' => Invoice::where($scope)->where('status', 'pending')->sum('total'),
        ];

        $dailySales = collect(range(6, 0))->map(fn($i) => [
            'date' => Carbon::today()->subDays($i)->format('D'),
            'total' => Invoice::where($scope)->whereDate('created_at', Carbon::today()->subDays($i))->where('status', 'paid')->sum('total'),
        ]);

        $chartData = [
            'dailySales' => [
                'labels' => $dailySales->pluck('date'),
                'data' => $dailySales->pluck('total'),
            ],
        ];

        $topItems = OrderItem::select('menu_item_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(subtotal) as revenue'))
            ->whereHas('order', fn($q) => $q->where($scope)->whereDate('created_at', $today))
            ->groupBy('menu_item_id')
            ->orderByDesc('qty')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $menuItem = $item->menuItem;
                return [
                    'name' => $menuItem?->name ?? 'Deleted Item',
                    'qty' => $item->qty,
                    'revenue' => $item->revenue,
                ];
            });

        $topWaiters = User::role('waiter')
            ->withCount(['waiterOrders as orders_count' => fn($q) => $q->whereDate('created_at', $today)])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get()
            ->map(fn($user) => [
                'name' => $user->name,
                'orders_count' => $user->orders_count,
                'revenue' => Invoice::where('waiter_id', $user->id)->whereDate('created_at', $today)->sum('total'),
            ])
            ->sortByDesc('orders_count')
            ->take(5)
            ->values();

        return $this->success([
            'stats' => $stats,
            'analytics' => $analytics,
            'chart_data' => $chartData,
            'top_items' => $topItems,
            'top_waiters' => $topWaiters,
        ]);
    }
}
