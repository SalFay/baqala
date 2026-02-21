<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Store;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Server-side listing for DataGridTable
     */
    public function listing(Request $request): JsonResponse
    {
        $store = Store::first();

        $query = Expense::with(['category', 'vendor', 'creator'])
            ->forStore($store->id);

        // Search
        if ($request->search) {
            $query->search($request->search);
        }

        // Sorting
        if ($request->sort && count($request->sort) > 0) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderBy('expense_date', 'desc');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $expenses = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($expense) => [
                'id' => $expense->id,
                'expense_date' => $expense->expense_date,
                'category' => $expense->category?->name,
                'vendor' => $expense->vendor?->name,
                'amount' => $expense->amount,
                'total' => $expense->total,
                'status' => $expense->status,
                'payment_method' => $expense->payment_method,
                'description' => $expense->description,
                'created_by' => $expense->creator?->name,
            ]);

        return response()->json([
            'data' => $expenses,
            'total' => $total,
        ]);
    }

    /**
     * List expenses with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $store = Store::first();

        $query = Expense::with(['category', 'vendor', 'creator', 'approver'])
            ->forStore($store->id)
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('is_recurring')) {
            $query->where('is_recurring', $request->boolean('is_recurring'));
        }

        $perPage = $request->input('per_page', 20);
        $expenses = $query->paginate($perPage);

        return response()->json([
            'data' => $expenses->items(),
            'meta' => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
            ],
        ]);
    }

    /**
     * Get expense summary/stats.
     */
    public function summary(Request $request): JsonResponse
    {
        $store = Store::first();

        $thisMonth = Expense::forStore($store->id)->thisMonth();
        $pending = Expense::forStore($store->id)->pending();

        return response()->json([
            'data' => [
                'this_month_total' => (float) $thisMonth->sum('total'),
                'this_month_count' => $thisMonth->count(),
                'pending_count' => $pending->count(),
                'pending_total' => (float) $pending->sum('total'),
                'approved_this_month' => (float) Expense::forStore($store->id)
                    ->thisMonth()
                    ->approved()
                    ->sum('total'),
                'paid_this_month' => (float) Expense::forStore($store->id)
                    ->thisMonth()
                    ->paid()
                    ->sum('total'),
            ],
        ]);
    }

    /**
     * Get a single expense.
     */
    public function show(Expense $expense): JsonResponse
    {
        $expense->load(['category', 'vendor', 'creator', 'approver', 'account']);

        return response()->json([
            'data' => $expense->toApiArray(),
        ]);
    }

    /**
     * Create a new expense.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,bank_transfer,cheque',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|string|in:daily,weekly,monthly,yearly',
            'status' => 'nullable|string|in:draft,pending',
        ]);

        $store = Store::first();

        $expense = Expense::create([
            ...$validated,
            'store_id' => $store->id,
            'created_by' => auth()->id(),
            'tax_amount' => $validated['tax_amount'] ?? 0,
            'total' => $validated['amount'] + ($validated['tax_amount'] ?? 0),
            'status' => $validated['status'] ?? Expense::STATUS_PENDING,
        ]);

        $expense->load(['category', 'vendor', 'creator']);

        return response()->json([
            'message' => 'Expense created successfully',
            'data' => $expense->toApiArray(),
        ], 201);
    }

    /**
     * Update an expense.
     */
    public function update(Request $request, Expense $expense): JsonResponse
    {
        if (!$expense->canBeEdited()) {
            return response()->json([
                'error' => 'This expense cannot be edited in its current status',
            ], 422);
        }

        $validated = $request->validate([
            'expense_category_id' => 'sometimes|exists:expense_categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'expense_date' => 'sometimes|date',
            'amount' => 'sometimes|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'sometimes|string|in:cash,card,bank_transfer,cheque',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|string|in:daily,weekly,monthly,yearly',
        ]);

        if (isset($validated['amount']) || isset($validated['tax_amount'])) {
            $amount = $validated['amount'] ?? $expense->amount;
            $taxAmount = $validated['tax_amount'] ?? $expense->tax_amount;
            $validated['total'] = $amount + $taxAmount;
        }

        // Reset status to pending if rejected expense is edited
        if ($expense->isRejected()) {
            $validated['status'] = Expense::STATUS_PENDING;
            $validated['rejection_reason'] = null;
        }

        $expense->update($validated);
        $expense->load(['category', 'vendor', 'creator', 'approver']);

        return response()->json([
            'message' => 'Expense updated successfully',
            'data' => $expense->toApiArray(),
        ]);
    }

    /**
     * Delete an expense.
     */
    public function destroy(Expense $expense): JsonResponse
    {
        if ($expense->isPaid()) {
            return response()->json([
                'error' => 'Paid expenses cannot be deleted',
            ], 422);
        }

        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully',
        ]);
    }

    /**
     * Approve an expense.
     */
    public function approve(Expense $expense): JsonResponse
    {
        if (!$expense->canBeApproved()) {
            return response()->json([
                'error' => 'This expense cannot be approved',
            ], 422);
        }

        $expense->approve(auth()->id());
        $expense->load(['category', 'vendor', 'creator', 'approver']);

        return response()->json([
            'message' => 'Expense approved successfully',
            'data' => $expense->toApiArray(),
        ]);
    }

    /**
     * Reject an expense.
     */
    public function reject(Request $request, Expense $expense): JsonResponse
    {
        if (!$expense->canBeRejected()) {
            return response()->json([
                'error' => 'This expense cannot be rejected',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $expense->reject(auth()->id(), $validated['reason']);
        $expense->load(['category', 'vendor', 'creator', 'approver']);

        return response()->json([
            'message' => 'Expense rejected',
            'data' => $expense->toApiArray(),
        ]);
    }

    /**
     * Mark expense as paid.
     */
    public function markPaid(Expense $expense): JsonResponse
    {
        if (!$expense->canBePaid()) {
            return response()->json([
                'error' => 'Only approved expenses can be marked as paid',
            ], 422);
        }

        $expense->markAsPaid();
        $expense->load(['category', 'vendor', 'creator', 'approver']);

        return response()->json([
            'message' => 'Expense marked as paid',
            'data' => $expense->toApiArray(),
        ]);
    }

    /**
     * Upload receipt for an expense.
     */
    public function uploadReceipt(Request $request, Expense $expense): JsonResponse
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Delete old receipt if exists
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $path = $request->file('receipt')->store('expenses/receipts', 'public');

        $expense->update(['receipt_path' => $path]);

        return response()->json([
            'message' => 'Receipt uploaded successfully',
            'data' => [
                'receipt_url' => $expense->receipt_url,
            ],
        ]);
    }

    /**
     * Get expense categories.
     */
    public function categories(): JsonResponse
    {
        $categories = ExpenseCategory::active()
            ->ordered()
            ->with('children')
            ->root()
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'name_ar' => $category->name_ar,
                    'code' => $category->code,
                    'children' => $category->children->map(fn($child) => [
                        'id' => $child->id,
                        'name' => $child->name,
                        'name_ar' => $child->name_ar,
                        'code' => $child->code,
                    ]),
                ];
            });

        return response()->json(['data' => $categories]);
    }

    /**
     * Get flat list of categories for dropdown.
     */
    public function categoriesFlat(): JsonResponse
    {
        $categories = ExpenseCategory::active()
            ->ordered()
            ->get()
            ->map(fn($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'name_ar' => $category->name_ar,
                'code' => $category->code,
                'parent_id' => $category->parent_id,
            ]);

        return response()->json(['data' => $categories]);
    }

    /**
     * Get vendors for expense dropdown.
     */
    public function vendors(): JsonResponse
    {
        $vendors = Vendor::active()
            ->orderBy('name')
            ->get()
            ->map(fn($vendor) => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'code' => $vendor->code,
            ]);

        return response()->json(['data' => $vendors]);
    }
}
