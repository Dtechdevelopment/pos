<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['branch_id', 'reconciliation_date', 'kitchen_quantity', 'kitchen_amount', 'sales_quantity', 'sales_amount', 'paid_quantity', 'paid_amount', 'missing_items', 'missing_sales', 'pending_payments', 'notes', 'created_by'])]
class ReconciliationLog extends Model
{
    protected function casts(): array
    {
        return [
            'reconciliation_date' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
