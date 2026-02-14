<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * List all users with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with('role');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $users = $query->paginate($request->input('per_page', 20));

        return $this->paginated($users);
    }

    /**
     * Create a new user
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role_id' => 'required|exists:roles,id',
            'status' => 'nullable|in:active,inactive',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['name'] = $validated['first_name'] . ' ' . $validated['last_name'];
        $validated['status'] = $validated['status'] ?? 'active';

        $user = User::create($validated);

        // Assign to store if provided
        if (!empty($validated['store_id'])) {
            $user->stores()->attach($validated['store_id'], ['is_primary' => true]);
        }

        return $this->created($user->load('role'), 'User created successfully');
    }

    /**
     * Show user details
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['role', 'stores']);

        return $this->success($user);
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role_id' => 'sometimes|required|exists:roles,id',
            'status' => 'nullable|in:active,inactive',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if (isset($validated['first_name']) || isset($validated['last_name'])) {
            $validated['name'] = ($validated['first_name'] ?? $user->first_name) . ' ' .
                                 ($validated['last_name'] ?? $user->last_name);
        }

        $user->update($validated);

        return $this->success($user->load('role'), 'User updated successfully');
    }

    /**
     * Delete user
     */
    public function destroy(User $user): JsonResponse
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return $this->error('Cannot delete your own account', 422);
        }

        // Check if user has orders
        if ($user->orders()->exists()) {
            return $this->error('Cannot delete user with orders. Deactivate instead.', 422);
        }

        $user->delete();

        return $this->success(null, 'User deleted successfully');
    }

    /**
     * Assign stores to user
     */
    public function assignStores(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'store_ids' => 'required|array',
            'store_ids.*' => 'exists:stores,id',
            'primary_store_id' => 'nullable|in_array:store_ids.*',
        ]);

        $storeIds = collect($validated['store_ids']);
        $primaryStoreId = $validated['primary_store_id'] ?? $storeIds->first();

        $syncData = [];
        foreach ($storeIds as $storeId) {
            $syncData[$storeId] = ['is_primary' => $storeId == $primaryStoreId];
        }

        $user->stores()->sync($syncData);

        return $this->success($user->load('stores'), 'Stores assigned successfully');
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return $this->success(null, 'Password changed successfully');
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return $this->error('Cannot deactivate your own account', 422);
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return $this->success($user, 'User status updated');
    }
}
