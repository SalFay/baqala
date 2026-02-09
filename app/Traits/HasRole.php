<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\PermissionSet;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * Trait for users with role-based permissions (SparkCRM pattern)
 */
trait HasRole
{
    /**
     * Cached permissions collection
     */
    protected ?Collection $cachedPermissions = null;

    /**
     * Get the user's role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get permission sets directly assigned to this user
     */
    public function permissionSets(): BelongsToMany
    {
        return $this->belongsToMany(PermissionSet::class, 'permission_set_user')
            ->withTimestamps();
    }

    /**
     * Load all permissions for this user
     * Combines: Role permissions + Role's permission sets + User's direct permission sets
     */
    public function loadRolePermissions(): Collection
    {
        if ($this->cachedPermissions !== null) {
            return $this->cachedPermissions;
        }

        $permissions = collect();

        // 1. Direct role permissions
        if ($this->role) {
            $rolePermissions = $this->role->permissions ?? [];
            $permissions = $permissions->merge($rolePermissions);

            // 2. Role's permission sets
            foreach ($this->role->permissionSets as $permissionSet) {
                $permissions = $permissions->merge($permissionSet->permissions ?? []);
            }
        }

        // 3. User's direct permission sets
        foreach ($this->permissionSets as $permissionSet) {
            $permissions = $permissions->merge($permissionSet->permissions ?? []);
        }

        $this->cachedPermissions = $permissions->unique()->values();

        return $this->cachedPermissions;
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        return $this->loadRolePermissions()->contains($permission);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user is a super user
     */
    public function isSuperUser(): bool
    {
        return in_array($this->email, config('auth.super_users', []));
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin' || $this->isSuperUser();
    }

    /**
     * Get all permission strings for frontend
     */
    public function getAllPermissions(): array
    {
        if ($this->isSuperUser()) {
            return ['*'];
        }

        return $this->loadRolePermissions()->toArray();
    }

    /**
     * Clear cached permissions (call after role/permission changes)
     */
    public function clearPermissionCache(): void
    {
        $this->cachedPermissions = null;
    }

    /**
     * Assign a role to the user
     */
    public function assignRole(Role|int|string $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        } elseif (is_int($role)) {
            $role = Role::findOrFail($role);
        }

        $this->role_id = $role->id;
        $this->save();
        $this->clearPermissionCache();
    }

    /**
     * Sync permission sets for the user
     */
    public function syncPermissionSets(array $permissionSetIds): void
    {
        $this->permissionSets()->sync($permissionSetIds);
        $this->clearPermissionCache();
    }
}
