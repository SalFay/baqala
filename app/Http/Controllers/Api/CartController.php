<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request->store_id);

        return response()->json([
            'cart' => $cart->load(['items.product', 'items.variant', 'customer']),
            'summary' => $this->cartService->getCartSummary(),
        ]);
    }

    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $variant = isset($validated['variant_id'])
            ? ProductVariant::find($validated['variant_id'])
            : null;

        $item = $this->cartService->addItem(
            $product,
            $validated['quantity'] ?? 1,
            $variant,
            $validated['store_id'] ?? null
        );

        $cart = $this->cartService->getCart();

        return response()->json([
            'item' => $item->load(['product', 'variant']),
            'cart' => $cart->load(['items.product', 'items.variant', 'customer']),
            'summary' => $this->cartService->getCartSummary(),
        ]);
    }

    public function updateItem(Request $request, int $itemId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $item = $this->cartService->updateItemQuantity($itemId, $validated['quantity']);
        $cart = $this->cartService->getCart();

        return response()->json([
            'item' => $item->load(['product', 'variant']),
            'cart' => $cart->load(['items.product', 'items.variant', 'customer']),
            'summary' => $this->cartService->getCartSummary(),
        ]);
    }

    public function removeItem(int $itemId): JsonResponse
    {
        $this->cartService->removeItem($itemId);
        $cart = $this->cartService->getCart();

        return response()->json([
            'cart' => $cart->load(['items.product', 'items.variant', 'customer']),
            'summary' => $this->cartService->getCartSummary(),
        ]);
    }

    public function clear(): JsonResponse
    {
        $this->cartService->clearCart();

        return response()->json(['message' => 'Cart cleared']);
    }

    public function setCustomer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $customer = isset($validated['customer_id'])
            ? Customer::find($validated['customer_id'])
            : null;

        $cart = $this->cartService->setCustomer($customer);

        return response()->json([
            'cart' => $cart->load(['items.product', 'items.variant', 'customer']),
            'summary' => $this->cartService->getCartSummary(),
        ]);
    }

    public function applyDiscount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
            'reason' => 'nullable|string',
        ]);

        $cart = $this->cartService->applyDiscount(
            $validated['amount'],
            $validated['type'],
            $validated['reason'] ?? null
        );

        return response()->json([
            'cart' => $cart->load(['items.product', 'items.variant', 'customer']),
            'summary' => $this->cartService->getCartSummary(),
        ]);
    }

    public function removeDiscount(): JsonResponse
    {
        $cart = $this->cartService->removeDiscount();

        return response()->json([
            'cart' => $cart->load(['items.product', 'items.variant', 'customer']),
            'summary' => $this->cartService->getCartSummary(),
        ]);
    }

    public function setLoyaltyPoints(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:0',
        ]);

        try {
            $cart = $this->cartService->setLoyaltyPoints($validated['points']);

            return response()->json([
                'cart' => $cart->load(['items.product', 'items.variant', 'customer']),
                'summary' => $this->cartService->getCartSummary(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_type' => 'required|string',
            'payment_reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $cart = $this->cartService->getCart($request->store_id);

            $order = $this->orderService->createOrderFromCart(
                $cart,
                $validated['payment_type'],
                [
                    'reference' => $validated['payment_reference'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            return response()->json([
                'order' => $order,
                'receipt' => $this->orderService->getOrderReceipt($order),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function holdOrders(Request $request): JsonResponse
    {
        $heldCarts = $this->cartService->getHeldCarts($request->store_id);

        return response()->json($heldCarts);
    }

    public function hold(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $cart = $this->cartService->holdCart($validated['name']);

        return response()->json([
            'message' => 'Cart held successfully',
            'cart' => $cart,
        ]);
    }

    public function restore(int $cartId): JsonResponse
    {
        try {
            $cart = $this->cartService->restoreHeldCart($cartId);

            return response()->json([
                'cart' => $cart->load(['items.product', 'items.variant', 'customer']),
                'summary' => $this->cartService->getCartSummary(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to restore cart'], 422);
        }
    }

    public function scanBarcode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $result = $this->cartService->findProductByBarcode($validated['barcode']);

        if (!$result) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Automatically add to cart
        $item = $this->cartService->addItem(
            $result['product'],
            1,
            $result['variant'],
            $validated['store_id'] ?? null
        );

        $cart = $this->cartService->getCart();

        return response()->json([
            'product' => $result['product'],
            'variant' => $result['variant'],
            'item' => $item->load(['product', 'variant']),
            'cart' => $cart->load(['items.product', 'items.variant', 'customer']),
            'summary' => $this->cartService->getCartSummary(),
        ]);
    }
}
