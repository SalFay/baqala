<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Role\StoreRoleRequest;
use App\Http\Requests\Api\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Services\PermissionService;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RoleController extends Controller
{
    use HasListing;

    public function __construct(
        protected PermissionService $permissionService
    ) {}

    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Roles/Index', [
            'permissions' => $this->permissionService->getAllPermissions(),
            'roles' => RoleResource::collection(Role::withCount('users')->orderBy('sort_order')->get()),
        ]);
    }

    public function listing(Request $request): JsonResponse
    {
        return $this->getListing(
            $request,
            Role::class,
            resource: RoleResource::class,
            options: [
                'searchColumns' => ['name'],
                'withCount' => ['users'],
                'defaultSort' => 'sort_order',
                'defaultSortDir' => 'asc',
            ]
        );
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $data['permissions'] = $data['permissions'] ?? [];
        $data['sort_order'] = Role::max('sort_order') + 1;

        $role = Role::create($data);

        return response()->json([
            'data' => new RoleResource($role),
            'notifications' => [['type' => 'success', 'message' => 'Role created successfully']],
        ], 201);
    }

    public function edit(Role $role): JsonResponse
    {
        return response()->json([
            'data' => new RoleResource($role),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name'] ?? $role->name);
        $data['permissions'] = $data['permissions'] ?? $role->permissions;

        $role->update($data);

        return response()->json([
            'data' => new RoleResource($role->fresh()),
            'notifications' => [['type' => 'success', 'message' => 'Role updated successfully']],
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->users()->count() > 0) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot delete role with assigned users']],
            ], 422);
        }

        $role->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Role deleted successfully']],
        ]);
    }

    public function clone(Role $role): JsonResponse
    {
        $clonedRole = $role->replicate();
        $clonedRole->name = $role->name . ' (Copy)';
        $clonedRole->slug = Str::slug($clonedRole->name);
        $clonedRole->sort_order = Role::max('sort_order') + 1;
        $clonedRole->save();

        return response()->json([
            'data' => new RoleResource($clonedRole),
            'notifications' => [['type' => 'success', 'message' => 'Role cloned successfully']],
        ]);
    }

    public function getPermissions(): JsonResponse
    {
        return response()->json([
            'permissions' => $this->permissionService->getAllPermissions(),
        ]);
    }

    public function permissions()
    {
        $roles = Role::orderBy('sort_order')->get(['id', 'name', 'slug', 'permissions']);

        $previousPermissions = [];
        foreach ($roles as $role) {
            $previousPermissions[$role->id] = $role->permissions ?? [];
        }

        return Inertia::render('Roles/Permissions', [
            'roles' => RoleResource::collection($roles),
            'permissions' => $this->permissionService->getAllPermissions(),
            'previousPermissions' => $previousPermissions,
        ]);
    }

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
            'notifications' => [['type' => 'success', 'message' => 'Permissions updated successfully']],
        ]);
    }
}
