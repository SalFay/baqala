<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    /**
     * Get all statuses for a category type.
     */
    public function index(Request $request, string $category): JsonResponse
    {
        $statuses = Status::forCategory($category)
            ->active()
            ->ordered()
            ->get();

        return StatusResource::collection($statuses)->response();
    }

    /**
     * Get a specific status.
     */
    public function show(Status $status): JsonResponse
    {
        return StatusResource::make($status)->response();
    }

    /**
     * Get default status for a category.
     */
    public function getDefault(string $category): JsonResponse
    {
        $status = Status::getDefault($category);

        if (!$status) {
            return response()->json(['message' => 'No default status found for this category'], 404);
        }

        return StatusResource::make($status)->response();
    }

    /**
     * Get all available category types.
     */
    public function categories(): JsonResponse
    {
        $categories = Status::query()
            ->select('category_type')
            ->distinct()
            ->pluck('category_type');

        return response()->json(['categories' => $categories]);
    }
}
