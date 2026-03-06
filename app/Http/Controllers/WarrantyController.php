<?php

namespace App\Http\Controllers;

use App\Models\Warranty;
use App\Models\WarrantyClaim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WarrantyController extends Controller
{
    // ==================== Warranty Templates ====================

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Settings/Warranties/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $query = Warranty::query();

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->soft_deleted) {
            $query->onlyTrashed();
        }

        if ($request->sort && count($request->sort) > 0) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderBy('sort_order')->orderBy('name');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $warranties = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($warranty) => [
                'id' => $warranty->id,
                'name' => $warranty->name,
                'description' => $warranty->description,
                'duration' => $warranty->duration,
                'duration_type' => $warranty->duration_type,
                'duration_display' => $warranty->duration_display,
                'is_transferable' => $warranty->is_transferable,
                'products_count' => $warranty->products()->count(),
                'claims_count' => $warranty->claims()->count(),
                'is_active' => $warranty->is_active,
                'sort_order' => $warranty->sort_order,
                'created_at' => $warranty->created_at,
            ]);

        return response()->json([
            'data' => $warranties,
            'total' => $total,
        ]);
    }

    public function all(): JsonResponse
    {
        $warranties = Warranty::active()
            ->ordered()
            ->get()
            ->map(fn($warranty) => [
                'id' => $warranty->id,
                'name' => $warranty->name,
                'duration' => $warranty->duration,
                'duration_type' => $warranty->duration_type,
                'duration_display' => $warranty->duration_display,
            ]);

        return response()->json(['data' => $warranties]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'duration' => 'required|integer|min:1',
            'duration_type' => 'required|in:days,months,years',
            'terms' => 'nullable|string',
            'coverage' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'is_transferable' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $warranty = Warranty::create($validated);

        return response()->json([
            'data' => $warranty,
            'notifications' => [['type' => 'success', 'message' => 'Warranty created successfully']],
        ], 201);
    }

    public function update(Request $request, Warranty $warranty): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'duration' => 'sometimes|integer|min:1',
            'duration_type' => 'sometimes|in:days,months,years',
            'terms' => 'nullable|string',
            'coverage' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'is_transferable' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $warranty->update($validated);

        return response()->json([
            'data' => $warranty->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Warranty updated successfully']],
        ]);
    }

    public function destroy(Warranty $warranty): JsonResponse
    {
        $claimsCount = $warranty->claims()->count();

        if ($claimsCount > 0) {
            return response()->json([
                'message' => "Cannot delete warranty. It has {$claimsCount} claim(s) associated with it.",
            ], 422);
        }

        $warranty->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Warranty deleted successfully']],
        ]);
    }

    // ==================== Warranty Claims ====================

    public function claimsIndex(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->claimsListing($request);
        }

        return Inertia::render('Sales/WarrantyClaims/Index');
    }

    public function claimsListing(Request $request): JsonResponse
    {
        $query = WarrantyClaim::with(['warranty', 'productSerial.product', 'customer', 'assignedUser']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('claim_number', 'like', "%{$request->search}%")
                    ->orWhere('customer_name', 'like', "%{$request->search}%")
                    ->orWhere('customer_phone', 'like', "%{$request->search}%")
                    ->orWhereHas('productSerial', function ($q) use ($request) {
                        $q->where('serial_number', 'like', "%{$request->search}%")
                            ->orWhere('imei', 'like', "%{$request->search}%");
                    });
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->soft_deleted) {
            $query->onlyTrashed();
        }

        if ($request->sort && count($request->sort) > 0) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderBy('priority', 'desc')->orderBy('claim_date', 'desc');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $claims = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($claim) => [
                'id' => $claim->id,
                'claim_number' => $claim->claim_number,
                'warranty' => $claim->warranty ? [
                    'id' => $claim->warranty->id,
                    'name' => $claim->warranty->name,
                ] : null,
                'product_serial' => $claim->productSerial ? [
                    'id' => $claim->productSerial->id,
                    'serial_number' => $claim->productSerial->serial_number,
                    'product_name' => $claim->productSerial->product?->name,
                ] : null,
                'customer' => $claim->customer ? [
                    'id' => $claim->customer->id,
                    'name' => $claim->customer->name,
                ] : null,
                'customer_name' => $claim->customer_name ?? $claim->customer?->name,
                'customer_phone' => $claim->customer_phone,
                'claim_date' => $claim->claim_date->format('Y-m-d'),
                'days_since_claim' => $claim->getDaysSinceClaim(),
                'issue_description' => $claim->issue_description,
                'status' => $claim->status,
                'status_color' => $claim->getStatusColor(),
                'priority' => $claim->priority,
                'priority_label' => $claim->getPriorityLabel(),
                'priority_color' => $claim->getPriorityColor(),
                'resolution_type' => $claim->resolution_type,
                'assigned_to' => $claim->assignedUser ? [
                    'id' => $claim->assignedUser->id,
                    'name' => $claim->assignedUser->name,
                ] : null,
                'created_at' => $claim->created_at,
            ]);

        return response()->json([
            'data' => $claims,
            'total' => $total,
        ]);
    }

    public function storeClaim(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warranty_id' => 'nullable|exists:warranties,id',
            'product_serial_id' => 'nullable|exists:product_serials,id',
            'order_id' => 'nullable|integer',
            'order_item_id' => 'nullable|integer',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'claim_date' => 'required|date',
            'issue_description' => 'required|string|max:2000',
            'symptoms' => 'nullable|array',
            'priority' => 'nullable|integer|in:0,1,2',
        ]);

        $claim = WarrantyClaim::create($validated);

        return response()->json([
            'data' => $claim,
            'notifications' => [['type' => 'success', 'message' => 'Warranty claim created successfully']],
        ], 201);
    }

    public function updateClaim(Request $request, WarrantyClaim $warrantyClaim): JsonResponse
    {
        $validated = $request->validate([
            'issue_description' => 'sometimes|string|max:2000',
            'symptoms' => 'nullable|array',
            'diagnosis' => 'nullable|string|max:2000',
            'fault_type' => 'nullable|in:manufacturing,user_damage,wear_and_tear,unknown,other',
            'resolution_type' => 'nullable|in:repair,replace,refund,rejected,pending',
            'resolution_notes' => 'nullable|string|max:2000',
            'repair_cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,in_review,approved,in_progress,completed,rejected,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|integer|in:0,1,2',
            'internal_notes' => 'nullable|string|max:2000',
        ]);

        // Handle status transitions
        if (isset($validated['status'])) {
            if ($validated['status'] === 'in_review' && !$warrantyClaim->received_at) {
                $validated['received_at'] = now();
            }
            if (in_array($validated['status'], ['approved', 'rejected']) && !$warrantyClaim->reviewed_at) {
                $validated['reviewed_at'] = now();
            }
            if (in_array($validated['status'], ['completed', 'rejected']) && !$warrantyClaim->resolved_at) {
                $validated['resolved_at'] = now();
            }
        }

        $warrantyClaim->update($validated);

        return response()->json([
            'data' => $warrantyClaim->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Claim updated successfully']],
        ]);
    }

    public function showClaim(WarrantyClaim $warrantyClaim): JsonResponse
    {
        $warrantyClaim->load([
            'warranty',
            'productSerial.product',
            'customer',
            'order',
            'assignedUser',
            'replacementSerial',
        ]);

        return response()->json([
            'data' => [
                'id' => $warrantyClaim->id,
                'claim_number' => $warrantyClaim->claim_number,
                'warranty' => $warrantyClaim->warranty,
                'product_serial' => $warrantyClaim->productSerial ? [
                    'id' => $warrantyClaim->productSerial->id,
                    'serial_number' => $warrantyClaim->productSerial->serial_number,
                    'imei' => $warrantyClaim->productSerial->imei,
                    'product' => $warrantyClaim->productSerial->product,
                ] : null,
                'customer' => $warrantyClaim->customer,
                'customer_name' => $warrantyClaim->customer_name,
                'customer_phone' => $warrantyClaim->customer_phone,
                'customer_email' => $warrantyClaim->customer_email,
                'claim_date' => $warrantyClaim->claim_date->format('Y-m-d'),
                'issue_description' => $warrantyClaim->issue_description,
                'symptoms' => $warrantyClaim->symptoms,
                'diagnosis' => $warrantyClaim->diagnosis,
                'fault_type' => $warrantyClaim->fault_type,
                'resolution_type' => $warrantyClaim->resolution_type,
                'resolution_notes' => $warrantyClaim->resolution_notes,
                'repair_cost' => $warrantyClaim->repair_cost,
                'status' => $warrantyClaim->status,
                'priority' => $warrantyClaim->priority,
                'assigned_to' => $warrantyClaim->assignedUser,
                'internal_notes' => $warrantyClaim->internal_notes,
                'received_at' => $warrantyClaim->received_at,
                'reviewed_at' => $warrantyClaim->reviewed_at,
                'resolved_at' => $warrantyClaim->resolved_at,
                'created_at' => $warrantyClaim->created_at,
            ],
        ]);
    }

    public function destroyClaim(WarrantyClaim $warrantyClaim): JsonResponse
    {
        if (!$warrantyClaim->canBeEdited()) {
            return response()->json([
                'message' => 'Cannot delete a claim that is already being processed.',
            ], 422);
        }

        $warrantyClaim->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Claim deleted successfully']],
        ]);
    }

    public function claimStatistics(): JsonResponse
    {
        $stats = [
            'total' => WarrantyClaim::count(),
            'pending' => WarrantyClaim::pending()->count(),
            'in_progress' => WarrantyClaim::inProgress()->count(),
            'completed' => WarrantyClaim::completed()->count(),
            'rejected' => WarrantyClaim::rejected()->count(),
            'urgent' => WarrantyClaim::urgent()->open()->count(),
            'avg_resolution_days' => WarrantyClaim::completed()
                ->whereNotNull('resolved_at')
                ->selectRaw('AVG(DATEDIFF(resolved_at, claim_date)) as avg_days')
                ->value('avg_days') ?? 0,
        ];

        return response()->json(['data' => $stats]);
    }
}
