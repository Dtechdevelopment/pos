<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['invoice', 'branch', 'cashier']);

        $branchId = $request->user()->branch_id;
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
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
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,m_pesa,card,bank_transfer',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        if ($invoice->status === 'paid') {
            return $this->error('This invoice is already fully paid.', 422);
        }

        if ($invoice->status === 'void' || $invoice->status === 'cancelled') {
            return $this->error('Cannot accept payment for a void/cancelled invoice.', 422);
        }

        $remaining = $invoice->total - $invoice->paid_amount;

        if ($validated['amount'] > $remaining) {
            return $this->error('Payment amount exceeds remaining balance of ' . number_format($remaining, 2), 422);
        }

        DB::beginTransaction();

        try {
            $paidAmount = $invoice->paid_amount + $validated['amount'];
            $change = max(0, $paidAmount - $invoice->total);

            $payment = new Payment();
            $payment->invoice_id = $invoice->id;
            $payment->branch_id = $invoice->branch_id;
            $payment->cashier_id = $request->user()->id;
            $payment->amount = $validated['amount'];
            $payment->payment_method = $validated['payment_method'];
            $payment->reference_number = $validated['reference_number'] ?? null;
            $payment->status = 'completed';
            $payment->paid_at = now();
            $payment->notes = $validated['notes'] ?? null;
            $payment->save();

            $invoice->paid_amount = $paidAmount;
            $invoice->change_amount = $change;
            $invoice->cashier_id = $request->user()->id;
            $invoice->status = $paidAmount >= $invoice->total ? 'paid' : 'partial';
            $invoice->save();

            // Close the order when fully paid
            if ($paidAmount >= $invoice->total) {
                $order = $invoice->order;
                $order->status = 'closed';
                $order->save();
            }

            $payment->load(['invoice', 'cashier']);

            DB::commit();

            return $this->success($payment, 'Payment recorded successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to record payment: ' . $e->getMessage(), 500);
        }
    }

    public function verify(Payment $payment): JsonResponse
    {
        if ($payment->status !== 'completed') {
            return $this->error('Only completed payments can be verified.', 422);
        }

        $payment->status = 'verified';
        $payment->save();
        $payment->invoice->status = 'paid';
        $payment->invoice->save();

        return $this->success($payment->fresh(), 'Payment verified successfully');
    }

    public function reverse(Payment $payment): JsonResponse
    {
        if (!in_array($payment->status, ['completed', 'verified'])) {
            return $this->error('Payment cannot be reversed.', 422);
        }

        DB::beginTransaction();

        try {
            $payment->status = 'reversed';
            $payment->save();

            $invoice = $payment->invoice;
            $newPaid = $invoice->paid_amount - $payment->amount;
            $invoice->paid_amount = max(0, $newPaid);
            $invoice->status = $newPaid <= 0 ? 'pending' : 'partial';
            $invoice->save();

            DB::commit();

            return $this->success($payment->fresh(), 'Payment reversed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to reverse payment: ' . $e->getMessage(), 500);
        }
    }

    public function refund(Payment $payment): JsonResponse
    {
        if ($payment->status !== 'completed' && $payment->status !== 'verified') {
            return $this->error('Payment cannot be refunded.', 422);
        }

        $payment->status = 'refunded';
        $payment->save();
        $payment->invoice->status = 'refunded';
        $payment->invoice->save();

        return $this->success($payment->fresh(), 'Payment refunded successfully');
    }

    public function storeCombined(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,m_pesa,card,bank_transfer',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $invoices = Invoice::whereIn('id', $validated['invoice_ids'])
            ->whereNotIn('status', ['paid', 'void', 'cancelled'])
            ->get();

        if ($invoices->isEmpty()) {
            return $this->error('No valid unpaid invoices found.', 422);
        }

        $totalRemaining = $invoices->sum(fn($inv) => $inv->total - $inv->paid_amount);

        if ($validated['amount'] > $totalRemaining + 0.01) {
            return $this->error('Payment amount exceeds total remaining balance of ' . number_format($totalRemaining, 2), 422);
        }

        DB::beginTransaction();

        try {
            $remainingPayment = $validated['amount'];

            foreach ($invoices as $invoice) {
                if ($remainingPayment <= 0) break;

                $invRemaining = $invoice->total - $invoice->paid_amount;
                $payAmount = min($remainingPayment, $invRemaining);

                $payment = new Payment();
                $payment->invoice_id = $invoice->id;
                $payment->branch_id = $invoice->branch_id;
                $payment->cashier_id = $request->user()->id;
                $payment->amount = $payAmount;
                $payment->payment_method = $validated['payment_method'];
                $payment->reference_number = $validated['reference_number'] ?? null;
                $payment->status = 'completed';
                $payment->paid_at = now();
                $payment->notes = $validated['notes'] ?? null;
                $payment->save();

                $newPaid = $invoice->paid_amount + $payAmount;
                $change = max(0, $newPaid - $invoice->total);

                $invoice->paid_amount = $newPaid;
                $invoice->change_amount = $change;
                $invoice->cashier_id = $request->user()->id;
                $invoice->status = $newPaid >= $invoice->total ? 'paid' : 'partial';
                $invoice->save();

                if ($newPaid >= $invoice->total) {
                    $order = $invoice->order;
                    $order->status = 'closed';
                    $order->save();
                }

                $remainingPayment -= $payAmount;
            }

            DB::commit();

            return $this->success(['message' => 'Payment recorded successfully'], 'Payment recorded successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to record payment: ' . $e->getMessage(), 500);
        }
    }

    public function dashboard(Request $request): JsonResponse
    {
        $branchId = $request->user()->branch_id;

        $query = Payment::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $today = today();

        $totals = [
            'today' => (clone $query)->whereDate('created_at', $today)->where('status', 'completed')->sum('amount'),
            'yesterday' => (clone $query)->whereDate('created_at', $today->copy()->subDay())->where('status', 'completed')->sum('amount'),
            'this_week' => (clone $query)->whereBetween('created_at', [now()->copy()->startOfWeek(), now()->copy()->endOfWeek()])->where('status', 'completed')->sum('amount'),
            'this_month' => (clone $query)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where('status', 'completed')->sum('amount'),
            'total' => (clone $query)->where('status', 'completed')->sum('amount'),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'refunded' => (clone $query)->where('status', 'refunded')->sum('amount'),
        ];

        $methodTotals = (clone $query)->where('status', 'completed')
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        return $this->success([
            'totals' => $totals,
            'methods' => $methodTotals,
        ]);
    }
}
