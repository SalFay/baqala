<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'table_id' => $this->table_id,
            'table' => $this->whenLoaded('table', fn() => [
                'id' => $this->table?->id,
                'name' => $this->table?->name,
                'section' => $this->table?->section,
            ]),
            'customer_id' => $this->customer_id,
            'guest_name' => $this->guest_name,
            'customer_phone' => $this->customer_phone ?? $this->customer?->phone,
            'customer_email' => $this->customer_email ?? $this->customer?->email,
            'reservation_date' => $this->reservation_date?->toDateString(),
            'start_time' => $this->start_time?->format('H:i'),
            'end_time' => $this->end_time?->format('H:i'),
            'party_size' => (int) $this->party_size,
            'status' => $this->status,
            'status_color' => $this->status_color,
            'special_requests' => $this->special_requests,
            'notes' => $this->notes,
            'confirmed_at' => $this->confirmed_at?->toISOString(),
            'created_by' => $this->whenLoaded('createdBy', fn() => $this->createdBy?->name),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
