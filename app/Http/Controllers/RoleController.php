<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Display roles listing page
     */
    public function index()
    {
        if (request()->wantsJson()) {
            return $this->listing(request());
        }

        return Inertia::render('Roles/Index', [
            'permissions' => $this->getAllPermissions(),
            'roles' => Role::withCount('users')->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Get paginated roles listing
     */
    public function listing(Request $request): JsonResponse
    {
        $query = Role::withCount('users')->orderBy('sort_order');

        // Search
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        $roles = $query->paginate($perPage);

        return response()->json([
            'data' => $roles->items(),
            'total' => $roles->total(),
        ]);
    }

    /**
     * Store a new role
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'color' => 'nullable|string|max:9',
            'permissions' => 'array',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['permissions'] = $validated['permissions'] ?? [];
        $validated['sort_order'] = Role::max('sort_order') + 1;

        $role = Role::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role,
        ]);
    }

    /**
     * Get role for editing
     */
    public function edit(Role $role): JsonResponse
    {
        return response()->json([
            'data' => $role,
        ]);
    }

    /**
     * Update a role
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'color' => 'nullable|string|max:9',
            'permissions' => 'array',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['permissions'] = $validated['permissions'] ?? [];

        $role->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role,
        ]);
    }

    /**
     * Delete a role
     */
    public function destroy(Role $role): JsonResponse
    {
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role with assigned users',
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully',
        ]);
    }

    /**
     * Clone a role
     */
    public function clone(Role $role): JsonResponse
    {
        $clonedRole = $role->replicate();
        $clonedRole->name = $role->name . ' (Copy)';
        $clonedRole->slug = Str::slug($clonedRole->name);
        $clonedRole->sort_order = Role::max('sort_order') + 1;
        $clonedRole->save();

        return response()->json([
            'success' => true,
            'message' => 'Role cloned successfully',
            'data' => $clonedRole,
        ]);
    }

    /**
     * Get all permissions from permission files
     */
    public function getPermissions(): JsonResponse
    {
        return response()->json([
            'permissions' => $this->getAllPermissions(),
        ]);
    }

    /**
     * Display permissions management page (matrix view)
     */
    public function permissions()
    {
        $roles = Role::orderBy('sort_order')->get(['id', 'name', 'slug', 'permissions']);

        // Build previousPermissions array (role_id => [permissions])
        $previousPermissions = [];
        foreach ($roles as $role) {
            $previousPermissions[$role->id] = $role->permissions ?? [];
        }

        return Inertia::render('Roles/Permissions', [
            'roles' => $roles,
            'permissions' => $this->getAllPermissions(),
            'previousPermissions' => $previousPermissions,
        ]);
    }

    /**
     * Store permissions for multiple roles at once
     */
    public function storePermissions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*.role_id' => 'required|exists:roles,id',
            'permissions.*.permissions' => 'array',
        ]);

        foreach ($validated['permissions'] as $item) {
            Role::where('id', $item['role_id'])->update([
                'permissions' => $item['permissions'] ?? [],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully',
        ]);
    }

    /**
     * Load all permissions from the Permissions directory
     */
    private function getAllPermissions(): array
    {
        $permissions = [];
        $permissionsPath = app_path('Permissions');

        if (!File::isDirectory($permissionsPath)) {
            return $permissions;
        }

        $files = File::allFiles($permissionsPath);

        foreach ($files as $file) {
            $permissionContent = include $file->getPathname();

            $fileName = str_replace('.php', '', $file->getFilename());

            $permissions[$fileName] = [
                'title' => $permissionContent['title'] ?? $fileName,
                'description' => $permissionContent['description'] ?? '',
                'permissions' => $permissionContent['permissions'] ?? [],
            ];
        }

        return $permissions;
    }
}
