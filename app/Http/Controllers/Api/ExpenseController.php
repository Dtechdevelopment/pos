<?php

namespace App\Http\Controllers\Api;

use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $branchId = $request->user()->branch_id;
        $category = $request->category;
        $frequency = $request->frequency;
        $active = $request->boolean('is_active', true);

        $query = Expense::with('creator:id,name')
            ->where('branch_id', $branchId)
            ->where('is_active', $active);

        if ($category) {
            $query->where('category', $category);
        }
        if ($frequency) {
            $query->where('frequency', $frequency);
        }

        $expenses = $query->orderByDesc('created_at')->get();

        return $this->success(['expenses' => $expenses]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|in:utilities,supplies,maintenance,rent,salaries,other',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:daily,weekly,monthly,one_time',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_recurring' => 'nullable',
        ]);

        $validated['branch_id'] = $request->user()->branch_id;
        $validated['created_by'] = $request->user()->id;
        $validated['is_recurring'] = filter_var($validated['is_recurring'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // Salaries and rent are always monthly
        if (in_array($validated['category'], ['rent', 'salaries'])) {
            $validated['frequency'] = 'monthly';
        }

        $expense = Expense::create($validated);

        return $this->success(['expense' => $expense->load('creator:id,name')], 'Expense created', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $expense = Expense::where('branch_id', $request->user()->branch_id)->findOrFail($id);

        $validated = $request->validate([
            'category' => 'sometimes|in:utilities,supplies,maintenance,rent,salaries,other',
            'description' => 'nullable|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'frequency' => 'sometimes|in:daily,weekly,monthly,one_time',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_recurring' => 'nullable',
            'is_active' => 'nullable',
        ]);

        // Salaries and rent are always monthly
        if (isset($validated['category']) && in_array($validated['category'], ['rent', 'salaries'])) {
            $validated['frequency'] = 'monthly';
        }

        if (isset($validated['is_recurring'])) {
            $validated['is_recurring'] = filter_var($validated['is_recurring'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($validated['is_active'])) {
            $validated['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        $expense->update($validated);

        return $this->success(['expense' => $expense->fresh()->load('creator:id,name')]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $expense = Expense::where('branch_id', $request->user()->branch_id)->findOrFail($id);
        $expense->delete();

        return $this->success(null, 'Expense deleted');
    }

    public function summary(Request $request): JsonResponse
    {
        $branchId = $request->user()->branch_id;
        $dateFrom = $request->date_from ?? today()->startOfMonth();
        $dateTo = $request->date_to ?? today();

        $expenses = Expense::where('branch_id', $branchId)
            ->where('is_active', true)
            ->where('start_date', '<=', $dateTo)
            ->where(function ($q) use ($dateTo) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $dateFrom);
            })
            ->get();

        $periodStart = \Carbon\Carbon::parse($dateFrom);
        $periodEnd = \Carbon\Carbon::parse($dateTo);
        $daysInPeriod = $periodStart->diffInDays($periodEnd) + 1;

        $total = 0;
        $byCategory = [];

        foreach ($expenses as $expense) {
            $expenseStart = max($expense->start_date, $periodStart);
            $expenseEnd = $expense->end_date ? min($expense->end_date, $periodEnd) : $periodEnd;

            if ($expenseStart > $expenseEnd) continue;

            $daysActive = $expenseStart->diffInDays($expenseEnd) + 1;

            switch ($expense->frequency) {
                case 'daily':
                    $count = $daysActive;
                    break;
                case 'weekly':
                    $count = $daysActive / 7;
                    break;
                case 'monthly':
                    $count = $expenseStart->diffInMonths($expenseEnd) + ($expenseStart->day <= $expenseEnd->day ? 1 : 0);
                    break;
                case 'one_time':
                    $count = ($expenseStart >= $periodStart && $expenseStart <= $periodEnd) ? 1 : 0;
                    break;
                default:
                    $count = 0;
            }

            $periodAmount = round($expense->amount * $count, 2);
            $total += $periodAmount;

            $byCategory[$expense->category] = ($byCategory[$expense->category] ?? 0) + $periodAmount;
        }

        return $this->success([
            'period' => ['from' => (string) $periodStart->toDateString(), 'to' => (string) $periodEnd->toDateString()],
            'total' => round($total, 2),
            'by_category' => $byCategory,
            'days_in_period' => $daysInPeriod,
        ]);
    }
}
