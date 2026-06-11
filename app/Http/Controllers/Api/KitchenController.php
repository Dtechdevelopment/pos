<?php

namespace App\Http\Controllers\Api;

use App\Models\KitchenOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KitchenController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $base = KitchenOrder::with(['order.restaurantTable', 'order.waiter', 'menuItem', 'chef']);

        $incoming = (clone $base)->where('status', 'pending')->latest()->get();
        $preparing = (clone $base)->where('status', 'preparing')->latest()->get();
        $ready = (clone $base)->where('status', 'ready')->latest()->get();
        $delivered = (clone $base)->whereIn('status', ['delivered', 'picked_up'])->whereDate('created_at', today())->latest()->limit(20)->get();
        $delayed = (clone $base)->where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes(15))->get();

        $summary = [
            'pending' => $incoming->count(),
            'preparing' => $preparing->count(),
            'ready' => $ready->count(),
            'delayed' => $delayed->count(),
            'done_today' => KitchenOrder::whereIn('status', ['delivered', 'picked_up', 'cancelled'])
                ->whereDate('created_at', today())->count(),
        ];

        return $this->success([
            'incoming' => $incoming,
            'preparing' => $preparing,
            'ready' => $ready,
            'delivered' => $delivered,
            'delayed' => $delayed,
            'summary' => $summary,
        ]);
    }

    public function updateStatus(Request $request, KitchenOrder $kitchenOrder): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,preparing,ready,picked_up,delivered,cancelled',
        ]);

        $kitchenOrder->status = $validated['status'];

        if ($validated['status'] === 'preparing') {
            $kitchenOrder->started_at = now();
            $kitchenOrder->chef_id = $request->user()->id;
        }

        if (in_array($validated['status'], ['delivered', 'picked_up', 'cancelled'])) {
            $kitchenOrder->completed_at = now();
        }

        $kitchenOrder->save();

        // Sync parent order status based on kitchen items
        // Order status = the slowest (least advanced) active kitchen item
        $order = $kitchenOrder->order;
        $statuses = $order->kitchenOrders()->pluck('status')->toArray();
        $active = array_diff($statuses, ['cancelled']);

        $priority = ['pending' => 1, 'preparing' => 2, 'ready' => 3, 'picked_up' => 4, 'delivered' => 5];
        $orderStatusMap = ['pending' => 'sent_to_kitchen', 'preparing' => 'preparing', 'ready' => 'ready', 'picked_up' => 'picked_up', 'delivered' => 'delivered'];

        if (empty($active)) {
            $order->status = 'cancelled';
        } else {
            $minStatus = collect($active)->min(fn($s) => $priority[$s] ?? 0);
            $minKey = array_search($minStatus, $priority);
            $order->status = $orderStatusMap[$minKey] ?? 'sent_to_kitchen';
        }
        $order->save();

        $kitchenOrder->load(['order.restaurantTable', 'menuItem', 'chef']);

        return $this->success($kitchenOrder, 'Kitchen order status updated');
    }

    public function analytics(Request $request): JsonResponse
    {
        $branchId = $request->user()->branch_id;

        $avgPrepTime = KitchenOrder::whereNotNull('completed_at')
            ->whereNotNull('started_at')
            ->when($branchId, fn($q) => $q->whereHas('order', fn($q2) => $q2->where('branch_id', $branchId)))
            ->get()
            ->avg(fn($ko) => $ko->started_at->diffInMinutes($ko->completed_at)) ?? 0;

        $delayedCount = KitchenOrder::where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes(15))
            ->when($branchId, fn($q) => $q->whereHas('order', fn($q2) => $q2->where('branch_id', $branchId)))
            ->count();

        $mostOrdered = \App\Models\OrderItem::select('menu_item_id', DB::raw('SUM(quantity) as total'))
            ->whereHas('order', fn($q) => $q->when($branchId, fn($q2) => $q2->where('branch_id', $branchId)))
            ->groupBy('menu_item_id')
            ->orderByDesc('total')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $menuItem = $item->menuItem;
                return [
                    'name' => $menuItem?->name ?? 'Deleted Item',
                    'total' => $item->total,
                ];
            });

        $todayStats = [
            'completed' => KitchenOrder::whereIn('status', ['delivered'])
                ->whereDate('created_at', today())
                ->when($branchId, fn($q) => $q->whereHas('order', fn($q2) => $q2->where('branch_id', $branchId)))
                ->count(),
            'pending' => KitchenOrder::where('status', 'pending')
                ->when($branchId, fn($q) => $q->whereHas('order', fn($q2) => $q2->where('branch_id', $branchId)))
                ->count(),
        ];

        return $this->success([
            'avg_prep_time_minutes' => round($avgPrepTime, 1),
            'delayed_count' => $delayedCount,
            'most_ordered' => $mostOrdered,
            'today' => $todayStats,
        ]);
    }
}
