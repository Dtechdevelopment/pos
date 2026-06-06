<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $tables = RestaurantTable::with('branch')->paginate(50);
        $branches = Branch::where('status', 'active')->get(['id', 'name']);
        $statusCounts = RestaurantTable::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');
        return view('admin.tables.index', compact('tables', 'branches', 'statusCounts'));
    }

    public function create()
    {
        $branches = Branch::where('status', 'active')->get();
        return view('admin.tables.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'table_number' => 'required|string|max:10',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,reserved,maintenance',
        ]);

        RestaurantTable::create($validated);

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table created successfully.');
    }

    public function edit(RestaurantTable $table)
    {
        $branches = Branch::where('status', 'active')->get();
        return view('admin.tables.edit', compact('table', 'branches'));
    }

    public function update(Request $request, RestaurantTable $table)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'table_number' => 'required|string|max:10',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,reserved,maintenance',
        ]);

        $table->update($validated);

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table updated successfully.');
    }

    public function destroy(RestaurantTable $table)
    {
        $table->delete();
        return redirect()->route('admin.tables.index')
            ->with('success', 'Table deleted successfully.');
    }

    public function mergeTables(Request $request)
    {
        $validated = $request->validate([
            'source_table_id' => 'required|exists:restaurant_tables,id',
            'target_table_id' => 'required|exists:restaurant_tables,id|different:source_table_id',
        ]);

        $source = RestaurantTable::findOrFail($validated['source_table_id']);
        $target = RestaurantTable::findOrFail($validated['target_table_id']);

        $source->orders()->update(['restaurant_table_id' => $target->id]);
        $source->update(['status' => 'available']);

        return redirect()->route('admin.tables.index')
            ->with('success', 'Tables merged successfully.');
    }

    public function transferTable(Request $request)
    {
        $validated = $request->validate([
            'from_table_id' => 'required|exists:restaurant_tables,id',
            'to_table_id' => 'required|exists:restaurant_tables,id|different:from_table_id',
        ]);

        $from = RestaurantTable::findOrFail($validated['from_table_id']);
        $to = RestaurantTable::findOrFail($validated['to_table_id']);

        $from->orders()->update(['restaurant_table_id' => $to->id]);
        $from->update(['status' => 'available']);
        $to->update(['status' => 'occupied']);

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table transferred successfully.');
    }
}
