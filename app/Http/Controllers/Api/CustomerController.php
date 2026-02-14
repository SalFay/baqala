<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Loyalty\LoyaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        protected LoyaltyService $loyaltyService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $customers = Customer::query()
            ->with('loyalty.tier')
            ->when($request->search, fn($q, $term) => $q->search($term))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderBy($request->sort_by ?? 'first_name', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 20);

        return response()->json($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'business_name' => 'nullable|string|max:200',
            'email' => 'nullable|email|unique:customers,email',
            'phone_mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'accepts_marketing' => 'boolean',
        ]);

        $customer = Customer::create($validated);

        // Create loyalty record
        $customer->getOrCreateLoyalty();

        return response()->json($customer->load('loyalty'), 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json($customer->load(['loyalty.tier', 'orders' => function ($q) {
            $q->latest()->limit(10);
        }]));
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'business_name' => 'nullable|string|max:200',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone_mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'accepts_marketing' => 'boolean',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer->update($validated);

        return response()->json($customer->fresh('loyalty'));
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully']);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:1']);

        $customers = Customer::query()
            ->with('loyalty')
            ->search($request->q)
            ->limit(20)
            ->get();

        return response()->json($customers);
    }

    public function orders(Customer $customer): JsonResponse
    {
        $orders = $customer->orders()
            ->with(['items', 'user'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($orders);
    }

    public function loyalty(Customer $customer): JsonResponse
    {
        return response()->json($this->loyaltyService->getCustomerLoyaltyInfo($customer));
    }

    public function loyaltyTransactions(Customer $customer): JsonResponse
    {
        $transactions = $customer->loyalty?->transactions()
            ->with('store')
            ->orderByDesc('created_at')
            ->paginate(20) ?? collect();

        return response()->json($transactions);
    }

    public function adjustCredit(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'reason' => 'nullable|string',
        ]);

        $customer->credit_balance += $validated['amount'];
        $customer->save();

        return response()->json([
            'message' => 'Credit adjusted successfully',
            'credit_balance' => $customer->credit_balance,
        ]);
    }
}
