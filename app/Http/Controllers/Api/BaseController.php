<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseController extends Controller
{
    /**
     * Return a success response.
     */
    protected function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return ApiResponse::success($data, $message, $code);
    }

    /**
     * Return a created response.
     */
    protected function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return ApiResponse::created($data, $message);
    }

    /**
     * Return an error response.
     */
    protected function error(string $message = 'Error', int $code = 400, array $errors = []): JsonResponse
    {
        return ApiResponse::error($message, $code, $errors);
    }

    /**
     * Return a validation error response.
     */
    protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return ApiResponse::validationError($errors, $message);
    }

    /**
     * Return a not found response.
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return ApiResponse::notFound($message);
    }

    /**
     * Return an unauthorized response.
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return ApiResponse::unauthorized($message);
    }

    /**
     * Return a forbidden response.
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return ApiResponse::forbidden($message);
    }

    /**
     * Return a server error response.
     */
    protected function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return ApiResponse::serverError($message);
    }

    /**
     * Return a paginated response.
     */
    protected function paginated(LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        return ApiResponse::success($paginator, $message);
    }

    /**
     * Get the store ID from request or authenticated user.
     */
    protected function getStoreId(): ?int
    {
        $storeId = request()->input('store_id');

        if (!$storeId && auth()->check()) {
            $user = auth()->user();
            // Get user's primary/default store
            $storeId = $user->stores()->first()?->id;
        }

        return $storeId ? (int) $storeId : null;
    }

    /**
     * Get pagination parameters from request.
     */
    protected function getPaginationParams(): array
    {
        return [
            'per_page' => min((int) request()->input('per_page', 20), 100),
            'page' => max((int) request()->input('page', 1), 1),
        ];
    }

    /**
     * Get sort parameters from request.
     */
    protected function getSortParams(string $defaultSort = 'created_at', string $defaultDirection = 'desc'): array
    {
        $sortBy = request()->input('sort_by', $defaultSort);
        $sortDirection = strtolower(request()->input('sort_direction', $defaultDirection));

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        return [
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
        ];
    }
}
