<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['branch', 'restaurantTable', 'waiter', 'customer'])
            ->withCount('orderItems');

        if ($request->filled('order_no')) {
            $query->where('order_number', 'like', '%' . $request->order_no . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        $statuses = ['pending', 'sent_to_kitchen', 'preparing', 'ready', 'delivered', 'closed'];
        $statusCounts = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        $summary = [
            'today'     => Order::whereDate('created_at', today())->count(),
            'pending'   => $statusCounts['pending'] ?? 0,
            'preparing' => ($statusCounts['sent_to_kitchen'] ?? 0) + ($statusCounts['preparing'] ?? 0),
            'ready'     => $statusCounts['ready'] ?? 0,
            'revenue'   => Order::whereIn('status', ['delivered','closed'])->sum('total'),
        ];

        $branches = \App\Models\Branch::where('status', 'active')->get(['id', 'name']);

        return view('admin.orders.index', compact('orders', 'summary', 'statusCounts', 'branches', 'statuses'));
    }

    public function show(Order $order)
    {
        $order->load(['branch', 'restaurantTable', 'waiter', 'customer', 'orderItems.menuItem', 'invoice', 'kitchenOrders']);
        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load(['orderItems.menuItem', 'restaurantTable', 'waiter', 'customer']);
        $waiters = User::role('waiter')->where('status', 'active')->get();
        return view('admin.orders.edit', compact('order', 'waiters'));
    }

    public function cancel(Order $order)
    {
        $order->update(['status' => 'cancelled']);
        return redirect()->route('admin.orders.index')
            ->with('success', 'Order cancelled successfully.');
    }

    public function reassignWaiter(Request $request, Order $order)
    {
        $validated = $request->validate([
            'waiter_id' => 'required|exists:users,id',
        ]);

        $order->update(['waiter_id' => $validated['waiter_id']]);

        return redirect()->route('admin.orders.index')
            ->with('success', 'Waiter reassigned successfully.');
    }
}
