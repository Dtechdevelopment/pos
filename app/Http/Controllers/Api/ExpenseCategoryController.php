<?php

namespace App\Http\Controllers\Api;

use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExpenseCategoryController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $branchId = $request->user()->branch_id;

        $query = ExpenseCategory::where('branch_id', $branchId)
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->boolean('all')) {
            $categories = $query->get();
        } else {
            $categories = $query->where('is_active', true)->get();
        }

        return $this->success($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $branchId = $request->user()->branch_id;
        $slug = Str::slug($validated['name']);

        $exists = ExpenseCategory::where('branch_id', $branchId)
            ->where('slug', $slug)
            ->exists();

        if ($exists) {
            return $this->error('A category with this name already exists', 422);
        }

        $maxSort = ExpenseCategory::where('branch_id', $branchId)->max('sort_order');

        $category = ExpenseCategory::create([
            'branch_id' => $branchId,
            'name' => $validated['name'],
            'slug' => $slug,
            'icon' => $validated['icon'] ?? 'more_horiz',
            'color' => $validated['color'] ?? '#757575',
            'is_active' => true,
            'sort_order' => ($maxSort ?? 0) + 1,
        ]);

        return $this->success($category, 'Category created', 201);
    }

    public function update(Request $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        $branchId = $request->user()->branch_id;
        if ($expenseCategory->branch_id !== $branchId) {
            return $this->error('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);

            $exists = ExpenseCategory::where('branch_id', $branchId)
                ->where('slug', $validated['slug'])
                ->where('id', '!=', $expenseCategory->id)
                ->exists();

            if ($exists) {
                return $this->error('A category with this name already exists', 422);
            }
        }

        if (isset($validated['is_active'])) {
            $validated['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        $expenseCategory->update($validated);

        return $this->success($expenseCategory->fresh(), 'Category updated');
    }

    public function destroy(Request $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        $branchId = $request->user()->branch_id;
        if ($expenseCategory->branch_id !== $branchId) {
            return $this->error('Unauthorized', 403);
        }

        $expenseCategory->delete();
        return $this->success(null, 'Category deleted');
    }
}
