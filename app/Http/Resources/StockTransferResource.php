<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transfer_number' => $this->transfer_number,
            'status' => $this->status,
            'current_status' => StatusResource::make($this->whenLoaded('currentStatus')),
            'status_name' => $this->status_name,
            'status_color' => $this->status_color,
            'notes' => $this->notes,
            'shipped_at' => $this->shipped_at?->toISOString(),
            'received_at' => $this->received_at?->toISOString(),
            'from_store_id' => $this->from_store_id,
            'from_store' => $this->whenLoaded('fromStore'),
            'to_store_id' => $this->to_store_id,
            'to_store' => $this->whenLoaded('toStore'),
            'created_by' => UserResource::make($this->whenLoaded('createdBy')),
            'approved_by' => UserResource::make($this->whenLoaded('approvedBy')),
            'received_by' => UserResource::make($this->whenLoaded('receivedBy')),
            'items' => StockTransferItemResource::collection($this->whenLoaded('items')),
            'status_histories' => StatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
