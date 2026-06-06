<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['branch', 'customer', 'order']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('invoice_no')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_no . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        $summary = [
            'total'     => Invoice::count(),
            'paid'      => Invoice::where('status', 'paid')->count(),
            'pending'   => Invoice::whereIn('status', ['pending', 'draft'])->count(),
            'cancelled' => Invoice::whereIn('status', ['cancelled', 'void'])->count(),
            'revenue'   => Invoice::where('status', 'paid')->sum('total'),
            'outstanding' => Invoice::whereIn('status', ['pending', 'draft'])->sum('total'),
        ];

        return view('admin.billing.index', compact('invoices', 'summary'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['branch', 'waiter', 'cashier', 'customer', 'order.orderItems', 'invoiceItems', 'payments']);
        return view('admin.billing.show', compact('invoice'));
    }

    public function reprint(Invoice $invoice)
    {
        $invoice->load(['branch', 'order.restaurantTable', 'invoiceItems']);
        return view('admin.billing.print', compact('invoice'));
    }

    public function void(Invoice $invoice)
    {
        $invoice->update(['status' => 'void']);
        return redirect()->route('admin.billing.index')
            ->with('success', 'Invoice voided successfully.');
    }

    public function applyDiscount(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'discount' => 'required|numeric|min:0|max:100',
        ]);

        $discountAmount = ($invoice->subtotal * $validated['discount']) / 100;
        $invoice->update([
            'discount' => $discountAmount,
            'total' => $invoice->subtotal - $discountAmount + $invoice->tax,
        ]);

        return redirect()->route('admin.billing.index')
            ->with('success', 'Discount applied successfully.');
    }
}
