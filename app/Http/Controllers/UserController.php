<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display users listing page
     */
    public function index()
    {
        if (request()->wantsJson()) {
            return $this->listing(request());
        }

        return Inertia::render('Users/Index', [
            'roles' => Role::orderBy('sort_order')->get(['id', 'name', 'slug', 'color']),
        ]);
    }

    /**
     * Get paginated users listing
     */
    public function listing(Request $request): JsonResponse
    {
        $query = User::with('role:id,name,slug,color')->orderBy('first_name');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        $users = $query->paginate($perPage);

        $data = collect($users->items())->map(fn($user) => [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'role_id' => $user->role_id,
            'role' => $user->role ? [
                'id' => $user->role->id,
                'name' => $user->role->name,
                'slug' => $user->role->slug,
                'color' => $user->role->color,
            ] : null,
            'created_at' => $user->created_at?->format('Y-m-d H:i'),
        ]);

        return response()->json([
            'data' => $data,
            'total' => $users->total(),
        ]);
    }

    /**
     * Store a new user
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => 'required|exists:roles,id',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = $validated['status'] ?? 'active';

        $user = User::create($validated);
        $user->load('role:id,name,slug,color');

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user,
        ]);
    }

    /**
     * Get user for editing
     */
    public function edit(User $user): JsonResponse
    {
        $user->load('role:id,name,slug,color');

        return response()->json([
            'data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role_id' => $user->role_id,
                'status' => $user->status,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Update a user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role_id' => 'required|exists:roles,id',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->load('role:id,name,slug,color');

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
    }

    /**
     * Delete a user
     */
    public function destroy(User $user): JsonResponse
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }

    /**
     * Restore a soft-deleted user
     */
    public function restore($id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return response()->json([
            'success' => true,
            'message' => 'User restored successfully',
        ]);
    }
}
