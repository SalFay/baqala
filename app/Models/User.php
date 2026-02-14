<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Config;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'phone',
        'role_id', 'status'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = ['role'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_user')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function hasPermissionTo($ability): bool
    {
        if ($this->isRootUser()) {
            return true;
        }
        $permissions = $this->role?->permissions ?? [];
        return in_array($ability, $permissions) || in_array('*', $permissions);
    }

    public function isRootUser(): bool
    {
        $rootUsers = Config::get('app.root_users', []);
        return in_array($this->email, $rootUsers);
    }

    /**
     * Get the redirect URL after authentication based on user role.
     */
    public function redirection(): string
    {
        // Redirect based on role
        $role = $this->role?->name ?? $this->role ?? 'cashier';

        return match ($role) {
            'admin', 'manager' => '/dashboard',
            'cashier' => '/pos',
            'inventory' => '/inventory',
            default => '/dashboard',
        };
    }
}
