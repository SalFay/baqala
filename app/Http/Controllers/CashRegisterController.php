<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Services\CashRegisterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CashRegisterController extends Controller
{
    public function __construct(
        protected CashRegisterService $cashRegisterService
    ) {}

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('POS/CashRegisters/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $query = CashRegister::query()->with(['user', 'location', 'openedBy', 'closedBy']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->date) {
            $query->whereDate('opened_at', $request->date);
        }

        if ($request->sort && count($request->sort) > 0) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderBy('opened_at', 'desc');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $registers = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($register) => [
                'id' => $register->id,
                'name' => $register->name,
                'status' => $register->status,
                'user' => $register->user ? [
                    'id' => $register->user->id,
                    'name' => $register->user->name,
                ] : null,
                'location' => $register->location?->name,
                'opening_cash' => $register->opening_cash,
                'closing_cash' => $register->closing_cash,
                'expected_cash' => $register->expected_cash,
                'cash_difference' => $register->cash_difference,
                'opened_at' => $register->opened_at?->format('Y-m-d H:i'),
                'closed_at' => $register->closed_at?->format('Y-m-d H:i'),
                'opened_by' => $register->openedBy?->name,
                'closed_by' => $register->closedBy?->name,
            ]);

        return response()->json([
            'data' => $registers,
            'total' => $total,
        ]);
    }

    /**
     * Get current register status for POS
     */
    public function current(): JsonResponse
    {
        $register = $this->cashRegisterService->getCurrentRegister();

        if (!$register) {
            return response()->json([
                'has_open_register' => false,
                'register' => null,
            ]);
        }

        $summary = $this->cashRegisterService->getRegisterSummary($register);

        return response()->json([
            'has_open_register' => true,
            'register' => [
                'id' => $register->id,
                'name' => $register->name,
                'status' => $register->status,
                'opening_cash' => $register->opening_cash,
                'opened_at' => $register->opened_at?->format('Y-m-d H:i'),
            ],
            'summary' => $summary,
        ]);
    }

    /**
     * Open a cash register
     */
    public function open(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'denominations' => 'nullable|array',
            'note' => 'nullable|string|max:500',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        // Check if user already has an open register
        if ($this->cashRegisterService->hasOpenRegister()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'You already have an open register']],
            ], 422);
        }

        // Get or create a register
        $register = CashRegister::getOrCreateForPOS($validated['location_id'] ?? null);

        $this->cashRegisterService->openRegister(
            $register,
            $validated['opening_cash'],
            $validated['denominations'] ?? null,
            $validated['note'] ?? null
        );

        return response()->json([
            'data' => $register->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Register opened successfully']],
        ]);
    }

    /**
     * Close a cash register
     */
    public function close(Request $request, CashRegister $cashRegister): JsonResponse
    {
        $validated = $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'denominations' => 'nullable|array',
            'note' => 'nullable|string|max:500',
        ]);

        $errors = $this->cashRegisterService->canCloseRegister($cashRegister);
        if (!empty($errors)) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => $errors[0]]],
            ], 422);
        }

        $this->cashRegisterService->closeRegister(
            $cashRegister,
            $validated['closing_cash'],
            $validated['denominations'] ?? null,
            $validated['note'] ?? null
        );

        return response()->json([
            'data' => $cashRegister->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Register closed successfully']],
        ]);
    }

    /**
     * Pay in (add cash to register)
     */
    public function payIn(Request $request, CashRegister $cashRegister): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500',
        ]);

        if (!$cashRegister->isOpen()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Register is not open']],
            ], 422);
        }

        $transaction = $this->cashRegisterService->payIn(
            $cashRegister,
            $validated['amount'],
            $validated['note'] ?? null
        );

        return response()->json([
            'data' => $transaction,
            'notifications' => [['type' => 'success', 'message' => 'Cash added to register']],
        ]);
    }

    /**
     * Pay out (remove cash from register)
     */
    public function payOut(Request $request, CashRegister $cashRegister): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'required|string|max:500',
        ]);

        if (!$cashRegister->isOpen()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Register is not open']],
            ], 422);
        }

        $transaction = $this->cashRegisterService->payOut(
            $cashRegister,
            $validated['amount'],
            $validated['note']
        );

        return response()->json([
            'data' => $transaction,
            'notifications' => [['type' => 'success', 'message' => 'Cash removed from register']],
        ]);
    }

    /**
     * Get register summary
     */
    public function summary(CashRegister $cashRegister): JsonResponse
    {
        $summary = $this->cashRegisterService->getRegisterSummary($cashRegister);

        return response()->json($summary);
    }

    /**
     * Get register transactions
     */
    public function transactions(Request $request, CashRegister $cashRegister): JsonResponse
    {
        $filters = $request->only(['type', 'payment_method_id']);
        $transactions = $this->cashRegisterService->getTransactions($cashRegister, $filters);

        return response()->json([
            'data' => $transactions->map(fn($t) => [
                'id' => $t->id,
                'transaction_type' => $t->transaction_type,
                'type_label' => $t->type_label,
                'type_color' => $t->type_color,
                'amount' => $t->amount,
                'signed_amount' => $t->signed_amount,
                'payment_method' => $t->paymentMethod?->name,
                'note' => $t->note,
                'created_by' => $t->createdBy?->name,
                'created_at' => $t->created_at->format('Y-m-d H:i'),
            ]),
        ]);
    }

    /**
     * Get daily report
     */
    public function dailyReport(Request $request): JsonResponse
    {
        $date = $request->date ?? today()->toDateString();
        $report = $this->cashRegisterService->getDailyReport($date);

        return response()->json($report);
    }

    /**
     * Get denomination options
     */
    public function denominations(): JsonResponse
    {
        return response()->json([
            'denominations' => $this->cashRegisterService->getDenominations(),
        ]);
    }
}
