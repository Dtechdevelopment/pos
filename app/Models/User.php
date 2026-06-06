<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone', 'status', 'last_login_at', 'last_login_ip', 'branch_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function waiterOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'waiter_id');
    }

    public function waiterInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'waiter_id');
    }

    public function cashierInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'cashier_id');
    }

    public function cashierPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'cashier_id');
    }

    public function chefOrders(): HasMany
    {
        return $this->hasMany(KitchenOrder::class, 'chef_id');
    }

    public function systemNotifications(): BelongsToMany
    {
        return $this->belongsToMany(SystemNotification::class)
            ->withPivot('is_read', 'read_at')
            ->withTimestamps();
    }
}
