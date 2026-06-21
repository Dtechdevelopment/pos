<?php

namespace App\Http\Controllers\Api;

use App\Models\RestaurantTable;
use App\Models\Invoice;
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

        $activeStatuses = ['pending', 'sent_to_kitchen', 'preparing', 'ready', 'picked_up', 'delivered'];

        $tables->each(function ($table) use ($activeStatuses) {
            $openOrders = $table->orders()
                ->whereIn('status', $activeStatuses)
                ->get();

            $activeGuests = $openOrders->sum('guest_count');
            $remainingSeats = max(0, $table->capacity - $activeGuests);

            $table->active_guests = $activeGuests;
            $table->remaining_seats = $remainingSeats;
            $table->open_orders_count = $openOrders->count();
            $table->open_order_ids = $openOrders->pluck('id')->toArray();

            if (in_array($table->status, ['reserved', 'cleaning'])) {
                $table->computed_status = $table->status;
            } elseif ($openOrders->isEmpty()) {
                $table->computed_status = 'available';
            } elseif ($remainingSeats == 0) {
                $table->computed_status = 'full';
            } else {
                $table->computed_status = 'partial';
            }
        });

        $summary = [
            'total' => $tables->count(),
            'available' => $tables->where('computed_status', 'available')->count(),
            'partial' => $tables->where('computed_status', 'partial')->count(),
            'full' => $tables->where('computed_status', 'full')->count(),
            'reserved' => $tables->where('computed_status', 'reserved')->count(),
            'cleaning' => $tables->where('computed_status', 'cleaning')->count(),
        ];

        return $this->success([
            'tables' => $tables,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:100',
            'status' => 'nullable|string|in:available,reserved,cleaning',
        ]);

        $branchId = $request->user()->branch_id;

        $exists = RestaurantTable::where('branch_id', $branchId)
            ->where('table_number', $validated['table_number'])
            ->exists();

        if ($exists) {
            return $this->error('Table number already exists for this branch', 422);
        }

        $table = RestaurantTable::create([
            'branch_id' => $branchId,
            'table_number' => $validated['table_number'],
            'capacity' => $validated['capacity'],
            'status' => $validated['status'] ?? 'available',
        ]);

        return $this->success($table, 'Table created successfully', 201);
    }

    public function update(Request $request, RestaurantTable $table): JsonResponse
    {
        if ($table->branch_id !== $request->user()->branch_id && !$request->user()->hasRole('super_admin')) {
            return $this->error('Unauthorized', 403);
        }

        $validated = $request->validate([
            'table_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:100',
            'status' => 'nullable|string|in:available,reserved,cleaning',
        ]);

        $exists = RestaurantTable::where('branch_id', $table->branch_id)
            ->where('table_number', $validated['table_number'])
            ->where('id', '!=', $table->id)
            ->exists();

        if ($exists) {
            return $this->error('Table number already exists for this branch', 422);
        }

        $table->update($validated);

        return $this->success($table, 'Table updated successfully');
    }

    public function destroy(Request $request, RestaurantTable $table): JsonResponse
    {
        if ($table->branch_id !== $request->user()->branch_id && !$request->user()->hasRole('super_admin')) {
            return $this->error('Unauthorized', 403);
        }

        $hasOrders = $table->orders()->whereIn('status', ['pending', 'sent_to_kitchen', 'preparing', 'ready'])->exists();
        if ($hasOrders) {
            return $this->error('Cannot delete table with active orders', 422);
        }

        $table->delete();

        return $this->success(null, 'Table deleted successfully');
    }

    public function show(RestaurantTable $table): JsonResponse
    {
        $table->load('branch');

        $activeStatuses = ['pending', 'sent_to_kitchen', 'preparing', 'ready', 'picked_up', 'delivered'];

        $openOrders = $table->orders()
            ->whereIn('status', $activeStatuses)
            ->with(['orderItems', 'invoice'])
            ->latest()
            ->get();

        $activeGuests = $openOrders->sum('guest_count');
        $remainingSeats = max(0, $table->capacity - $activeGuests);

        $table->active_guests = $activeGuests;
        $table->remaining_seats = $remainingSeats;
        $table->open_orders = $openOrders;
        $table->open_orders_count = $openOrders->count();

        if (in_array($table->status, ['reserved', 'cleaning'])) {
            $table->computed_status = $table->status;
        } elseif ($openOrders->isEmpty()) {
            $table->computed_status = 'available';
        } elseif ($remainingSeats == 0) {
            $table->computed_status = 'full';
        } else {
            $table->computed_status = 'partial';
        }

        return $this->success($table);
    }

    public function cashierTables(Request $request): JsonResponse
    {
        $branchId = $request->user()->branch_id;

        $tables = RestaurantTable::with('branch')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('table_number')
            ->get();

        $result = collect();

        $tables->each(function ($table) use ($result) {
            $unpaidInvoices = Invoice::whereHas('order', function ($q) use ($table) {
                $q->where('restaurant_table_id', $table->id);
            })
            ->whereIn('status', ['pending', 'draft', 'partial'])
            ->with(['order', 'order.orderItems.menuItem'])
            ->get();

            if ($unpaidInvoices->isNotEmpty()) {
                $table->unpaid_invoices = $unpaidInvoices;
                $table->unpaid_total = $unpaidInvoices->sum(fn($inv) => $inv->total - $inv->paid_amount);
                $table->unpaid_count = $unpaidInvoices->count();
                $result->push($table);
            }
        });

        return $this->success([
            'tables' => $result->values(),
        ]);
    }
}
