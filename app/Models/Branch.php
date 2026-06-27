<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = ['name', 'logo_path', 'address', 'phone', 'email', 'manager_name', 'status', 'order_method'];

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) return null;
        $path = $this->logo_path;
        if (str_starts_with($path, 'branches/')) {
            $path = substr($path, 9);
        }
        return url('branches/' . $path);
    }
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function restaurantTables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function reconciliationLogs(): HasMany
    {
        return $this->hasMany(ReconciliationLog::class);
    }
}
