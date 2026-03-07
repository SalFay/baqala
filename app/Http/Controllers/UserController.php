<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\User\StoreUserRequest;
use App\Http\Requests\Api\User\UpdateUserRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserController extends Controller
{
    use HasListing;

    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Users/Index', [
            'roles' => RoleResource::collection(Role::orderBy('sort_order')->get()),
        ]);
    }

    public function listing(Request $request): JsonResponse
    {
        return $this->getListing(
            $request,
            User::class,
            with: ['role'],
            resource: UserResource::class,
            options: [
                'searchColumns' => ['first_name', 'last_name', 'email'],
                'filterColumns' => [
                    'role_id' => 'exact',
                    'status' => 'exact',
                ],
                'defaultSort' => 'first_name',
                'defaultSortDir' => 'asc',
            ]
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['status'] = $data['status'] ?? 'active';

        $user = User::create($data);
        $user->load('role');

        return response()->json([
            'data' => new UserResource($user),
            'notifications' => [['type' => 'success', 'message' => 'User created successfully']],
        ], 201);
    }

    public function edit(User $user): JsonResponse
    {
        $user->load('role');

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $user->load('role');

        return response()->json([
            'data' => new UserResource($user),
            'notifications' => [['type' => 'success', 'message' => 'User updated successfully']],
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'You cannot delete your own account']],
            ], 422);
        }

        $user->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'User deleted successfully']],
        ]);
    }

    public function updatePassword(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Password updated successfully']],
        ]);
    }

    public function restore($id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'User restored successfully']],
        ]);
    }
}
