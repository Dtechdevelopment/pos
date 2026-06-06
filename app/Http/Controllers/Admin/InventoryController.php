<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::with('branch');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('stock_status')) {
            match($request->stock_status) {
                'out'  => $query->where('remaining_stock', '<=', 0),
                'low'  => $query->whereColumn('remaining_stock', '<=', 'reorder_level')->where('remaining_stock', '>', 0),
                'ok'   => $query->whereColumn('remaining_stock', '>', 'reorder_level'),
                default => null,
            };
        }

        $inventoryItems = $query->latest()->paginate(20)->withQueryString();

        $summary = [
            'total'    => InventoryItem::count(),
            'in_stock' => InventoryItem::whereColumn('remaining_stock', '>', 'reorder_level')->count(),
            'low'      => InventoryItem::whereColumn('remaining_stock', '<=', 'reorder_level')->where('remaining_stock', '>', 0)->count(),
            'out'      => InventoryItem::where('remaining_stock', '<=', 0)->count(),
            'value'    => InventoryItem::selectRaw('SUM(remaining_stock * cost_price) as total')->value('total') ?? 0,
        ];

        $branches = Branch::where('status', 'active')->get(['id', 'name']);

        return view('admin.inventory.index', compact('inventoryItems', 'summary', 'branches'));
    }

    public function create()
    {
        $branches = Branch::where('status', 'active')->get();
        return view('admin.inventory.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:inventory_items,sku',
            'branch_id' => 'nullable|exists:branches,id',
            'unit' => 'required|string|max:20',
            'opening_stock' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        $validated['remaining_stock'] = $validated['opening_stock'];

        $item = InventoryItem::create($validated);

        StockMovement::create([
            'inventory_item_id' => $item->id,
            'user_id' => auth()->id(),
            'type' => 'in',
            'quantity' => $validated['opening_stock'],
            'notes' => 'Opening stock',
        ]);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory item created successfully.');
    }

    public function stockOut(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $item = InventoryItem::findOrFail($validated['inventory_item_id']);
        $item->decrement('remaining_stock', $validated['quantity']);

        StockMovement::create([
            'inventory_item_id' => $item->id,
            'user_id' => auth()->id(),
            'type' => 'out',
            'quantity' => $validated['quantity'],
            'notes' => $validated['notes'] ?? 'Stock out',
        ]);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Stock out recorded successfully.');
    }

    public function alerts()
    {
        $lowStock = InventoryItem::whereColumn('remaining_stock', '<=', 'reorder_level')->get();
        return view('admin.inventory.alerts', compact('lowStock'));
    }

    public function reports()
    {
        $items = InventoryItem::with('stockMovements')->paginate(15);
        return view('admin.inventory.reports', compact('items'));
    }

    public function wasteReport()
    {
        $waste = StockMovement::where('type', 'waste')
            ->with(['inventoryItem', 'user'])
            ->latest()
            ->paginate(15);
        return view('admin.inventory.waste', compact('waste'));
    }
}
