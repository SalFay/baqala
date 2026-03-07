<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Restaurant\StoreTableRequest;
use App\Http\Requests\Api\Restaurant\UpdateTableRequest;
use App\Http\Resources\RestaurantTableResource;
use App\Models\RestaurantTable;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RestaurantTableController extends Controller
{
    use HasListing;

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Restaurant/Tables/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        return $this->getListing(
            $request,
            RestaurantTable::class,
            with: ['location', 'currentOrder'],
            resource: RestaurantTableResource::class,
            options: [
                'searchColumns' => ['name', 'section'],
                'filterColumns' => [
                    'status' => 'exact',
                    'section' => 'exact',
                    'floor' => 'exact',
                    'is_active' => 'exact',
                ],
                'defaultSort' => 'section',
                'defaultSortDir' => 'asc',
            ]
        );
    }

    public function store(StoreTableRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['store_id'] = auth()->user()->store_id ?? 1;
        $data['status'] = RestaurantTable::STATUS_AVAILABLE;

        $table = RestaurantTable::create($data);

        return response()->json([
            'data' => new RestaurantTableResource($table),
            'notifications' => [['type' => 'success', 'message' => 'Table created successfully']],
        ], 201);
    }

    public function update(UpdateTableRequest $request, RestaurantTable $restaurantTable): JsonResponse
    {
        $restaurantTable->update($request->validated());

        return response()->json([
            'data' => new RestaurantTableResource($restaurantTable->fresh()),
            'notifications' => [['type' => 'success', 'message' => 'Table updated successfully']],
        ]);
    }

    public function destroy(RestaurantTable $restaurantTable): JsonResponse
    {
        if ($restaurantTable->isOccupied()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot delete an occupied table']],
            ], 422);
        }

        $restaurantTable->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Table deleted successfully']],
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $query = RestaurantTable::active();

        if ($request->available_only) {
            $query->available();
        }

        if ($request->min_capacity) {
            $query->withCapacity((int) $request->min_capacity);
        }

        $tables = $query->orderBy('section')->orderBy('name')->get();

        return response()->json([
            'data' => RestaurantTableResource::collection($tables),
        ]);
    }

    public function updateStatus(Request $request, RestaurantTable $restaurantTable): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,reserved,maintenance',
        ]);

        match ($validated['status']) {
            'available' => $restaurantTable->release(),
            'maintenance' => $restaurantTable->setMaintenance(),
            'reserved' => $restaurantTable->reserve(),
            default => $restaurantTable->update(['status' => $validated['status']]),
        };

        return response()->json([
            'data' => new RestaurantTableResource($restaurantTable->fresh()),
            'notifications' => [['type' => 'success', 'message' => 'Table status updated']],
        ]);
    }

    public function floorPlan(Request $request): JsonResponse
    {
        $query = RestaurantTable::active();

        if ($request->floor) {
            $query->onFloor($request->floor);
        }

        $tables = $query->get();
        $sections = RestaurantTable::active()->distinct()->pluck('section')->filter()->values();
        $floors = RestaurantTable::active()->distinct()->pluck('floor')->filter()->values();

        return response()->json([
            'tables' => RestaurantTableResource::collection($tables),
            'sections' => $sections,
            'floors' => $floors,
        ]);
    }

    public function updatePosition(Request $request, RestaurantTable $restaurantTable): JsonResponse
    {
        $validated = $request->validate([
            'position_x' => 'required|integer',
            'position_y' => 'required|integer',
        ]);

        $restaurantTable->update($validated);

        return response()->json([
            'data' => new RestaurantTableResource($restaurantTable->fresh()),
        ]);
    }
}
