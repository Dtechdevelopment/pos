<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\KitchenOrder;
use App\Models\MenuItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['branch', 'restaurantTable', 'waiter', 'customer'])
            ->withCount('orderItems');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        } elseif ($request->user()->branch_id) {
            $query->where('branch_id', $request->user()->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('waiter_id')) {
            $query->where('waiter_id', $request->waiter_id);
        }

        if ($request->filled('order_no')) {
            $query->where('order_number', 'like', '%' . $request->order_no . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $query->latest();

        return $this->paginated($query);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_table_id' => 'required|exists:restaurant_tables,id',
            'guest_count' => 'required|integer|min:1',
            'customer_id' => 'nullable|exists:customers,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        $table = \App\Models\RestaurantTable::findOrFail($validated['restaurant_table_id']);
        $branchId = $table->branch_id ?? $request->user()->branch_id;

        $activeStatuses = ['pending', 'sent_to_kitchen', 'preparing', 'ready', 'picked_up', 'delivered'];
        $activeGuests = $table->orders()->whereIn('status', $activeStatuses)->sum('guest_count');
        $remainingSeats = $table->capacity - $activeGuests;

        if ($validated['guest_count'] > $remainingSeats) {
            return $this->error("Not enough seats. Table {$table->table_number} has {$remainingSeats} seats remaining (capacity {$table->capacity}, {$activeGuests} already in use).", 422);
        }

        DB::beginTransaction();

        try {
            $orderNumber = 'ORD-' . strtoupper(Str::random(8));

            $subtotal = 0;
            $tax = 0;

            foreach ($validated['items'] as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                $itemSubtotal = $menuItem->selling_price * $item['quantity'];
                $itemTax = $itemSubtotal * ($menuItem->tax / 100);
                $subtotal += $itemSubtotal;
                $tax += $itemTax;
            }

            $total = $subtotal + $tax;

            $order = new Order();
            $order->order_number = $orderNumber;
            $order->branch_id = $branchId;
            $order->restaurant_table_id = $validated['restaurant_table_id'];
            $order->guest_count = $validated['guest_count'];
            $order->waiter_id = $request->user()->id;
            $order->customer_id = $validated['customer_id'] ?? null;
            $order->subtotal = $subtotal;
            $order->tax = $tax;
            $order->discount = 0;
            $order->total = $total;
            $order->status = 'pending';
            $order->notes = $validated['notes'] ?? null;
            $order->save();

            foreach ($validated['items'] as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                $itemSubtotal = $menuItem->selling_price * $item['quantity'];

                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->menu_item_id = $item['menu_item_id'];
                $orderItem->item_name = $menuItem->name;
                $orderItem->quantity = $item['quantity'];
                $orderItem->unit_price = $menuItem->selling_price;
                $orderItem->subtotal = $itemSubtotal;
                $orderItem->notes = $item['notes'] ?? null;
                $orderItem->save();

                $kitchenOrder = new KitchenOrder();
                $kitchenOrder->order_id = $order->id;
                $kitchenOrder->menu_item_id = $item['menu_item_id'];
                $kitchenOrder->item_name = $menuItem->name;
                $kitchenOrder->quantity = $item['quantity'];
                $kitchenOrder->status = 'pending';
                $kitchenOrder->notes = $item['notes'] ?? null;
                $kitchenOrder->save();
            }

            $order->load(['restaurantTable', 'waiter', 'orderItems.menuItem']);

            DB::commit();

            return $this->success($order, 'Order created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to create order: ' . $e->getMessage(), 500);
        }
    }

    public function addItems(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        if (in_array($order->status, ['closed', 'cancelled'])) {
            return $this->error('Cannot add items to a closed or cancelled order.', 422);
        }

        DB::beginTransaction();

        try {
            foreach ($validated['items'] as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                $itemSubtotal = $menuItem->selling_price * $item['quantity'];

                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->menu_item_id = $item['menu_item_id'];
                $orderItem->item_name = $menuItem->name;
                $orderItem->quantity = $item['quantity'];
                $orderItem->unit_price = $menuItem->selling_price;
                $orderItem->subtotal = $itemSubtotal;
                $orderItem->notes = $item['notes'] ?? null;
                $orderItem->save();

                $kitchenOrder = new KitchenOrder();
                $kitchenOrder->order_id = $order->id;
                $kitchenOrder->menu_item_id = $item['menu_item_id'];
                $kitchenOrder->item_name = $menuItem->name;
                $kitchenOrder->quantity = $item['quantity'];
                $kitchenOrder->status = 'pending';
                $kitchenOrder->notes = $item['notes'] ?? null;
                $kitchenOrder->is_addon = true;
                $kitchenOrder->save();
            }

            $newSubtotal = $order->orderItems()->sum('subtotal');
            $newTax = 0;
            $order->orderItems()->with('menuItem')->get()->each(function ($item) use (&$newTax) {
                $newTax += $item->subtotal * ($item->menuItem->tax / 100);
            });

            $order->subtotal = $newSubtotal;
            $order->tax = $newTax;
            $order->total = $newSubtotal + $newTax - $order->discount;
            $order->status = 'sent_to_kitchen';
            $order->save();

            $order->load(['restaurantTable', 'waiter', 'orderItems.menuItem', 'kitchenOrders']);

            DB::commit();

            return $this->success($order, 'Items added to order successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to add items: ' . $e->getMessage(), 500);
        }
    }

    public function show(Order $order): JsonResponse
    {
        $order->load([
            'branch', 'restaurantTable', 'waiter', 'customer',
            'orderItems.menuItem', 'kitchenOrders.chef', 'invoice'
        ]);

        return $this->success($order);
    }

    public function sendToKitchen(Request $request, Order $order): JsonResponse
    {
        try {
            if ($order->status !== 'pending') {
                return $this->error('Only pending orders can be sent to kitchen.', 422);
            }

            $order->status = 'sent_to_kitchen';
            $order->save();

            return $this->success(null, 'Order sent to kitchen');
        } catch (\Exception $e) {
            return $this->error('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,sent_to_kitchen,preparing,ready,picked_up,delivered,closed,cancelled',
        ]);

        $order->status = $validated['status'];
        $order->save();

        return $this->success(null, 'Order status updated');
    }

    public function cancel(Order $order): JsonResponse
    {
        if (in_array($order->status, ['delivered', 'closed'])) {
            return $this->error('Cannot cancel a delivered or closed order.', 422);
        }

        $order->status = 'cancelled';
        $order->save();

        return $this->success(null, 'Order cancelled');
    }

    public function summary(Request $request): JsonResponse
    {
        $branchId = $request->user()->branch_id;

        $today = now()->today();

        $query = Order::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $statusCounts = (clone $query)->selectRaw('status, count(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        $summary = [
            'today' => (clone $query)->whereDate('created_at', $today)->count(),
            'pending' => $statusCounts['pending'] ?? 0,
            'preparing' => ($statusCounts['sent_to_kitchen'] ?? 0) + ($statusCounts['preparing'] ?? 0),
            'ready' => $statusCounts['ready'] ?? 0,
            'delivered' => $statusCounts['delivered'] ?? 0,
            'revenue' => (clone $query)->whereIn('status', ['delivered', 'closed'])->sum('total'),
        ];

        return $this->success($summary);
    }
}
