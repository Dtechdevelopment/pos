@extends('admin.layouts.app')

@section('title', 'Invoice Detail')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Invoice #{{ $invoice->invoice_number }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.billing.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow-sm p-5">
            <h3 class="font-semibold text-gray-700 mb-4">Invoice Items</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 font-medium text-gray-500">Item</th>
                            <th class="text-center py-3 font-medium text-gray-500">Qty</th>
                            <th class="text-right py-3 font-medium text-gray-500">Price</th>
                            <th class="text-right py-3 font-medium text-gray-500">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->items ?? [] as $item)
                        <tr class="border-b border-gray-100">
                            <td class="py-3">{{ $item->description ?? $item->name }}</td>
                            <td class="py-3 text-center">{{ $item->quantity ?? 1 }}</td>
                            <td class="py-3 text-right">${{ number_format($item->unit_price ?? 0, 2) }}</td>
                            <td class="py-3 text-right">${{ number_format(($item->unit_price ?? 0) * ($item->quantity ?? 1), 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-400">No items</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold">
                            <td colspan="3" class="py-3 text-right">Subtotal</td>
                            <td class="py-3 text-right">${{ number_format($invoice->subtotal ?? 0, 2) }}</td>
                        </tr>
                        @if($invoice->tax > 0)
                        <tr>
                            <td colspan="3" class="py-2 text-right text-gray-500">Tax</td>
                            <td class="py-2 text-right">${{ number_format($invoice->tax ?? 0, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->discount > 0)
                        <tr>
                            <td colspan="3" class="py-2 text-right text-gray-500">Discount</td>
                            <td class="py-2 text-right">-${{ number_format($invoice->discount ?? 0, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="font-bold text-lg">
                            <td colspan="3" class="py-3 text-right">Total</td>
                            <td class="py-3 text-right">${{ number_format($invoice->total_amount ?? 0, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm p-5">
            <h3 class="font-semibold text-gray-700 mb-4">Invoice Details</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Invoice #</dt>
                    <dd class="font-medium">{{ $invoice->invoice_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Order #</dt>
                    <dd>{{ $invoice->order->order_number ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Status</dt>
                    <dd>
                        <span class="px-2 py-1 rounded-full text-xs 
                            @if($invoice->status == 'paid') bg-green-100 text-green-700
                            @elseif($invoice->status == 'unpaid') bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700 @endif">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Amount Paid</dt>
                    <dd class="font-medium text-green-600">${{ number_format($invoice->paid_amount ?? 0, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Balance Due</dt>
                    <dd class="font-medium text-red-600">${{ number_format(($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0), 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Date</dt>
                    <dd>{{ $invoice->created_at->format('Y-m-d H:i') }}</dd>
                </div>
            </dl>
        </div>
        @if($invoice->status != 'paid')
        <div class="bg-white rounded-lg shadow-sm p-5">
            <h3 class="font-semibold text-gray-700 mb-4">Record Payment</h3>
            <form action="{{ route('admin.payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Amount</label>
                        <input type="number" step="0.01" name="amount" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile">Mobile Payment</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                        <i class="fas fa-credit-card mr-2"></i>Process Payment
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection