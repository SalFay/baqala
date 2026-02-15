<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class POSController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService
    ) {}

    public function me(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
        ]);
    }

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

    // Dashboard data for POS app
    public function dashboardStats(Request $request): JsonResponse
    {
        $storeId = $request->store_id;
        $today = now()->startOfDay();

        $ordersQuery = \App\Models\Order::query()
            ->when($storeId, fn($q, $id) => $q->where('store_id', $id));

        $todayOrders = (clone $ordersQuery)->whereDate('created_at', $today)->get();
        $monthOrders = (clone $ordersQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->get();

        return response()->json([
            'today_sales' => $todayOrders->sum('total'),
            'today_orders' => $todayOrders->count(),
            'month_sales' => $monthOrders->sum('total'),
            'month_orders' => $monthOrders->count(),
            'pending_orders' => (clone $ordersQuery)->where('status', 'pending')->count(),
            'low_stock_count' => Product::where('stock_quantity', '<', 10)->count(),
        ]);
    }

    public function dashboardSalesChart(Request $request): JsonResponse
    {
        $fromDate = $request->from_date ? \Carbon\Carbon::parse($request->from_date) : now()->subDays(30);
        $toDate = $request->to_date ? \Carbon\Carbon::parse($request->to_date) : now();

        $sales = \App\Models\Order::query()
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json(['data' => $sales]);
    }

    public function dashboardTopProducts(Request $request): JsonResponse
    {
        $fromDate = $request->from_date ? \Carbon\Carbon::parse($request->from_date) : now()->subDays(30);
        $toDate = $request->to_date ? \Carbon\Carbon::parse($request->to_date) : now();

        $topProducts = \App\Models\OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->when($request->store_id, fn($q, $id) => $q->where('orders.store_id', $id))
            ->whereBetween('orders.created_at', [$fromDate, $toDate])
            ->selectRaw('products.id, products.name, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_sales')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit($request->limit ?? 10)
            ->get();

        return response()->json(['data' => $topProducts]);
    }

    public function dashboardLowStock(Request $request): JsonResponse
    {
        $products = Product::where('stock_quantity', '<', 10)
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->orderBy('stock_quantity')
            ->limit(20)
            ->get(['id', 'name', 'stock_quantity', 'sku']);

        return response()->json(['data' => $products]);
    }

    public function dashboardRecentOrders(Request $request): JsonResponse
    {
        $orders = \App\Models\Order::with('customer:id,first_name,last_name')
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->orderByDesc('created_at')
            ->limit($request->limit ?? 10)
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'customer_name' => $o->customer?->full_name ?? 'Walk-in',
                'total' => $o->total,
                'status' => $o->status,
                'created_at' => $o->created_at,
            ]);

        return response()->json(['data' => $orders]);
    }

    // Orders for POS app
    public function orders(Request $request): JsonResponse
    {
        $orders = \App\Models\Order::with('customer:id,first_name,last_name')
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->from_date, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->to_date, fn($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($orders);
    }

    public function orderDetail($id): JsonResponse
    {
        $order = \App\Models\Order::with(['customer', 'items.product', 'payments'])
            ->findOrFail($id);

        return response()->json(['data' => $order]);
    }

    public function orderReceipt($id): JsonResponse
    {
        $order = \App\Models\Order::with(['customer', 'items.product', 'store'])
            ->findOrFail($id);

        return response()->json([
            'data' => $this->orderService->getOrderReceipt($order)
        ]);
    }

    public function cancelOrder(Request $request, $id): JsonResponse
    {
        $order = \App\Models\Order::findOrFail($id);
        $reason = $request->validate(['reason' => 'required|string'])['reason'];

        $order->update([
            'status' => 'cancelled',
            'notes' => ($order->notes ? $order->notes . "\n" : '') . "Cancelled: $reason"
        ]);

        return response()->json(['order' => $order->fresh()]);
    }

    public function todayOrders(Request $request): JsonResponse
    {
        $orders = \App\Models\Order::with('customer:id,first_name,last_name')
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->whereDate('created_at', now())
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $orders]);
    }

    public function recentOrders(Request $request): JsonResponse
    {
        return $this->dashboardRecentOrders($request);
    }
}
