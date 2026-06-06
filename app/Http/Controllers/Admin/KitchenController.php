<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KitchenOrder;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KitchenController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->branch_id;

        $base = KitchenOrder::with(['order.restaurantTable', 'menuItem', 'chef'])
            ->when($branchId, fn($q) => $q->whereHas('order', fn($q2) => $q2->where('branch_id', $branchId)));

        $incoming = (clone $base)->where('status', 'pending')->latest()->get();
        $preparing = (clone $base)->where('status', 'preparing')->latest()->get();
        $ready    = (clone $base)->where('status', 'ready')->latest()->get();
        $delivered = (clone $base)->where('status', 'delivered')->whereDate('created_at', today())->latest()->limit(20)->get();

        $delayed  = (clone $base)->where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes(15))->get();

        $summary = [
            'pending'   => $incoming->count(),
            'preparing' => $preparing->count(),
            'ready'     => $ready->count(),
            'delayed'   => $delayed->count(),
            'done_today'=> KitchenOrder::whereIn('status', ['delivered','cancelled'])
                ->whereDate('created_at', today())->count(),
        ];

        $branches = \App\Models\Branch::where('status', 'active')->get(['id', 'name']);

        return view('admin.kitchen.index', compact(
            'incoming', 'preparing', 'ready', 'delivered',
            'delayed', 'summary', 'branches'
        ));
    }

    public function updateStatus(Request $request, KitchenOrder $kitchenOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,preparing,ready,delivered,cancelled',
        ]);

        $data = ['status' => $validated['status']];

        if ($validated['status'] === 'preparing') {
            $data['started_at'] = now();
            $data['chef_id'] = auth()->id();
        }

        if (in_array($validated['status'], ['delivered', 'cancelled'])) {
            $data['completed_at'] = now();
        }

        $kitchenOrder->update($data);

        return redirect()->route('admin.kitchen.index')
            ->with('success', 'Kitchen order status updated.');
    }

    public function analytics()
    {
        // SQLite-compatible avg prep time in minutes
        $avgPrepTime = KitchenOrder::whereNotNull('completed_at')
            ->whereNotNull('started_at')
            ->get()
            ->avg(fn($ko) => $ko->started_at->diffInMinutes($ko->completed_at)) ?? 0;

        $delayedCount = KitchenOrder::where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes(15))
            ->count();

        $mostOrdered = OrderItem::select('menu_item_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('menu_item_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        return view('admin.kitchen.analytics', compact('avgPrepTime', 'delayedCount', 'mostOrdered'));
    }
}
