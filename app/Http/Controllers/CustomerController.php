<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Customer\StoreCustomerRequest;
use App\Http\Requests\Api\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    use HasListing;

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Customers/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        return $this->getListing(
            $request,
            Customer::class,
            with: ['customerGroup'],
            resource: CustomerResource::class,
            options: [
                'searchColumns' => ['first_name', 'last_name', 'email', 'phone'],
                'filterColumns' => [
                    'status' => 'exact',
                    'customer_group_id' => 'exact',
                ],
                'defaultSort' => 'created_at',
                'defaultSortDir' => 'desc',
            ]
        );
    }

    public function show(Customer $customer, Request $request): Response|JsonResponse
    {
        $customer->load(['orders' => fn($q) => $q->latest()->limit(10)]);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new CustomerResource($customer),
            ]);
        }

        return Inertia::render('Customers/Show', [
            'customer' => new CustomerResource($customer),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Customers/Create');
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Edit', [
            'customer' => new CustomerResource($customer),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse|JsonResponse
    {
        $customer = Customer::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new CustomerResource($customer),
                'notifications' => [['type' => 'success', 'message' => 'Customer created successfully']],
            ], 201);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse|JsonResponse
    {
        $customer->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new CustomerResource($customer->fresh()),
                'notifications' => [['type' => 'success', 'message' => 'Customer updated successfully']],
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse|JsonResponse
    {
        $customer->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'notifications' => [['type' => 'success', 'message' => 'Customer deleted successfully']],
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
