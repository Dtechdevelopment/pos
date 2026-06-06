<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'branch', 'cashier']);

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

        if ($request->filled('reference')) {
            $query->where('reference_number', 'like', '%' . $request->reference . '%');
        }

        $payments = $query->latest()->paginate(15)->withQueryString();

        $summary = [
            'total'     => Payment::count(),
            'completed' => Payment::where('status', 'completed')->count(),
            'pending'   => Payment::where('status', 'pending')->count(),
            'refunded'  => Payment::where('status', 'refunded')->count(),
            'collected' => Payment::where('status', 'completed')->sum('amount'),
        ];

        return view('admin.payments.index', compact('payments', 'summary'));
    }

    public function verify(Payment $payment)
    {
        $payment->update(['status' => 'verified']);
        $payment->invoice->update(['status' => 'paid']);
        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment verified successfully.');
    }

    public function reverse(Payment $payment)
    {
        $payment->update(['status' => 'reversed']);
        $payment->invoice->update(['status' => 'unpaid']);
        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment reversed successfully.');
    }

    public function refund(Payment $payment)
    {
        $payment->update(['status' => 'refunded']);
        $payment->invoice->update(['status' => 'refunded']);
        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment refunded successfully.');
    }

    public function dashboard()
    {
        $today     = today();
        $now       = now();

        $totals = [
            'today'       => Payment::whereDate('created_at', $today)->where('status', 'completed')->sum('amount'),
            'yesterday'   => Payment::whereDate('created_at', $today->copy()->subDay())->where('status', 'completed')->sum('amount'),
            'thisWeek'    => Payment::whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->where('status', 'completed')->sum('amount'),
            'thisMonth'   => Payment::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->where('status', 'completed')->sum('amount'),
            'outstanding' => Invoice::whereIn('status', ['pending', 'draft'])->sum('total'),
            'total'       => Payment::where('status', 'completed')->sum('amount'),
            'pending'     => Payment::where('status', 'pending')->count(),
            'refunded'    => Payment::where('status', 'refunded')->sum('amount'),
        ];

        // Payment method breakdown
        $methodTotals = Payment::where('status', 'completed')
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $chartData = [];

        // Doughnut: payment methods
        $chartData['paymentMethods'] = [
            'labels' => $methodTotals->keys()->map(fn($k) => ucwords(str_replace('_', ' ', $k)))->values(),
            'data'   => $methodTotals->pluck('total')->values(),
            'counts' => $methodTotals->pluck('count')->values(),
        ];

        // Bar: daily collections last 7 days
        $daily = collect(range(6, 0))->map(fn($i) => [
            'date'  => $today->copy()->subDays($i)->format('D'),
            'total' => Payment::whereDate('created_at', $today->copy()->subDays($i))->where('status', 'completed')->sum('amount'),
        ]);
        $chartData['dailyCollections'] = [
            'labels' => $daily->pluck('date'),
            'data'   => $daily->pluck('total'),
        ];

        // Line: monthly trend last 6 months
        $monthly = collect(range(5, 0))->map(fn($i) => [
            'month' => $now->copy()->subMonths($i)->format('M Y'),
            'total' => Payment::whereMonth('created_at', $now->copy()->subMonths($i)->month)
                ->whereYear('created_at', $now->copy()->subMonths($i)->year)
                ->where('status', 'completed')->sum('amount'),
        ]);
        $chartData['monthlyTrend'] = [
            'labels' => $monthly->pluck('month'),
            'data'   => $monthly->pluck('total'),
        ];

        // Recent payments
        $recentPayments = Payment::with(['invoice', 'cashier', 'branch'])
            ->latest()->limit(8)->get();

        return view('admin.payments.dashboard', compact(
            'totals', 'chartData', 'methodTotals', 'recentPayments'
        ));
    }
}
