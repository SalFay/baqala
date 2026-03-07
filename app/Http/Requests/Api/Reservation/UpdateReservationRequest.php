<?php

namespace App\Http\Requests\Api\Reservation;

use App\Models\TableReservation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_id' => ['sometimes', 'exists:restaurant_tables,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['sometimes', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'reservation_date' => ['sometimes', 'date'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'party_size' => ['sometimes', 'integer', 'min:1'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $reservation = $this->route('tableReservation');
            if (!$reservation) {
                return;
            }

            // Check for conflicts if date/time/table changed
            if ($this->has('table_id') || $this->has('reservation_date') || $this->has('start_time')) {
                $tableId = $this->table_id ?? $reservation->table_id;
                $date = $this->reservation_date ?? $reservation->reservation_date;
                $startTime = $this->start_time ?? $reservation->start_time;
                $endTime = $this->end_time ?? $reservation->end_time;

                if (TableReservation::hasConflict($tableId, $date, $startTime, $endTime, $reservation->id)) {
                    $validator->errors()->add('table_id', 'Table is already reserved for this time');
                }
            }
        });
    }
}
