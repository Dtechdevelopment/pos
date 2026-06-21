<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\KitchenOrder;
use App\Models\MenuItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\AuditLog;
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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhereHas('restaurantTable', function ($q2) use ($search) {
                      $q2->where('table_number', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('waiter', function ($q3) use ($search) {
                      $q3->where('name', 'like', '%' . $search . '%');
                  });
            });
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

            $order->load(['restaurantTable', 'waiter', 'customer', 'orderItems.menuItem']);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'create_order',
                'module' => 'orders',
                'description' => "Created order {$orderNumber} for table {$table->table_number} ({$validated['guest_count']} guests, \${$total})",
                'ip_address' => $request->ip(),
                'old_values' => null,
                'new_values' => ['order_id' => $order->id, 'order_number' => $orderNumber, 'table' => $table->table_number, 'total' => $total, 'items' => count($validated['items'])],
            ]);

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

        $invoice = $order->invoice;
        if ($invoice && $invoice->status === 'paid' && $order->status === 'closed') {
            return $this->error('Cannot add items — order is closed and paid.', 422);
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
            $branch = $order->branch;
            $isManual = ($branch && $branch->order_method === 'manual');
            $order->status = $isManual ? 'ready' : 'sent_to_kitchen';
            $order->save();

            if ($isManual) {
                $order->kitchenOrders()->where('status', 'pending')->update(['status' => 'ready']);
            }

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
            'orderItems.menuItem', 'kitchenOrders.chef', 'invoice.invoiceItems.menuItem'
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

        $newStatus = $validated['status'];
        $oldStatus = $order->status;
        $order->status = $newStatus;
        $order->save();

        if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
            $this->syncInvoiceOnDelivery($order);
        }

        return $this->success(null, 'Order status updated');
    }

    private function syncInvoiceOnDelivery(Order $order): void
    {
        $invoice = $order->invoice;
        if (!$invoice || $invoice->status === 'paid') {
            return;
        }

        $order->load('orderItems.menuItem');

        $orderQuantities = $order->orderItems->groupBy('menu_item_id')
            ->map(fn($items) => [
                'quantity' => $items->sum('quantity'),
                'item' => $items->first(),
            ]);

        $invoiceQuantities = $invoice->invoiceItems->groupBy('menu_item_id')
            ->map(fn($items) => $items->sum('quantity'));

        $hasNewItems = false;
        foreach ($orderQuantities as $menuItemId => $data) {
            $invoicedQty = $invoiceQuantities[$menuItemId] ?? 0;
            if ($data['quantity'] > $invoicedQty) {
                $diff = $data['quantity'] - $invoicedQty;
                $item = $data['item'];
                $diffSubtotal = $item->unit_price * $diff;
                $menuItem = $item->menuItem;

                $invoiceItem = new InvoiceItem();
                $invoiceItem->invoice_id = $invoice->id;
                $invoiceItem->menu_item_id = $menuItemId;
                $invoiceItem->item_name = $item->item_name;
                $invoiceItem->quantity = $diff;
                $invoiceItem->unit_price = $item->unit_price;
                $invoiceItem->subtotal = $diffSubtotal;
                $invoiceItem->tax = $diffSubtotal * ($menuItem->tax / 100);
                $invoiceItem->save();
                $hasNewItems = true;
            }
        }

        if ($hasNewItems) {
            $invoice->load('invoiceItems');
            $invoice->subtotal = $invoice->invoiceItems->sum('subtotal');
            $invoice->tax = $invoice->invoiceItems->sum('tax');
            $invoice->total = $invoice->subtotal - ($invoice->discount ?? 0) + $invoice->tax;
            $invoice->save();
        }
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'manager'])) {
            return $this->error('Only managers can cancel orders.', 403);
        }

        $order->load('waiter');

        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ]);

        if ($order->status === 'cancelled') {
            return $this->error('Order is already cancelled.', 422);
        }

        if ($order->status === 'closed') {
            $invoice = $order->invoice;
            if ($invoice && $invoice->status === 'paid') {
                return $this->error('Cannot cancel a paid and closed order. Void the invoice and reverse payments first.', 422);
            }
        }

        $invoice = $order->invoice;

        if ($invoice && in_array($invoice->status, ['partial', 'paid'])) {
            $activePayments = $invoice->payments()->whereIn('status', ['completed', 'verified'])->count();
            if ($activePayments > 0) {
                return $this->error("Invoice has {$activePayments} active payment(s). Reverse all payments before cancelling this order.", 422);
            }
        }

        DB::beginTransaction();

        try {
            $previousStatus = $order->status;
            $creatorName = $order->waiter?->name ?? 'Unknown';

            $order->status = 'cancelled';
            $order->save();

            $order->kitchenOrders()
                ->whereIn('status', ['pending', 'preparing', 'ready'])
                ->update(['status' => 'cancelled', 'completed_at' => now()]);

            if ($invoice && $invoice->status === 'pending') {
                $invoice->status = 'void';
                $invoice->save();
            }

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'cancel_order',
                'module' => 'orders',
                'description' => "Cancelled order {$order->order_number} (created by {$creatorName}): {$validated['reason']}",
                'ip_address' => $request->ip(),
                'old_values' => ['status' => $previousStatus],
                'new_values' => ['status' => 'cancelled', 'reason' => $validated['reason'], 'invoice_voided' => $invoice && $invoice->status === 'void', 'created_by' => $creatorName],
            ]);

            DB::commit();

            return $this->success(null, 'Order cancelled successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to cancel order: ' . $e->getMessage(), 500);
        }
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
