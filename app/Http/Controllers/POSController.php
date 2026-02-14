<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class POSController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService
    ) {}

    public function index(): Response
    {
        return Inertia::render('POS/Index', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']),
            'initialCart' => $this->cartService->getCart()->toApiArray(),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $products = Product::with('category')
            ->active()
            ->when($request->category_id, fn($q, $id) => $q->where('category_id', $id))
            ->when($request->search, fn($q, $term) => $q->search($term))
            ->orderBy('name')
            ->limit($request->per_page ?? 100)
            ->get()
            ->map(fn($p) => $p->toPosArray());

        return response()->json(['data' => $products]);
    }

    public function getCart(): JsonResponse
    {
        return response()->json($this->cartService->getCart()->load(['items.product', 'customer'])->toApiArray());
    }

    public function addItem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'integer|min:1',
        ]);

        $this->cartService->addItem(
            Product::find($data['product_id']),
            $data['quantity'] ?? 1,
            $data['variant_id'] ? \App\Models\ProductVariant::find($data['variant_id']) : null
        );

        return $this->getCart();
    }

    public function updateItem(Request $request, $itemId): JsonResponse
    {
        $this->cartService->updateItemQuantity((int) $itemId, $request->validate(['quantity' => 'required|integer|min:0'])['quantity']);
        return $this->getCart();
    }

    public function removeItem($itemId): JsonResponse
    {
        $this->cartService->removeItem((int) $itemId);
        return $this->getCart();
    }

    public function clearCart(): JsonResponse
    {
        $this->cartService->clearCart();
        return response()->json(['message' => 'Cart cleared']);
    }

    public function setCustomer(Request $request): JsonResponse
    {
        $id = $request->validate(['customer_id' => 'nullable|exists:customers,id'])['customer_id'];
        $this->cartService->setCustomer($id ? Customer::find($id) : null);
        return $this->getCart();
    }

    public function scanBarcode(Request $request): JsonResponse
    {
        $result = $this->cartService->findProductByBarcode($request->validate(['barcode' => 'required|string'])['barcode']);

        if (!$result) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $this->cartService->addItem($result['product'], 1, $result['variant']);
        return $this->getCart();
    }

    public function holdCart(Request $request): JsonResponse
    {
        $this->cartService->holdCart($request->validate(['name' => 'required|string|max:255'])['name']);
        return response()->json(['message' => 'Cart held']);
    }

    public function getHeldCarts(): JsonResponse
    {
        return response()->json([
            'data' => $this->cartService->getHeldCarts()->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->hold_name,
                'items_count' => $c->items->sum('quantity'),
                'total' => $c->total,
                'customer' => $c->customer,
                'held_at' => $c->held_at,
            ])
        ]);
    }

    public function restoreHeldCart($cartId): JsonResponse
    {
        $this->cartService->restoreHeldCart((int) $cartId);
        return $this->getCart();
    }

    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payment_method' => 'required|in:cash,card,mobile,bank_transfer',
            'cash_received' => 'nullable|numeric|min:0',
        ]);

        try {
            $cart = $this->cartService->getCart()->load(['items.product', 'customer']);

            $order = $this->orderService->createOrderFromCart(
                $cart,
                $data['payment_method'],
                $data['cash_received'] ? ['cash_received' => $data['cash_received']] : []
            );

            return response()->json([
                'order' => $order->only(['id', 'order_number', 'total']),
                'receipt' => $this->orderService->getOrderReceipt($order),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Customer::active()
                ->when($request->q, fn($q, $term) => $q->search($term))
                ->limit(20)
                ->get()
                ->map(fn($c) => $c->only(['id', 'first_name', 'last_name', 'full_name', 'phone', 'email', 'loyalty_points']))
        ]);
    }
}
