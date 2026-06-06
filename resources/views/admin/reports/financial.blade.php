@extends('admin.layouts.app')

@section('title', 'Financial Reports')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Financial Reports</h1>
</div>
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('admin.reports.financial') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Date From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Branch</label>
            <select name="branch_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Branches</option>
                @foreach($branches ?? [] as $branch)
                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
            <i class="fas fa-search mr-1"></i>Generate Report
        </button>
    </form>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-green-500">
        <p class="text-sm text-gray-500">Gross Revenue</p>
        <p class="text-2xl font-bold text-gray-800">${{ number_format($grossRevenue ?? 0, 2) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-red-500">
        <p class="text-sm text-gray-500">Expenses</p>
        <p class="text-2xl font-bold text-gray-800">${{ number_format($expenses ?? 0, 2) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-blue-500">
        <p class="text-sm text-gray-500">Net Profit</p>
        <p class="text-2xl font-bold text-gray-800">${{ number_format($netProfit ?? 0, 2) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-purple-500">
        <p class="text-sm text-gray-500">Profit Margin</p>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($profitMargin ?? 0, 1) }}%</p>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow-sm p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Revenue vs Expenses</h3>
        <div class="relative" style="height:300px">
            <canvas id="financialChart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Expense Breakdown</h3>
        <div class="relative" style="height:300px">
            <canvas id="expenseChart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('financialChart'), {
        type: 'bar',
        data: {
            labels: @json($chartData['financial']['labels'] ?? []),
            datasets: [
                {
                    label: 'Revenue',
                    data: @json($chartData['financial']['revenue'] ?? []),
                    backgroundColor: 'rgba(16, 185, 129, 0.5)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1
                },
                {
                    label: 'Expenses',
                    data: @json($chartData['financial']['expenses'] ?? []),
                    backgroundColor: 'rgba(239, 68, 68, 0.5)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1
                }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });

    new Chart(document.getElementById('expenseChart'), {
        type: 'doughnut',
        data: {
            labels: @json($chartData['expenses']['labels'] ?? []),
            datasets: [{
                data: @json($chartData['expenses']['data'] ?? []),
                backgroundColor: ['rgba(239, 68, 68, 0.7)', 'rgba(245, 158, 11, 0.7)', 'rgba(59, 130, 246, 0.7)', 'rgba(139, 92, 246, 0.7)'],
                borderWidth: 1
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>
@endpush