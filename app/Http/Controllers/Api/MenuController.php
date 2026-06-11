<?php

namespace App\Http\Controllers\Api;

use App\Models\MenuItem;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = MenuItem::with(['category', 'branch']);

        $branchId = $request->user()->branch_id;
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->boolean('active_only', false)) {
            $query->where('is_active', true)->where('is_available', true);
        }

        $menuItems = $query->orderBy('name')->get();

        return $this->success($menuItems);
    }

    public function show(MenuItem $menuItem): JsonResponse
    {
        $menuItem->load(['category', 'branch']);

        return $this->success($menuItem);
    }

    public function categories(Request $request): JsonResponse
    {
        $query = Category::with(['branch']);

        $branchId = $request->user()->branch_id;
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $categories = $query->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success($categories);
    }
}
