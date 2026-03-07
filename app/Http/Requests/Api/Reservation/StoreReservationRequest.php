<?php

namespace App\Http\Requests\Api\Reservation;

use App\Models\RestaurantTable;
use App\Models\TableReservation;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_id' => ['required', 'exists:restaurant_tables,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['required_without:customer_id', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'party_size' => ['required', 'integer', 'min:1'],
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

            // Check table capacity
            $table = RestaurantTable::find($this->table_id);
            if ($table && $this->party_size > $table->capacity) {
                $validator->errors()->add('party_size', "Party size exceeds table capacity of {$table->capacity}");
            }

            // Check for conflicts
            if (TableReservation::hasConflict(
                $this->table_id,
                $this->reservation_date,
                $this->start_time,
                $this->end_time
            )) {
                $validator->errors()->add('table_id', 'Table is already reserved for this time');
            }
        });
    }
}
