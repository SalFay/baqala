<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'creditable_type' => class_basename($this->creditable_type),
            'creditable_id' => $this->creditable_id,
            'amount' => (float) $this->amount,
            'type' => $this->type,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'balance_after' => (float) $this->balance_after,
            'created_by' => $this->whenLoaded('createdBy', fn() => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ]),
            'creditable' => $this->whenLoaded('creditable', function () {
                $creditable = $this->creditable;
                return [
                    'id' => $creditable->id,
                    'name' => $creditable->full_name ?? $creditable->name,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
