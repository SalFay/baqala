<?php

namespace App\Http\Controllers;
use App\Http\Requests\Api\Customer\StoreCustomerRequest;
use App\Http\Requests\Api\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        $customers = Customer::query()
            ->when($request->search, function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_dir ?? 'desc')
            ->paginate($request->per_page ?? 20);

        $customersData = $customers->map(fn($customer) => [
            'id' => $customer->id,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'full_name' => $customer->full_name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'loyalty_points' => $customer->loyalty_points,
            'credit_balance' => $customer->credit_balance ?? 0,
            'status' => $customer->status,
            'created_at' => $customer->created_at,
        ]);

        // Return JSON only for non-Inertia API requests
        if (!$request->header('X-Inertia') && ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest')) {
            return response()->json([
                'data' => $customersData,
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ]);
        }

        return Inertia::render('Customers/Index', [
            'customers' => [
                'data' => $customersData,
                'meta' => [
                    'total' => $customers->total(),
                    'per_page' => $customers->perPage(),
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                ],
            ],
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Server-side listing for DataGridTable
     */
    public function listing(Request $request): JsonResponse
    {
        $query = Customer::query();

        // Search
        if ($request->search) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        // Soft deleted filter
        if ($request->soft_deleted) {
            $query->onlyTrashed();
        }

        // Sorting
        if ($request->sort && count($request->sort) > 0) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $customers = $query
            ->with('customerGroup')
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($customer) => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'loyalty_points' => $customer->loyalty_points,
                'credit_balance' => $customer->credit_balance ?? 0,
                'customer_group_id' => $customer->customer_group_id,
                'customer_group' => $customer->customerGroup ? [
                    'id' => $customer->customerGroup->id,
                    'name' => $customer->customerGroup->name,
                ] : null,
                'status' => $customer->status,
                'created_at' => $customer->created_at,
            ]);

        return response()->json([
            'data' => $customers,
            'total' => $total,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Customers/Create');
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Edit', [
            'customer' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'city' => $customer->city,
                'loyalty_points' => $customer->loyalty_points,
                'credit_limit' => $customer->credit_limit,
                'status' => $customer->status,
            ],
        ]);
    }

    public function show(Customer $customer, Request $request): Response|JsonResponse
    {
        $customer->load(['orders' => fn($q) => $q->latest()->limit(10)]);

        $customerData = [
            'id' => $customer->id,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'full_name' => $customer->full_name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address' => $customer->address,
            'city' => $customer->city,
            'loyalty_points' => $customer->loyalty_points,
            'credit_limit' => $customer->credit_limit,
            'credit_balance' => $customer->credit_balance ?? 0,
            'status' => $customer->status,
            'recent_orders' => $customer->orders->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'status' => $order->status,
                'created_at' => $order->created_at,
            ]),
            'created_at' => $customer->created_at,
        ];

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['data' => $customerData]);
        }

        return Inertia::render('Customers/Show', [
            'customer' => $customerData,
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse|JsonResponse
    {
        $customer = Customer::create($request->validated());

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'data' => $customer,
                'message' => 'Customer created successfully.',
            ], 201);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse|JsonResponse
    {
        $customer->update($request->validated());

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'data' => $customer->fresh(),
                'message' => 'Customer updated successfully.',
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse|JsonResponse
    {
        $customer->delete();

        if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'message' => 'Customer deleted successfully.',
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
