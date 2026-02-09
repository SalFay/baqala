<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\PermissionSet;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    use ApiResponse;

    /**
     * List all roles
     */
    public function index(Request $request): JsonResponse
    {
        $query = Role::withCount('users');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->orderBy('sort_order')->get();

        return $this->success($roles);
    }

    /**
     * Create a new role
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'color' => 'nullable|string|max:20',
            'permission_set_ids' => 'nullable|array',
            'permission_set_ids.*' => 'exists:permission_sets,id',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['permissions'] = $validated['permissions'] ?? [];
        $validated['created_by_id'] = auth()->id();

        $role = Role::create($validated);

        // Attach permission sets if provided
        if (!empty($validated['permission_set_ids'])) {
            $role->permissionSets()->sync($validated['permission_set_ids']);
        }

        return $this->created($role->load('permissionSets'), 'Role created successfully');
    }

    /**
     * Show role details
     */
    public function show(Role $role): JsonResponse
    {
        $role->load('permissionSets');
        $role->loadCount('users');

        return $this->success($role);
    }

    /**
     * Update role
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'color' => 'nullable|string|max:20',
            'permission_set_ids' => 'nullable|array',
            'permission_set_ids.*' => 'exists:permission_sets,id',
        ]);

        if (isset($validated['name']) && !isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $role->update($validated);

        // Sync permission sets if provided
        if (isset($validated['permission_set_ids'])) {
            $role->permissionSets()->sync($validated['permission_set_ids']);
        }

        return $this->success($role->load('permissionSets'), 'Role updated successfully');
    }

    /**
     * Delete role
     */
    public function destroy(Role $role): JsonResponse
    {
        // Prevent deleting roles with users
        if ($role->users()->exists()) {
            return $this->error('Cannot delete role with assigned users', 422);
        }

        // Prevent deleting system roles
        if (in_array($role->slug, ['admin', 'manager', 'cashier'])) {
            return $this->error('Cannot delete system roles', 422);
        }

        $role->permissionSets()->detach();
        $role->delete();

        return $this->success(null, 'Role deleted successfully');
    }

    /**
     * Get all available permissions
     */
    public function permissions(): JsonResponse
    {
        $permissions = Role::allPermissions();

        return $this->success($permissions);
    }

    /**
     * Duplicate a role
     */
    public function duplicate(Role $role): JsonResponse
    {
        $newRole = $role->replicate();
        $newRole->name = $role->name . ' (Copy)';
        $newRole->slug = Str::slug($newRole->name);
        $newRole->created_by_id = auth()->id();
        $newRole->save();

        // Copy permission sets
        $newRole->permissionSets()->sync($role->permissionSets->pluck('id'));

        return $this->created($newRole->load('permissionSets'), 'Role duplicated successfully');
    }

    /**
     * List permission sets
     */
    public function permissionSets(): JsonResponse
    {
        $sets = PermissionSet::orderBy('name')->get();

        return $this->success($sets);
    }

    /**
     * Create permission set
     */
    public function storePermissionSet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:permission_sets,slug',
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['created_by_id'] = auth()->id();

        $set = PermissionSet::create($validated);

        return $this->created($set, 'Permission set created successfully');
    }

    /**
     * Update permission set
     */
    public function updatePermissionSet(Request $request, PermissionSet $permissionSet): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:permission_sets,slug,' . $permissionSet->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'sometimes|required|array|min:1',
            'permissions.*' => 'string',
        ]);

        $permissionSet->update($validated);

        return $this->success($permissionSet, 'Permission set updated successfully');
    }

    /**
     * Delete permission set
     */
    public function destroyPermissionSet(PermissionSet $permissionSet): JsonResponse
    {
        // Detach from roles and users first
        $permissionSet->roles()->detach();
        $permissionSet->users()->detach();
        $permissionSet->delete();

        return $this->success(null, 'Permission set deleted successfully');
    }
}
