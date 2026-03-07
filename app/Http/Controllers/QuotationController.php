<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Quotation\StoreQuotationRequest;
use App\Http\Requests\Api\Quotation\UpdateQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Models\Quotation;
use App\Services\QuotationService;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuotationController extends Controller
{
    use HasListing;

    public function __construct(
        protected QuotationService $quotationService
    ) {}

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Sales/Quotations/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        return $this->getListing(
            $request,
            Quotation::class,
            with: ['customer', 'createdBy', 'location'],
            resource: QuotationResource::class,
            options: [
                'searchColumns' => ['quotation_number', 'customer_name', 'customer.name'],
                'filterColumns' => [
                    'status' => 'exact',
                    'customer_id' => 'exact',
                    'location_id' => 'exact',
                ],
                'withCount' => ['items'],
                'defaultSort' => 'created_at',
                'defaultSortDir' => 'desc',
            ]
        );
    }

    public function form(Request $request, ?Quotation $quotation = null): Response
    {
        return Inertia::render('Sales/Quotations/Form', [
            'quotation' => $quotation ? new QuotationResource($quotation->load(['items.product', 'customer'])) : null,
        ]);
    }

    public function show(Quotation $quotation): JsonResponse
    {
        return response()->json([
            'data' => new QuotationResource($quotation->load(['items.product', 'customer', 'createdBy', 'location'])),
        ]);
    }

    public function store(StoreQuotationRequest $request): JsonResponse
    {
        $quotation = $this->quotationService->createQuotation($request->validated());

        return response()->json([
            'data' => new QuotationResource($quotation),
            'notifications' => [['type' => 'success', 'message' => 'Quotation created successfully']],
        ], 201);
    }

    public function update(UpdateQuotationRequest $request, Quotation $quotation): JsonResponse
    {
        $quotation = $this->quotationService->updateQuotation($quotation, $request->validated());

        return response()->json([
            'data' => new QuotationResource($quotation),
            'notifications' => [['type' => 'success', 'message' => 'Quotation updated successfully']],
        ]);
    }

    public function destroy(Quotation $quotation): JsonResponse
    {
        $quotation->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Quotation deleted']],
        ]);
    }

    public function markAsSent(Quotation $quotation): JsonResponse
    {
        if (!$quotation->markAsSent()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot mark this quotation as sent']],
            ], 422);
        }

        return response()->json([
            'data' => new QuotationResource($quotation->fresh()),
            'notifications' => [['type' => 'success', 'message' => 'Quotation marked as sent']],
        ]);
    }

    public function accept(Quotation $quotation): JsonResponse
    {
        if (!$quotation->markAsAccepted()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot accept this quotation']],
            ], 422);
        }

        return response()->json([
            'data' => new QuotationResource($quotation->fresh()),
            'notifications' => [['type' => 'success', 'message' => 'Quotation accepted']],
        ]);
    }

    public function reject(Quotation $quotation): JsonResponse
    {
        if (!$quotation->markAsRejected()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot reject this quotation']],
            ], 422);
        }

        return response()->json([
            'data' => new QuotationResource($quotation->fresh()),
            'notifications' => [['type' => 'success', 'message' => 'Quotation rejected']],
        ]);
    }

    public function convertToOrder(Quotation $quotation): JsonResponse
    {
        $order = $quotation->convertToOrder();

        if (!$order) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot convert this quotation to order']],
            ], 422);
        }

        return response()->json([
            'data' => [
                'quotation' => new QuotationResource($quotation->fresh()),
                'order' => $order,
            ],
            'notifications' => [['type' => 'success', 'message' => 'Quotation converted to order']],
        ]);
    }

    public function duplicate(Quotation $quotation): JsonResponse
    {
        $newQuotation = $this->quotationService->duplicate($quotation);

        return response()->json([
            'data' => new QuotationResource($newQuotation),
            'notifications' => [['type' => 'success', 'message' => 'Quotation duplicated']],
        ], 201);
    }

    public function statistics(): JsonResponse
    {
        return response()->json(
            $this->quotationService->getStatistics(auth()->user()->store_id)
        );
    }

    public function print(Quotation $quotation): Response
    {
        return Inertia::render('Sales/Quotations/Print', [
            'quotation' => new QuotationResource($quotation->load(['items.product', 'customer', 'createdBy', 'location', 'store'])),
        ]);
    }
}
