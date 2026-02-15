<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreditRequest;
use App\Http\Requests\StatementRequest;
use App\Http\Resources\CreditResource;
use App\Models\Credit;
use App\Models\Customer;
use App\Models\Vendor;
use App\Services\Pdf\PdfService;
use App\Services\Statement\StatementService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class StatementController extends Controller
{
    public function __construct(
        private StatementService $statementService,
        private PdfService $pdfService
    ) {}

    /**
     * Get customer statement.
     */
    public function customerStatement(StatementRequest $request, Customer $customer): JsonResponse
    {
        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : null;
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : null;

        $statement = $this->statementService->getCustomerStatement($customer, $fromDate, $toDate);

        return response()->json($statement);
    }

    /**
     * Get vendor statement.
     */
    public function vendorStatement(StatementRequest $request, Vendor $vendor): JsonResponse
    {
        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : null;
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : null;

        $statement = $this->statementService->getVendorStatement($vendor, $fromDate, $toDate);

        return response()->json($statement);
    }

    /**
     * Get customer credit history.
     */
    public function customerCredits(Customer $customer): JsonResponse
    {
        $credits = $customer->credits()
            ->with('createdBy')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'data' => CreditResource::collection($credits),
            'meta' => [
                'current_page' => $credits->currentPage(),
                'last_page' => $credits->lastPage(),
                'per_page' => $credits->perPage(),
                'total' => $credits->total(),
            ],
        ]);
    }

    /**
     * Get vendor credit history.
     */
    public function vendorCredits(Vendor $vendor): JsonResponse
    {
        $credits = $vendor->credits()
            ->with('createdBy')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'data' => CreditResource::collection($credits),
            'meta' => [
                'current_page' => $credits->currentPage(),
                'last_page' => $credits->lastPage(),
                'per_page' => $credits->perPage(),
                'total' => $credits->total(),
            ],
        ]);
    }

    /**
     * Add credit to customer.
     */
    public function addCustomerCredit(CreditRequest $request, Customer $customer): JsonResponse
    {
        $credit = $request->type === 'credit'
            ? $customer->addCredit($request->amount, $request->reference, $request->notes)
            : $customer->useCredit($request->amount, $request->reference, $request->notes);

        return response()->json([
            'message' => 'Credit transaction added successfully',
            'data' => new CreditResource($credit->load('createdBy')),
            'current_balance' => (float) $customer->fresh()->credit_balance,
        ]);
    }

    /**
     * Add credit to vendor.
     */
    public function addVendorCredit(CreditRequest $request, Vendor $vendor): JsonResponse
    {
        $credit = $request->type === 'credit'
            ? $vendor->addCredit($request->amount, $request->reference, $request->notes)
            : $vendor->addDebit($request->amount, $request->reference, $request->notes);

        return response()->json([
            'message' => 'Credit transaction added successfully',
            'data' => new CreditResource($credit->load('createdBy')),
            'current_balance' => (float) $vendor->fresh()->balance,
        ]);
    }

    /**
     * Download customer statement as PDF.
     */
    public function customerStatementPdf(StatementRequest $request, Customer $customer): Response
    {
        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : null;
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : null;

        $statement = $this->statementService->getCustomerStatement($customer, $fromDate, $toDate);

        return $this->pdfService->customerStatement($statement);
    }

    /**
     * Download vendor statement as PDF.
     */
    public function vendorStatementPdf(StatementRequest $request, Vendor $vendor): Response
    {
        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : null;
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : null;

        $statement = $this->statementService->getVendorStatement($vendor, $fromDate, $toDate);

        return $this->pdfService->vendorStatement($statement);
    }
}
