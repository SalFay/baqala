<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'display_name' => $this->display_name,
            'business_name' => $this->business_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'tax_number' => $this->tax_number,
            'loyalty_card_number' => $this->loyalty_card_number,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'gender' => $this->gender,
            'accepts_marketing' => $this->accepts_marketing,
            'preferences' => $this->preferences,
            'credit_limit' => (float) $this->credit_limit,
            'credit_balance' => (float) $this->credit_balance,
            'available_credit' => $this->available_credit,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'loyalty_points' => $this->loyalty_points,
            'loyalty_tier' => $this->loyalty_tier,
            'customer_group_id' => $this->customer_group_id,
            'customer_group' => $this->whenLoaded('customerGroup', fn() => [
                'id' => $this->customerGroup?->id,
                'name' => $this->customerGroup?->name,
            ]),
            'total_orders' => $this->when($this->relationLoaded('orders'), $this->total_orders),
            'total_spent' => $this->when($this->relationLoaded('orders'), $this->total_spent),
            'loyalty' => $this->whenLoaded('loyalty'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
