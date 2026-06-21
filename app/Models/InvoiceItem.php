<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'menu_item_id', 'item_name', 'quantity', 'unit_price', 'subtotal', 'tax'];
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
