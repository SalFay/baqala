<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'po_number' => $this->po_number,
            'status' => $this->status,
            'current_status' => StatusResource::make($this->whenLoaded('currentStatus')),
            'status_name' => $this->status_name,
            'status_color' => $this->status_color,
            'order_date' => $this->order_date?->toDateString(),
            'expected_date' => $this->expected_date?->toDateString(),
            'received_date' => $this->received_date?->toDateString(),
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'discount' => (float) $this->discount,
            'shipping_cost' => (float) $this->shipping_cost,
            'total' => (float) $this->total,
            'notes' => $this->notes,
            'vendor_notes' => $this->vendor_notes,
            'vendor_invoice_number' => $this->vendor_invoice_number,
            'store_id' => $this->store_id,
            'store' => $this->whenLoaded('store'),
            'vendor_id' => $this->vendor_id,
            'vendor' => VendorResource::make($this->whenLoaded('vendor')),
            'created_by' => UserResource::make($this->whenLoaded('createdBy')),
            'approved_by' => UserResource::make($this->whenLoaded('approvedBy')),
            'items' => PurchaseOrderItemResource::collection($this->whenLoaded('items')),
            'receipts' => $this->whenLoaded('receipts'),
            'status_histories' => StatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
