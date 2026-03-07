<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Reservation\StoreReservationRequest;
use App\Http\Requests\Api\Reservation\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\TableReservation;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TableReservationController extends Controller
{
    use HasListing;

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Restaurant/Reservations/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        return $this->getListing(
            $request,
            TableReservation::class,
            with: ['table', 'customer', 'createdBy'],
            resource: ReservationResource::class,
            options: [
                'searchColumns' => ['customer_name', 'customer_phone', 'customer.name'],
                'filterColumns' => [
                    'status' => 'exact',
                    'table_id' => 'exact',
                ],
                'defaultSort' => 'reservation_date',
                'defaultSortDir' => 'desc',
                'preFilter' => function ($query, $request) {
                    if ($request->date) {
                        $query->whereDate('reservation_date', $request->date);
                    }
                    if ($request->from_date && $request->to_date) {
                        $query->whereBetween('reservation_date', [$request->from_date, $request->to_date]);
                    }
                    if ($request->upcoming_only) {
                        $query->upcoming();
                    }
                },
            ]
        );
    }

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['store_id'] = auth()->user()->store_id ?? 1;
        $data['created_by'] = auth()->id();
        $data['status'] = TableReservation::STATUS_PENDING;

        $reservation = TableReservation::create($data);

        return response()->json([
            'data' => new ReservationResource($reservation->load(['table', 'customer'])),
            'notifications' => [['type' => 'success', 'message' => 'Reservation created successfully']],
        ], 201);
    }

    public function update(UpdateReservationRequest $request, TableReservation $tableReservation): JsonResponse
    {
        $tableReservation->update($request->validated());

        return response()->json([
            'data' => new ReservationResource($tableReservation->fresh()->load(['table', 'customer'])),
            'notifications' => [['type' => 'success', 'message' => 'Reservation updated successfully']],
        ]);
    }

    public function destroy(TableReservation $tableReservation): JsonResponse
    {
        $tableReservation->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Reservation deleted successfully']],
        ]);
    }

    public function confirm(TableReservation $tableReservation): JsonResponse
    {
        if (!$tableReservation->confirm()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot confirm this reservation']],
            ], 422);
        }

        return response()->json([
            'data' => new ReservationResource($tableReservation->fresh()),
            'notifications' => [['type' => 'success', 'message' => 'Reservation confirmed']],
        ]);
    }

    public function cancel(TableReservation $tableReservation): JsonResponse
    {
        if (!$tableReservation->cancel()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot cancel this reservation']],
            ], 422);
        }

        return response()->json([
            'data' => new ReservationResource($tableReservation->fresh()),
            'notifications' => [['type' => 'success', 'message' => 'Reservation cancelled']],
        ]);
    }

    public function complete(TableReservation $tableReservation): JsonResponse
    {
        if (!$tableReservation->complete()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot complete this reservation']],
            ], 422);
        }

        return response()->json([
            'data' => new ReservationResource($tableReservation->fresh()),
            'notifications' => [['type' => 'success', 'message' => 'Reservation completed']],
        ]);
    }

    public function noShow(TableReservation $tableReservation): JsonResponse
    {
        if (!$tableReservation->markNoShow()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot mark as no-show']],
            ], 422);
        }

        return response()->json([
            'data' => new ReservationResource($tableReservation->fresh()),
            'notifications' => [['type' => 'warning', 'message' => 'Reservation marked as no-show']],
        ]);
    }

    public function todaySummary(): JsonResponse
    {
        $today = today();

        return response()->json([
            'total' => TableReservation::forDate($today)->count(),
            'pending' => TableReservation::forDate($today)->pending()->count(),
            'confirmed' => TableReservation::forDate($today)->confirmed()->count(),
            'completed' => TableReservation::forDate($today)->where('status', 'completed')->count(),
            'no_show' => TableReservation::forDate($today)->where('status', 'no_show')->count(),
            'upcoming' => ReservationResource::collection(
                TableReservation::forDate($today)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->where('start_time', '>=', now()->format('H:i'))
                    ->orderBy('start_time')
                    ->with('table')
                    ->limit(5)
                    ->get()
            ),
        ]);
    }
}
