<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with(['branch', 'customer', 'order', 'order.restaurantTable', 'waiter', 'cashier', 'payments']);

        $branchId = $request->user()->branch_id;
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('waiter_id')) {
            $query->where('waiter_id', $request->waiter_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                  ->orWhereHas('order', function ($q2) use ($search) {
                      $q2->where('order_number', 'like', '%' . $search . '%')
                         ->orWhere('customer_name', 'like', '%' . $search . '%')
                         ->orWhereHas('restaurantTable', function ($q3) use ($search) {
                             $q3->where('table_number', 'like', '%' . $search . '%');
                         });
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'amount_high':
                $query->orderByDesc('total');
                break;
            case 'amount_low':
                $query->orderBy('total');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        return $this->paginated($query);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load([
            'branch', 'waiter', 'cashier', 'customer',
            'order.orderItems.menuItem', 'invoiceItems.menuItem', 'payments'
        ]);

        return $this->success($invoice);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'customer_id' => 'nullable|exists:customers,id',
            'discount' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'local_uuid' => 'nullable|string|max:36',
        ]);

        // Idempotency check: if local_uuid provided, check if already synced
        if (!empty($validated['local_uuid'])) {
            $existing = Invoice::where('local_uuid', $validated['local_uuid'])->first();
            if ($existing) {
                $existing->load(['order', 'invoiceItems.menuItem']);
                return $this->success($existing, 'Invoice already exists', 200);
            }
        }

        $order = Order::with(['orderItems.menuItem', 'restaurantTable', 'customer'])->findOrFail($validated['order_id']);

        if ($order->invoice) {
            return $this->error('This order already has an invoice.', 422);
        }

        $branchId = $order->branch_id;

        $tableNumber = $order->restaurantTable ? $this->sanitizeName($order->restaurantTable->table_number) : '0';
        $customerName = 'WALKIN';
        if ($order->customer && $order->customer->name) {
            $customerName = $this->sanitizeName($order->customer->name);
        } elseif (!empty($order->customer_name)) {
            $customerName = $this->sanitizeName($order->customer_name);
        }
        $invoiceNumber = 'INV-T' . $tableNumber . '-' . $customerName . '-' . strtoupper(Str::random(4));

        $subtotal = $order->subtotal;
        $tax = $order->tax;

        $discountPercent = $validated['discount'] ?? 0;
        $discountAmount = ($subtotal * $discountPercent) / 100;
        $total = $subtotal - $discountAmount + $tax;

        $invoice = new Invoice();
        $invoice->invoice_number = $invoiceNumber;
        $invoice->order_id = $order->id;
        $invoice->branch_id = $branchId;
        $invoice->waiter_id = $request->user()->id;
        $invoice->customer_id = $validated['customer_id'] ?? $order->customer_id;
        $invoice->subtotal = $subtotal;
        $invoice->tax = $tax;
        $invoice->discount = $discountAmount;
        $invoice->total = $total;
        $invoice->paid_amount = 0;
        $invoice->change_amount = 0;
        $invoice->status = 'pending';
        $invoice->notes = $validated['notes'] ?? null;
        $invoice->local_uuid = $validated['local_uuid'] ?? null;
        $invoice->save();

        foreach ($order->orderItems as $item) {
            $invoiceItem = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->menu_item_id = $item->menu_item_id;
            $invoiceItem->item_name = $item->item_name;
            $invoiceItem->quantity = $item->quantity;
            $invoiceItem->unit_price = $item->unit_price;
            $invoiceItem->subtotal = $item->subtotal;
            $invoiceItem->tax = $item->subtotal * ($item->menuItem->tax / 100);
            $invoiceItem->save();
        }

        $invoice->load(['order', 'invoiceItems.menuItem']);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'branch_id' => $request->user()->branch_id,
            'action' => 'create_invoice',
            'module' => 'billing',
            'description' => "Created invoice {$invoiceNumber} for order {$order->order_number} (total \${$total})",
            'ip_address' => $request->ip(),
            'old_values' => null,
            'new_values' => ['invoice_id' => $invoice->id, 'invoice_number' => $invoiceNumber, 'order_number' => $order->order_number, 'total' => $total],
        ]);

        return $this->success($invoice, 'Invoice created successfully', 201);
    }

    public function regenerate(Request $request, Invoice $invoice): JsonResponse
    {
        if (in_array($invoice->status, ['paid', 'void', 'refunded'])) {
            return $this->error('Cannot regenerate a ' . ucfirst($invoice->status) . ' invoice.', 422);
        }

        $order = Order::with('orderItems.menuItem')->findOrFail($invoice->order_id);

        DB::beginTransaction();

        try {
            $invoice->invoiceItems()->delete();

            foreach ($order->orderItems as $item) {
                $invoiceItem = new InvoiceItem();
                $invoiceItem->invoice_id = $invoice->id;
                $invoiceItem->menu_item_id = $item->menu_item_id;
                $invoiceItem->item_name = $item->item_name;
                $invoiceItem->quantity = $item->quantity;
                $invoiceItem->unit_price = $item->unit_price;
                $invoiceItem->subtotal = $item->subtotal;
                $invoiceItem->tax = $item->subtotal * ($item->menuItem->tax / 100);
                $invoiceItem->save();
            }

            $invoice->subtotal = $invoice->fresh()->invoiceItems->sum('subtotal');
            $invoice->tax = $invoice->fresh()->invoiceItems->sum('tax');
            $invoice->total = $invoice->subtotal - ($invoice->discount ?? 0) + $invoice->tax;
            $invoice->status = 'pending';
            $invoice->save();

            $invoice->load(['order', 'invoiceItems.menuItem']);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'branch_id' => $request->user()->branch_id,
                'action' => 'regenerate_invoice',
                'module' => 'billing',
                'description' => "Regenerated invoice {$invoice->invoice_number} for order {$order->order_number} (new total \${$invoice->total})",
                'ip_address' => $request->ip(),
                'old_values' => null,
                'new_values' => ['invoice_number' => $invoice->invoice_number, 'total' => $invoice->total],
            ]);

            DB::commit();

            return $this->success($invoice, 'Invoice regenerated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to regenerate invoice: ' . $e->getMessage(), 500);
        }
    }

    public function void(Request $request, Invoice $invoice): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['super_admin', 'admin', 'manager'])) {
            return $this->error('Only managers can void invoices.', 403);
        }

        $invoice->load(['waiter', 'order.waiter']);

        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ]);

        if ($invoice->status === 'void') {
            return $this->error('Invoice is already voided.', 422);
        }

        if ($invoice->status === 'refunded') {
            return $this->error('Invoice is already refunded.', 422);
        }

        if ($invoice->status === 'paid') {
            $paymentCount = $invoice->payments()->whereIn('status', ['completed', 'verified'])->count();
            return $this->error("Invoice is fully paid with {$paymentCount} payment(s). Reverse or refund all payments before voiding.", 422);
        }

        if ($invoice->status === 'partial') {
            $paymentCount = $invoice->payments()->whereIn('status', ['completed', 'verified'])->count();
            return $this->error("Invoice has {$paymentCount} active payment(s). Reverse all payments before voiding.", 422);
        }

        DB::beginTransaction();

        try {
            $previousStatus = $invoice->status;
            $order = $invoice->order;
            $creatorName = $invoice->waiter?->name ?? $order?->waiter?->name ?? 'Unknown';

            $invoice->status = 'void';
            $invoice->save();

            if ($order && $order->status !== 'cancelled') {
                $order->status = 'cancelled';
                $order->save();

                $order->kitchenOrders()
                    ->whereIn('status', ['pending', 'preparing', 'ready'])
                    ->update(['status' => 'cancelled', 'completed_at' => now()]);
            }

            AuditLog::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'action' => 'void_invoice',
                'module' => 'billing',
                'description' => "Voided invoice {$invoice->invoice_number} (created by {$creatorName}): {$validated['reason']}",
                'ip_address' => $request->ip(),
                'old_values' => ['status' => $previousStatus],
                'new_values' => ['status' => 'void', 'reason' => $validated['reason'], 'created_by' => $creatorName],
            ]);

            DB::commit();

            return $this->success(null, 'Invoice voided successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to void invoice: ' . $e->getMessage(), 500);
        }
    }

    public function applyDiscount(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'discount' => 'required|numeric|min:0|max:100',
        ]);

        $discountAmount = ($invoice->subtotal * $validated['discount']) / 100;
        $invoice->discount = $discountAmount;
        $invoice->total = $invoice->subtotal - $discountAmount + $invoice->tax;
        $invoice->save();

        return $this->success($invoice->fresh(), 'Discount applied successfully');
    }

    public function summary(Request $request): JsonResponse
    {
        $branchId = $request->user()->branch_id;

        $query = Invoice::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $summary = [
            'total' => (clone $query)->count(),
            'paid' => (clone $query)->where('status', 'paid')->count(),
            'pending' => (clone $query)->whereIn('status', ['pending', 'draft'])->count(),
            'cancelled' => (clone $query)->whereIn('status', ['cancelled', 'void'])->count(),
            'revenue' => (clone $query)->where('status', 'paid')->sum('total'),
            'outstanding' => (clone $query)->whereIn('status', ['pending', 'draft'])->sum('total'),
        ];

        return $this->success($summary);
    }

    private function sanitizeName(string $name): string
    {
        $first = explode(' ', trim($name))[0] ?? '';
        $clean = preg_replace('/[^a-zA-Z0-9]/', '', $first);
        return strtoupper(substr($clean, 0, 20)) ?: 'UNKNOWN';
    }
}
