<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'expense_date' => $this->expense_date?->toDateString(),
            'expense_category_id' => $this->expense_category_id,
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'vendor_id' => $this->vendor_id,
            'vendor' => $this->whenLoaded('vendor', fn() => [
                'id' => $this->vendor?->id,
                'name' => $this->vendor?->name,
            ]),
            'amount' => (float) $this->amount,
            'tax_amount' => (float) $this->tax_amount,
            'total' => (float) $this->total,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'reference_number' => $this->reference_number,
            'description' => $this->description,
            'is_recurring' => (bool) $this->is_recurring,
            'recurring_frequency' => $this->recurring_frequency,
            'receipt_path' => $this->receipt_path,
            'receipt_url' => $this->receipt_url,
            'rejection_reason' => $this->rejection_reason,
            'created_by' => $this->whenLoaded('creator', fn() => $this->creator?->name),
            'approved_by' => $this->whenLoaded('approver', fn() => $this->approver?->name),
            'approved_at' => $this->approved_at?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
