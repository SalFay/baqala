<?php

namespace App\Services\Inventory;

use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreInventory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function getStock(int $storeId, int $productId, ?int $variantId = null): int
    {
        return StoreInventory::query()
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->value('quantity') ?? 0;
    }

    public function recordMovement(
        int $storeId,
        int $productId,
        ?int $variantId,
        InventoryMovementType $type,
        int $quantity,
        ?float $unitCost = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $reason = null,
        ?string $notes = null
    ): InventoryMovement {
        return DB::transaction(function () use (
            $storeId, $productId, $variantId, $type, $quantity,
            $unitCost, $referenceType, $referenceId, $reason, $notes
        ) {
            // Get or create inventory record
            $inventory = StoreInventory::firstOrCreate(
                [
                    'store_id' => $storeId,
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                ],
                ['quantity' => 0]
            );

            $quantityBefore = $inventory->quantity;
            $quantityAfter = $quantityBefore + $quantity;

            // Update inventory
            $inventory->update(['quantity' => $quantityAfter]);

            // Record movement
            return InventoryMovement::create([
                'store_id' => $storeId,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'type' => $type,
                'quantity' => abs($quantity),
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'unit_cost' => $unitCost,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reason' => $reason,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);
        });
    }

    public function adjustStock(
        int $storeId,
        int $productId,
        ?int $variantId,
        int $newQuantity,
        ?string $reason = null
    ): InventoryMovement {
        $currentStock = $this->getStock($storeId, $productId, $variantId);
        $difference = $newQuantity - $currentStock;

        $type = $difference >= 0
            ? InventoryMovementType::ADJUSTMENT_ADD
            : InventoryMovementType::ADJUSTMENT_REMOVE;

        return $this->recordMovement(
            storeId: $storeId,
            productId: $productId,
            variantId: $variantId,
            type: $type,
            quantity: $difference,
            reason: $reason ?? 'Stock adjustment'
        );
    }

    public function setInitialStock(
        int $storeId,
        int $productId,
        ?int $variantId,
        int $quantity,
        ?float $unitCost = null
    ): InventoryMovement {
        return $this->recordMovement(
            storeId: $storeId,
            productId: $productId,
            variantId: $variantId,
            type: InventoryMovementType::INITIAL,
            quantity: $quantity,
            unitCost: $unitCost,
            reason: 'Initial stock'
        );
    }

    public function getLowStockProducts(int $storeId, ?int $threshold = null): \Illuminate\Database\Eloquent\Collection
    {
        return StoreInventory::query()
            ->with(['product', 'variant'])
            ->where('store_id', $storeId)
            ->when($threshold, function ($query, $threshold) {
                $query->where('quantity', '<=', $threshold);
            }, function ($query) {
                $query->whereRaw('quantity <= COALESCE(low_stock_threshold, 5)');
            })
            ->orderBy('quantity')
            ->get();
    }

    public function getMovementHistory(
        int $storeId,
        ?int $productId = null,
        ?int $variantId = null,
        ?string $fromDate = null,
        ?string $toDate = null,
        int $limit = 50
    ): \Illuminate\Database\Eloquent\Collection {
        return InventoryMovement::query()
            ->with(['product', 'variant', 'createdBy'])
            ->where('store_id', $storeId)
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($variantId, fn($q) => $q->where('product_variant_id', $variantId))
            ->when($fromDate, fn($q) => $q->whereDate('created_at', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('created_at', '<=', $toDate))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function stockCount(
        int $storeId,
        int $productId,
        ?int $variantId,
        int $countedQuantity
    ): InventoryMovement {
        $currentStock = $this->getStock($storeId, $productId, $variantId);
        $difference = $countedQuantity - $currentStock;

        // Update last counted timestamp
        StoreInventory::query()
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->update(['last_counted_at' => now()]);

        return $this->recordMovement(
            storeId: $storeId,
            productId: $productId,
            variantId: $variantId,
            type: InventoryMovementType::COUNT,
            quantity: $difference,
            reason: "Stock count: Expected {$currentStock}, Counted {$countedQuantity}"
        );
    }

    public function reserveStock(int $storeId, int $productId, ?int $variantId, int $quantity): bool
    {
        $inventory = StoreInventory::query()
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();

        if (!$inventory || $inventory->available_quantity < $quantity) {
            return false;
        }

        $inventory->increment('reserved_quantity', $quantity);

        return true;
    }

    public function releaseReservation(int $storeId, int $productId, ?int $variantId, int $quantity): void
    {
        StoreInventory::query()
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->decrement('reserved_quantity', $quantity);
    }
}
