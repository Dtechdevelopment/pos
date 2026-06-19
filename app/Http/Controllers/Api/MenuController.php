<?php

namespace App\Http\Controllers\Api;

use App\Models\MenuItem;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('sku', 'like', '%' . $search . '%');
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0|max:100',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'is_available' => 'nullable|boolean',
        ]);

        $branchId = $request->user()->branch_id;

        $menuItem = new MenuItem();
        $menuItem->name = $validated['name'];
        $menuItem->category_id = $validated['category_id'];
        $menuItem->branch_id = $branchId;
        $menuItem->selling_price = $validated['selling_price'];
        $menuItem->cost_price = $validated['cost_price'] ?? 0;
        $menuItem->tax = $validated['tax'] ?? 0;
        $menuItem->sku = $validated['sku'] ?? null;
        $menuItem->description = $validated['description'] ?? null;
        $menuItem->image = $validated['image'] ?? null;
        $menuItem->is_active = $validated['is_active'] ?? true;
        $menuItem->is_available = $validated['is_available'] ?? true;
        $menuItem->save();

        $menuItem->load(['category', 'branch']);

        return $this->success($menuItem, 'Menu item created successfully', 201);
    }

    public function update(Request $request, MenuItem $menuItem): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0|max:100',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'is_available' => 'nullable|boolean',
        ]);

        if (isset($validated['name'])) $menuItem->name = $validated['name'];
        if (isset($validated['category_id'])) $menuItem->category_id = $validated['category_id'];
        if (isset($validated['selling_price'])) $menuItem->selling_price = $validated['selling_price'];
        if (array_key_exists('cost_price', $validated)) $menuItem->cost_price = $validated['cost_price'];
        if (array_key_exists('tax', $validated)) $menuItem->tax = $validated['tax'];
        if (array_key_exists('sku', $validated)) $menuItem->sku = $validated['sku'];
        if (array_key_exists('description', $validated)) $menuItem->description = $validated['description'];
        if (array_key_exists('image', $validated)) $menuItem->image = $validated['image'];
        if (array_key_exists('is_active', $validated)) $menuItem->is_active = $validated['is_active'];
        if (array_key_exists('is_available', $validated)) $menuItem->is_available = $validated['is_available'];
        $menuItem->save();

        $menuItem->load(['category', 'branch']);

        return $this->success($menuItem, 'Menu item updated successfully');
    }

    public function destroy(MenuItem $menuItem): JsonResponse
    {
        $hasOrders = $menuItem->orderItems()->count() > 0;
        if ($hasOrders) {
            return $this->error('Cannot delete menu item that has been ordered. Deactivate it instead.', 422);
        }

        $menuItem->delete();

        return $this->success(null, 'Menu item deleted successfully');
    }

    public function toggleAvailability(MenuItem $menuItem): JsonResponse
    {
        $menuItem->is_available = !$menuItem->is_available;
        $menuItem->save();

        return $this->success($menuItem, $menuItem->is_available ? 'Item is now available' : 'Item is now unavailable');
    }

    public function toggleActive(MenuItem $menuItem): JsonResponse
    {
        $menuItem->is_active = !$menuItem->is_active;
        $menuItem->save();

        return $this->success($menuItem, $menuItem->is_active ? 'Item is now active' : 'Item is now inactive');
    }

    // --- Categories ---

    public function categories(Request $request): JsonResponse
    {
        $query = Category::with(['branch']);

        $branchId = $request->user()->branch_id;
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $categories = $query->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success($categories);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $branchId = $request->user()->branch_id;

        $category = new Category();
        $category->name = $validated['name'];
        $category->slug = Str::slug($validated['name']);
        $category->branch_id = $branchId;
        $category->description = $validated['description'] ?? null;
        $category->sort_order = $validated['sort_order'] ?? 0;
        $category->is_active = true;
        $category->save();

        return $this->success($category, 'Category created successfully', 201);
    }

    public function updateCategory(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['name'])) {
            $category->name = $validated['name'];
            $category->slug = Str::slug($validated['name']);
        }
        if (array_key_exists('description', $validated)) $category->description = $validated['description'];
        if (array_key_exists('sort_order', $validated)) $category->sort_order = $validated['sort_order'];
        if (array_key_exists('is_active', $validated)) $category->is_active = $validated['is_active'];
        $category->save();

        return $this->success($category, 'Category updated successfully');
    }

    public function destroyCategory(Category $category): JsonResponse
    {
        $hasItems = $category->menuItems()->count() > 0;
        if ($hasItems) {
            return $this->error('Cannot delete category that has menu items. Remove items first.', 422);
        }

        $category->delete();

        return $this->success(null, 'Category deleted successfully');
    }
}
