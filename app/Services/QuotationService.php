<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    /**
     * Create a new quotation
     */
    public function createQuotation(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {
            $quotation = Quotation::create([
                'store_id' => $data['store_id'] ?? auth()->user()->store_id ?? 1,
                'location_id' => $data['location_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'notes' => $data['notes'] ?? null,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
                'discount_percent' => $data['discount_percent'] ?? null,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'created_by' => auth()->id(),
            ]);

            // Add items
            if (!empty($data['items'])) {
                $this->addItems($quotation, $data['items']);
            }

            return $quotation->fresh(['items', 'customer']);
        });
    }

    /**
     * Update a quotation
     */
    public function updateQuotation(Quotation $quotation, array $data): Quotation
    {
        if (!$quotation->is_editable) {
            throw new \Exception('This quotation cannot be edited');
        }

        return DB::transaction(function () use ($quotation, $data) {
            $quotation->update([
                'customer_id' => $data['customer_id'] ?? $quotation->customer_id,
                'customer_name' => $data['customer_name'] ?? $quotation->customer_name,
                'customer_email' => $data['customer_email'] ?? $quotation->customer_email,
                'customer_phone' => $data['customer_phone'] ?? $quotation->customer_phone,
                'valid_until' => $data['valid_until'] ?? $quotation->valid_until,
                'notes' => $data['notes'] ?? $quotation->notes,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? $quotation->terms_and_conditions,
                'discount_percent' => $data['discount_percent'] ?? $quotation->discount_percent,
                'discount_amount' => $data['discount_amount'] ?? $quotation->discount_amount,
            ]);

            // Update items if provided
            if (isset($data['items'])) {
                // Remove existing items
                $quotation->items()->delete();
                // Add new items
                $this->addItems($quotation, $data['items']);
            }

            return $quotation->fresh(['items', 'customer']);
        });
    }

    /**
     * Add items to quotation
     */
    protected function addItems(Quotation $quotation, array $items): void
    {
        foreach ($items as $index => $itemData) {
            $product = null;
            if (!empty($itemData['product_id'])) {
                $product = Product::find($itemData['product_id']);
            }

            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'product_id' => $itemData['product_id'] ?? null,
                'product_variant_id' => $itemData['product_variant_id'] ?? null,
                'product_name' => $itemData['product_name'] ?? $product?->name ?? 'Unknown',
                'product_sku' => $itemData['product_sku'] ?? $product?->sku ?? null,
                'quantity' => $itemData['quantity'] ?? 1,
                'unit_price' => $itemData['unit_price'] ?? $product?->selling_price ?? 0,
                'discount' => $itemData['discount'] ?? 0,
                'discount_percent' => $itemData['discount_percent'] ?? null,
                'tax_rate' => $itemData['tax_rate'] ?? $product?->tax_rate ?? 0,
                'notes' => $itemData['notes'] ?? null,
                'sort_order' => $itemData['sort_order'] ?? $index,
            ]);
        }
    }

    /**
     * Add single item to quotation
     */
    public function addItem(Quotation $quotation, array $itemData): QuotationItem
    {
        if (!$quotation->is_editable) {
            throw new \Exception('This quotation cannot be edited');
        }

        $product = null;
        if (!empty($itemData['product_id'])) {
            $product = Product::find($itemData['product_id']);
        }

        $maxSort = $quotation->items()->max('sort_order') ?? 0;

        return QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $itemData['product_id'] ?? null,
            'product_variant_id' => $itemData['product_variant_id'] ?? null,
            'product_name' => $itemData['product_name'] ?? $product?->name ?? 'Unknown',
            'product_sku' => $itemData['product_sku'] ?? $product?->sku ?? null,
            'quantity' => $itemData['quantity'] ?? 1,
            'unit_price' => $itemData['unit_price'] ?? $product?->selling_price ?? 0,
            'discount' => $itemData['discount'] ?? 0,
            'discount_percent' => $itemData['discount_percent'] ?? null,
            'tax_rate' => $itemData['tax_rate'] ?? $product?->tax_rate ?? 0,
            'notes' => $itemData['notes'] ?? null,
            'sort_order' => $maxSort + 1,
        ]);
    }

    /**
     * Update item
     */
    public function updateItem(QuotationItem $item, array $data): QuotationItem
    {
        if (!$item->quotation->is_editable) {
            throw new \Exception('This quotation cannot be edited');
        }

        $item->update($data);
        return $item->fresh();
    }

    /**
     * Remove item
     */
    public function removeItem(QuotationItem $item): bool
    {
        if (!$item->quotation->is_editable) {
            throw new \Exception('This quotation cannot be edited');
        }

        return $item->delete();
    }

    /**
     * Duplicate quotation
     */
    public function duplicate(Quotation $quotation): Quotation
    {
        return DB::transaction(function () use ($quotation) {
            $newQuotation = $quotation->replicate([
                'quotation_number',
                'status',
                'converted_order_id',
                'converted_at',
                'sent_by',
                'sent_at',
                'created_at',
                'updated_at',
            ]);

            $newQuotation->status = Quotation::STATUS_DRAFT;
            $newQuotation->created_by = auth()->id();
            $newQuotation->save();

            // Copy items
            foreach ($quotation->items as $item) {
                $newItem = $item->replicate(['quotation_id', 'created_at', 'updated_at']);
                $newItem->quotation_id = $newQuotation->id;
                $newItem->save();
            }

            return $newQuotation->fresh(['items', 'customer']);
        });
    }

    /**
     * Get statistics
     */
    public function getStatistics(?int $storeId = null): array
    {
        $query = Quotation::query();

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $thisMonth = now()->startOfMonth();

        return [
            'total' => (clone $query)->count(),
            'draft' => (clone $query)->draft()->count(),
            'sent' => (clone $query)->sent()->count(),
            'accepted' => (clone $query)->accepted()->count(),
            'expired' => (clone $query)->expired()->count(),
            'expiring_soon' => (clone $query)->expiring()->count(),
            'this_month' => [
                'count' => (clone $query)->where('created_at', '>=', $thisMonth)->count(),
                'total' => (clone $query)->where('created_at', '>=', $thisMonth)->sum('total'),
                'converted' => (clone $query)->where('created_at', '>=', $thisMonth)
                    ->where('status', Quotation::STATUS_CONVERTED)
                    ->count(),
            ],
            'conversion_rate' => $this->calculateConversionRate($query),
        ];
    }

    /**
     * Calculate conversion rate
     */
    protected function calculateConversionRate($query): float
    {
        $total = (clone $query)->whereIn('status', [
            Quotation::STATUS_ACCEPTED,
            Quotation::STATUS_REJECTED,
            Quotation::STATUS_CONVERTED,
            Quotation::STATUS_EXPIRED,
        ])->count();

        if ($total === 0) {
            return 0;
        }

        $converted = (clone $query)->whereIn('status', [
            Quotation::STATUS_ACCEPTED,
            Quotation::STATUS_CONVERTED,
        ])->count();

        return round(($converted / $total) * 100, 1);
    }

    /**
     * Check and expire old quotations
     */
    public function expireOldQuotations(): int
    {
        $count = 0;
        $quotations = Quotation::sent()
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', today())
            ->get();

        foreach ($quotations as $quotation) {
            if ($quotation->checkExpiration()) {
                $count++;
            }
        }

        return $count;
    }
}
