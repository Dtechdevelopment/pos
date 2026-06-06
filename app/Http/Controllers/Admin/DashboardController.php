<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\KitchenOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $stats = [
            'totalOrders' => Order::whereDate('created_at', $today)->count(),
            'totalKitchenOrders' => KitchenOrder::whereDate('created_at', $today)->count(),
            'totalBillsGenerated' => Invoice::whereDate('created_at', $today)->count(),
            'totalRevenue' => Invoice::whereDate('created_at', $today)->where('status', 'paid')->sum('total'),
            'totalPaymentsCollected' => Payment::whereDate('created_at', $today)->where('status', 'completed')->sum('amount'),
            'pendingPayments' => Invoice::where('status', 'pending')->sum('total'),
            'missingSales' => 0,
            'activeTables' => RestaurantTable::where('status', 'occupied')->count(),
            'activeWaiters' => User::role('waiter')->where('status', 'active')->count(),
            'activeKitchenStaff' => User::role('kitchen_staff')->where('status', 'active')->count(),
            'activeCashiers' => User::role('cashier')->where('status', 'active')->count(),
            'totalCustomersServed' => Order::whereDate('created_at', $today)->distinct('customer_id')->count('customer_id'),
            'avgOrderValue' => Order::whereDate('created_at', $today)->avg('total') ?? 0,
            'totalItemsSold' => OrderItem::whereHas('order', fn($q) => $q->whereDate('created_at', $today))->sum('quantity'),
            'voidOrders' => Order::whereDate('created_at', $today)->where('status', 'closed')->count(),
            'discountGiven' => Invoice::whereDate('created_at', $today)->sum('discount'),
        ];

        $analytics = [
            'salesToday' => Invoice::whereDate('created_at', $today)->where('status', 'paid')->sum('total'),
            'salesThisWeek' => Invoice::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('status', 'paid')->sum('total'),
            'salesThisMonth' => Invoice::whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->where('status', 'paid')->sum('total'),
            'outstandingPayments' => Invoice::where('status', 'pending')->sum('total'),
        ];

        $chartData = [];

        $dailySales = collect(range(6, 0))->map(fn($i) => [
            'date' => Carbon::today()->subDays($i)->format('D'),
            'total' => Invoice::whereDate('created_at', Carbon::today()->subDays($i))->where('status', 'paid')->sum('total'),
        ]);
        $chartData['dailySales'] = [
            'labels' => $dailySales->pluck('date'),
            'data' => $dailySales->pluck('total'),
        ];

        $revenueTrend = collect(range(29, 0))->map(fn($i) => [
            'date' => Carbon::today()->subDays($i)->format('M d'),
            'total' => Invoice::whereDate('created_at', Carbon::today()->subDays($i))->where('status', 'paid')->sum('total'),
        ]);
        $chartData['revenueTrend'] = [
            'labels' => $revenueTrend->pluck('date')->reverse()->values(),
            'data' => $revenueTrend->pluck('total')->reverse()->values(),
        ];

        $ordersVsSales = collect(range(6, 0))->map(fn($i) => [
            'date' => Carbon::today()->subDays($i)->format('D'),
            'orders' => Order::whereDate('created_at', Carbon::today()->subDays($i))->count(),
            'sales' => Invoice::whereDate('created_at', Carbon::today()->subDays($i))->where('status', 'paid')->sum('total'),
        ]);
        $chartData['ordersVsSales'] = [
            'labels' => $ordersVsSales->pluck('date'),
            'orders' => $ordersVsSales->pluck('orders'),
            'sales' => $ordersVsSales->pluck('sales'),
        ];

        $paid = Invoice::where('status', 'paid')->count();
        $unpaid = Invoice::whereIn('status', ['pending', 'draft'])->count();
        $chartData['paidUnpaid'] = ['data' => [$paid, $unpaid]];

        $topItems = OrderItem::select('menu_item_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(subtotal) as revenue'))
            ->whereHas('order', fn($q) => $q->whereDate('created_at', $today))
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

        $chartData['topItems'] = [
            'labels' => $topItems->pluck('name'),
            'data' => $topItems->pluck('qty'),
        ];

        $topWaiters = User::role('waiter')
            ->withCount(['waiterOrders as orders_count' => fn($q) => $q->whereDate('created_at', $today)])
            ->get()
            ->map(fn($user) => [
                'name' => $user->name,
                'orders_count' => $user->orders_count,
                'revenue' => Invoice::where('waiter_id', $user->id)->whereDate('created_at', $today)->sum('total'),
            ])
            ->sortByDesc('orders_count')
            ->take(5)
            ->values();

        $chartData['waiterPerformance'] = [
            'labels' => $topWaiters->pluck('name'),
            'orders' => $topWaiters->pluck('orders_count'),
            'revenue' => $topWaiters->pluck('revenue'),
        ];

        return view('admin.dashboard.index', compact('stats', 'analytics', 'chartData', 'topItems', 'topWaiters'));
    }
}
