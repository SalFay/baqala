<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Expense\StoreExpenseRequest;
use App\Http\Requests\Api\Expense\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Http\Resources\VendorResource;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Store;
use App\Models\Vendor;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    use HasListing;

    public function index(Request $request): JsonResponse
    {
        if ($request->wantsJson() && $request->has('pageSize')) {
            return $this->listing($request);
        }

        $store = Store::first();

        $query = Expense::with(['category', 'vendor', 'creator', 'approver'])
            ->forStore($store->id)
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc');

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

        $perPage = $request->input('per_page', 20);
        $expenses = $query->paginate($perPage);

        return response()->json([
            'data' => ExpenseResource::collection($expenses),
            'meta' => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
            ],
        ]);
    }

    public function listing(Request $request): JsonResponse
    {
        $store = Store::first();

        return $this->getListing(
            $request,
            Expense::class,
            with: ['category', 'vendor', 'creator'],
            resource: ExpenseResource::class,
            options: [
                'searchColumns' => ['description', 'reference_number'],
                'filterColumns' => [
                    'status' => 'exact',
                    'expense_category_id' => 'exact',
                    'vendor_id' => 'exact',
                    'payment_method' => 'exact',
                ],
                'defaultSort' => 'expense_date',
                'defaultSortDir' => 'desc',
                'preFilter' => function ($query) use ($store) {
                    $query->forStore($store->id);
                },
            ]
        );
    }

    public function summary(): JsonResponse
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
                'approved_this_month' => (float) Expense::forStore($store->id)->thisMonth()->approved()->sum('total'),
                'paid_this_month' => (float) Expense::forStore($store->id)->thisMonth()->paid()->sum('total'),
            ],
        ]);
    }

    public function show(Expense $expense): JsonResponse
    {
        $expense->load(['category', 'vendor', 'creator', 'approver', 'account']);

        return response()->json([
            'data' => new ExpenseResource($expense),
        ]);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $data = $request->validated();
        $store = Store::first();

        $expense = Expense::create([
            ...$data,
            'store_id' => $store->id,
            'created_by' => auth()->id(),
            'tax_amount' => $data['tax_amount'] ?? 0,
            'total' => $data['amount'] + ($data['tax_amount'] ?? 0),
            'status' => $data['status'] ?? Expense::STATUS_PENDING,
        ]);

        $expense->load(['category', 'vendor', 'creator']);

        return response()->json([
            'data' => new ExpenseResource($expense),
            'notifications' => [['type' => 'success', 'message' => 'Expense created successfully']],
        ], 201);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['amount']) || isset($data['tax_amount'])) {
            $amount = $data['amount'] ?? $expense->amount;
            $taxAmount = $data['tax_amount'] ?? $expense->tax_amount;
            $data['total'] = $amount + $taxAmount;
        }

        if ($expense->isRejected()) {
            $data['status'] = Expense::STATUS_PENDING;
            $data['rejection_reason'] = null;
        }

        $expense->update($data);
        $expense->load(['category', 'vendor', 'creator', 'approver']);

        return response()->json([
            'data' => new ExpenseResource($expense),
            'notifications' => [['type' => 'success', 'message' => 'Expense updated successfully']],
        ]);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        if ($expense->isPaid()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Paid expenses cannot be deleted']],
            ], 422);
        }

        $expense->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Expense deleted successfully']],
        ]);
    }

    public function approve(Expense $expense): JsonResponse
    {
        if (!$expense->canBeApproved()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'This expense cannot be approved']],
            ], 422);
        }

        $expense->approve(auth()->id());
        $expense->load(['category', 'vendor', 'creator', 'approver']);

        return response()->json([
            'data' => new ExpenseResource($expense),
            'notifications' => [['type' => 'success', 'message' => 'Expense approved successfully']],
        ]);
    }

    public function reject(Request $request, Expense $expense): JsonResponse
    {
        if (!$expense->canBeRejected()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'This expense cannot be rejected']],
            ], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $expense->reject(auth()->id(), $validated['reason']);
        $expense->load(['category', 'vendor', 'creator', 'approver']);

        return response()->json([
            'data' => new ExpenseResource($expense),
            'notifications' => [['type' => 'warning', 'message' => 'Expense rejected']],
        ]);
    }

    public function markPaid(Expense $expense): JsonResponse
    {
        if (!$expense->canBePaid()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Only approved expenses can be marked as paid']],
            ], 422);
        }

        $expense->markAsPaid();
        $expense->load(['category', 'vendor', 'creator', 'approver']);

        return response()->json([
            'data' => new ExpenseResource($expense),
            'notifications' => [['type' => 'success', 'message' => 'Expense marked as paid']],
        ]);
    }

    public function uploadReceipt(Request $request, Expense $expense): JsonResponse
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $path = $request->file('receipt')->store('expenses/receipts', 'public');
        $expense->update(['receipt_path' => $path]);

        return response()->json([
            'data' => ['receipt_url' => $expense->receipt_url],
            'notifications' => [['type' => 'success', 'message' => 'Receipt uploaded successfully']],
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = ExpenseCategory::active()
            ->ordered()
            ->with('children')
            ->root()
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function categoriesFlat(): JsonResponse
    {
        $categories = ExpenseCategory::active()
            ->ordered()
            ->get(['id', 'name', 'name_ar', 'code', 'parent_id']);

        return response()->json(['data' => $categories]);
    }

    public function vendors(): JsonResponse
    {
        $vendors = Vendor::active()->orderBy('name')->get();

        return response()->json([
            'data' => VendorResource::collection($vendors),
        ]);
    }
}
