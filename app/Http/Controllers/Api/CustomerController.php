<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\StoreCustomerRequest;
use App\Http\Requests\Api\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\OrderResource;
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

        return CustomerResource::collection($customers)->response();
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        // Create loyalty record if not exists
        if (method_exists($customer, 'getOrCreateLoyalty')) {
            $customer->getOrCreateLoyalty();
        }

        return CustomerResource::make($customer->load('loyalty'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return CustomerResource::make(
            $customer->load(['loyalty.tier', 'orders' => function ($q) {
                $q->latest()->limit(10);
            }])
        )->response();
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer->update($request->validated());

        return CustomerResource::make($customer->fresh('loyalty'))->response();
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

        return CustomerResource::collection($customers)->response();
    }

    public function orders(Customer $customer): JsonResponse
    {
        $orders = $customer->orders()
            ->with(['items', 'user', 'currentStatus'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return OrderResource::collection($orders)->response();
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
