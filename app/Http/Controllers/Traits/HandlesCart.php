<?php

namespace App\Http\Controllers\Traits;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Trait for handling cart operations across controllers
 */
trait HandlesCart
{
    protected CartService $cartService;

    /**
     * Add item to cart
     */
    protected function addToCartAction(Request $request, int $customerId): JsonResponse
    {
        $this->cartService->setCustomerId($customerId);

        $id = $request->id;
        $stock = $request->stock;
        $price = $request->sale_price ?? $request->purchase_price;
        $pprice = $request->purchase_price ?? 0;
        $taxable_price = $request->taxable_price ?? 0;

        if (isset($request->price)) {
            $taxable_price = $taxable_price != 0 ? $taxable_price : $request->price;
            $this->cartService->updateWithPrice(
                $id,
                $stock,
                $request->price,
                ['purchase_price' => $pprice, 'taxable_price' => $taxable_price]
            );
        } else {
            $this->cartService->add(
                $id,
                $stock,
                $price,
                ['purchase_price' => $pprice, 'taxable_price' => $taxable_price]
            );
        }

        return response()->json(['status' => 'ok', 'message' => 'Item Added to Cart'], 200);
    }

    /**
     * Remove item from cart
     */
    protected function deleteFromCartAction(Request $request, int $customerId): JsonResponse
    {
        $this->cartService->setCustomerId($customerId);
        $this->cartService->remove($request->id);

        return response()->json(['status' => 'ok', 'message' => 'Product Deleted from Cart'], 200);
    }

    /**
     * Empty entire cart
     */
    protected function emptyCartAction(int $customerId): JsonResponse
    {
        $this->cartService->setCustomerId($customerId);
        $this->cartService->trash();

        return response()->json(['status' => 'ok', 'message' => 'Cart Deleted Successfully'], 200);
    }

    /**
     * Get cart content
     */
    protected function getCartContent(int $customerId): array
    {
        $this->cartService->setCustomerId($customerId);
        return $this->cartService->getContent();
    }

    /**
     * Clear cart after successful operation
     */
    protected function clearCart(int $customerId): void
    {
        $this->cartService->setCustomerId($customerId);
        $this->cartService->trash();
    }
}
