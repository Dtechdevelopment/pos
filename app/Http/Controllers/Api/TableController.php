<?php

namespace App\Http\Controllers\Api;

use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = RestaurantTable::with('branch');

        $branchId = $request->user()->branch_id;
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tables = $query->orderBy('table_number')->get();

        $statusCounts = $tables->groupBy('status')->map(fn($t) => $t->count());

        return $this->success([
            'tables' => $tables,
            'summary' => [
                'total' => $tables->count(),
                'available' => $statusCounts['available'] ?? 0,
                'occupied' => $statusCounts['occupied'] ?? 0,
                'reserved' => $statusCounts['reserved'] ?? 0,
                'maintenance' => $statusCounts['maintenance'] ?? 0,
            ],
        ]);
    }

    public function show(RestaurantTable $table): JsonResponse
    {
        $table->load('branch');

        $table->loadCount(['orders as active_orders_count' => fn($q) => $q->whereIn('status', ['pending', 'sent_to_kitchen', 'preparing', 'ready'])]);

        return $this->success($table);
    }
}
