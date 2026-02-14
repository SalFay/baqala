<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
        'current_password',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
    {
        // Only handle API requests with JSON responses
        if ($this->shouldReturnJson($request, $e)) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Determine if the exception should return JSON.
     */
    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    /**
     * Handle API exceptions and return standardized responses.
     */
    protected function handleApiException(Request $request, Throwable $e): JsonResponse
    {
        // Validation errors
        if ($e instanceof ValidationException) {
            return ApiResponse::validationError(
                $e->errors(),
                $e->getMessage() ?: 'Validation failed'
            );
        }

        // Authentication errors
        if ($e instanceof AuthenticationException) {
            return ApiResponse::unauthorized(
                $e->getMessage() ?: 'Unauthenticated'
            );
        }

        // Authorization/Access denied errors
        if ($e instanceof AccessDeniedHttpException) {
            return ApiResponse::forbidden(
                $e->getMessage() ?: 'Access denied'
            );
        }

        // Model not found errors
        if ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());
            return ApiResponse::notFound(
                "{$model} not found"
            );
        }

        // Route not found errors
        if ($e instanceof NotFoundHttpException) {
            return ApiResponse::notFound(
                'The requested resource was not found'
            );
        }

        // Method not allowed errors
        if ($e instanceof MethodNotAllowedHttpException) {
            return ApiResponse::error(
                'Method not allowed',
                405
            );
        }

        // Generic HTTP exceptions
        if ($e instanceof HttpException) {
            return ApiResponse::error(
                $e->getMessage() ?: 'HTTP Error',
                $e->getStatusCode()
            );
        }

        // Invalid argument exceptions (from services)
        if ($e instanceof \InvalidArgumentException) {
            return ApiResponse::error(
                $e->getMessage(),
                422
            );
        }

        // All other exceptions
        if (config('app.debug')) {
            return ApiResponse::error(
                $e->getMessage(),
                500,
                [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect($e->getTrace())->take(5)->toArray(),
                ]
            );
        }

        return ApiResponse::serverError('An unexpected error occurred');
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
    {
        if ($this->shouldReturnJson($request, $exception)) {
            return ApiResponse::unauthorized('Unauthenticated');
        }

        return redirect()->guest(route('login'));
    }
}
