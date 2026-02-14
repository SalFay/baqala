<?php

namespace App\Http\Controllers\Api;

use App\Http\Responses\ApiResponse;
use App\Services\Sync\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends BaseController
{
    public function __construct(
        protected SyncService $syncService
    ) {}

    /**
     * Bootstrap sync - initial full data download.
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'terminal_id' => 'required|uuid',
            'store_id' => 'required|integer|exists:stores,id',
            'entities' => 'nullable|array',
            'entities.*' => 'string|in:products,categories,customers,settings,tax_rates,payment_methods',
        ]);

        try {
            $data = $this->syncService->bootstrap(
                $validated['terminal_id'],
                $validated['store_id'],
                $validated['entities'] ?? null
            );

            return $this->success($data, 'Bootstrap data retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Pull changes from server (delta sync).
     */
    public function pull(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'terminal_id' => 'required|uuid',
            'store_id' => 'required|integer|exists:stores,id',
            'last_sync_at' => 'nullable|date',
            'entity_versions' => 'nullable|array',
            'entity_versions.*.entity_type' => 'required|string',
            'entity_versions.*.version' => 'required|integer',
        ]);

        try {
            $data = $this->syncService->pull(
                $validated['terminal_id'],
                $validated['store_id'],
                $validated['last_sync_at'] ?? null,
                $validated['entity_versions'] ?? []
            );

            return $this->success($data, 'Changes retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Push changes to server.
     */
    public function push(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'terminal_id' => 'required|uuid',
            'store_id' => 'required|integer|exists:stores,id',
            'orders' => 'nullable|array',
            'orders.*.offline_id' => 'required|uuid',
            'orders.*.data' => 'required|array',
            'orders.*.created_offline_at' => 'required|date',
            'customers' => 'nullable|array',
            'customers.*.offline_id' => 'required|uuid',
            'customers.*.data' => 'required|array',
            'inventory_adjustments' => 'nullable|array',
        ]);

        try {
            $result = $this->syncService->push(
                $validated['terminal_id'],
                $validated['store_id'],
                $validated
            );

            return $this->success($result, 'Changes pushed successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get sync status.
     */
    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'terminal_id' => 'required|uuid',
            'store_id' => 'required|integer|exists:stores,id',
        ]);

        try {
            $status = $this->syncService->getStatus(
                $validated['terminal_id'],
                $validated['store_id']
            );

            return $this->success($status);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Register a terminal.
     */
    public function registerTerminal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'terminal_id' => 'required|uuid',
            'store_id' => 'required|integer|exists:stores,id',
            'name' => 'nullable|string|max:100',
            'device_info' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:50',
        ]);

        try {
            $terminal = $this->syncService->registerTerminal(
                $validated['terminal_id'],
                $validated['store_id'],
                $validated
            );

            return $this->success($terminal, 'Terminal registered successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Resolve a sync conflict.
     */
    public function resolve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conflict_id' => 'required|uuid',
            'resolution' => 'required|string|in:client_wins,server_wins,manual',
            'resolved_data' => 'required_if:resolution,manual|nullable|array',
        ]);

        try {
            $result = $this->syncService->resolveConflict(
                $validated['conflict_id'],
                $validated['resolution'],
                $validated['resolved_data'] ?? null
            );

            return $this->success($result, 'Conflict resolved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get pending conflicts for a terminal.
     */
    public function conflicts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'terminal_id' => 'required|uuid',
        ]);

        try {
            $conflicts = $this->syncService->getPendingConflicts(
                $validated['terminal_id']
            );

            return $this->success($conflicts);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
