<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChequeController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Payments/Cheques/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $query = Cheque::query()->with(['customer', 'createdBy']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('cheque_number', 'like', "%{$request->search}%")
                    ->orWhere('bank_name', 'like', "%{$request->search}%")
                    ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->soft_deleted) {
            $query->onlyTrashed();
        }

        if ($request->due_soon) {
            $query->dueSoon((int) $request->due_soon);
        }

        if ($request->overdue) {
            $query->overdue();
        }

        if ($request->sort && count($request->sort) > 0) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderBy('due_date', 'asc')->orderBy('created_at', 'desc');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $cheques = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($cheque) => [
                'id' => $cheque->id,
                'cheque_number' => $cheque->cheque_number,
                'bank_name' => $cheque->bank_name,
                'bank_branch' => $cheque->bank_branch,
                'amount' => $cheque->amount,
                'cheque_date' => $cheque->cheque_date?->format('Y-m-d'),
                'due_date' => $cheque->due_date?->format('Y-m-d'),
                'status' => $cheque->status,
                'status_color' => $cheque->status_color,
                'days_until_due' => $cheque->days_until_due,
                'is_overdue' => $cheque->isOverdue(),
                'customer' => $cheque->customer ? [
                    'id' => $cheque->customer->id,
                    'name' => $cheque->customer->name,
                ] : null,
                'deposited_at' => $cheque->deposited_at?->format('Y-m-d H:i'),
                'cleared_at' => $cheque->cleared_at?->format('Y-m-d H:i'),
                'notes' => $cheque->notes,
                'created_at' => $cheque->created_at,
            ]);

        return response()->json([
            'data' => $cheques,
            'total' => $total,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cheque_number' => 'required|string|max:50',
            'bank_name' => 'required|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:0.01',
            'cheque_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:cheque_date',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_id' => 'nullable|exists:payments,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['store_id'] = auth()->user()->store_id ?? 1;
        $validated['created_by'] = auth()->id();
        $validated['status'] = Cheque::STATUS_PENDING;

        $cheque = Cheque::create($validated);

        return response()->json([
            'data' => $cheque,
            'notifications' => [['type' => 'success', 'message' => 'Cheque recorded successfully']],
        ], 201);
    }

    public function update(Request $request, Cheque $cheque): JsonResponse
    {
        $validated = $request->validate([
            'cheque_number' => 'sometimes|string|max:50',
            'bank_name' => 'sometimes|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'amount' => 'sometimes|numeric|min:0.01',
            'cheque_date' => 'sometimes|date',
            'due_date' => 'nullable|date',
            'customer_id' => 'nullable|exists:customers,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cheque->update($validated);

        return response()->json([
            'data' => $cheque->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Cheque updated successfully']],
        ]);
    }

    public function destroy(Cheque $cheque): JsonResponse
    {
        $cheque->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Cheque deleted successfully']],
        ]);
    }

    /**
     * Mark cheque as deposited
     */
    public function deposit(Cheque $cheque): JsonResponse
    {
        if (!$cheque->deposit()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot deposit this cheque']],
            ], 422);
        }

        return response()->json([
            'data' => $cheque->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Cheque marked as deposited']],
        ]);
    }

    /**
     * Mark cheque as cleared
     */
    public function clear(Cheque $cheque): JsonResponse
    {
        if (!$cheque->clear()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot clear this cheque']],
            ], 422);
        }

        return response()->json([
            'data' => $cheque->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Cheque marked as cleared']],
        ]);
    }

    /**
     * Mark cheque as bounced
     */
    public function bounce(Request $request, Cheque $cheque): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        if (!$cheque->bounce($validated['notes'] ?? null)) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot mark this cheque as bounced']],
            ], 422);
        }

        return response()->json([
            'data' => $cheque->fresh(),
            'notifications' => [['type' => 'warning', 'message' => 'Cheque marked as bounced']],
        ]);
    }

    /**
     * Cancel cheque
     */
    public function cancel(Request $request, Cheque $cheque): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        if (!$cheque->cancel($validated['notes'] ?? null)) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot cancel this cheque']],
            ], 422);
        }

        return response()->json([
            'data' => $cheque->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Cheque cancelled']],
        ]);
    }

    /**
     * Get cheque summary/stats
     */
    public function summary(): JsonResponse
    {
        $pendingCount = Cheque::pending()->count();
        $pendingAmount = Cheque::pending()->sum('amount');
        $depositedCount = Cheque::deposited()->count();
        $depositedAmount = Cheque::deposited()->sum('amount');
        $overdueCount = Cheque::overdue()->count();
        $overdueAmount = Cheque::overdue()->sum('amount');
        $dueSoonCount = Cheque::dueSoon(7)->count();
        $dueSoonAmount = Cheque::dueSoon(7)->sum('amount');

        return response()->json([
            'pending' => ['count' => $pendingCount, 'amount' => $pendingAmount],
            'deposited' => ['count' => $depositedCount, 'amount' => $depositedAmount],
            'overdue' => ['count' => $overdueCount, 'amount' => $overdueAmount],
            'due_soon' => ['count' => $dueSoonCount, 'amount' => $dueSoonAmount],
        ]);
    }
}
