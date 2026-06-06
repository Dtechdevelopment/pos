<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'sku', 'branch_id', 'unit', 'opening_stock', 'received_stock', 'used_stock', 'remaining_stock', 'reorder_level', 'cost_price', 'expiry_date'])]
class InventoryItem extends Model
{
    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
