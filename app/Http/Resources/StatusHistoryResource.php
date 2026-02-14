<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => StatusResource::make($this->whenLoaded('status')),
            'previous_status' => StatusResource::make($this->whenLoaded('previousStatus')),
            'reason' => $this->reason,
            'user' => UserResource::make($this->whenLoaded('user')),
            'is_system_change' => $this->is_system_change,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
