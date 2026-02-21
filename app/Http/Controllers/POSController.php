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

    public function categories(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->when($request->search, fn($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'parent_id', 'image', 'is_active']);

        return response()->json(['data' => $categories]);
    }

    public function getCart(): JsonResponse
    {
        $cart = $this->cartService->getCart()->load(['items.product', 'customer']);
        $cartData = $cart->toApiArray();

        return response()->json([
            'cart' => [
                'id' => $cartData['id'],
                'items' => $cartData['items'],
                'customer' => $cartData['customer'],
            ],
            'summary' => [
                'subtotal' => (float) ($cartData['subtotal'] ?? 0),
                'tax_amount' => (float) ($cartData['tax_amount'] ?? 0),
                'discount' => (float) ($cartData['discount'] ?? 0),
                'total' => (float) ($cartData['total'] ?? 0),
            ],
        ]);
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
            ($data['variant_id'] ?? null) ? \App\Models\ProductVariant::find($data['variant_id']) : null
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
            'payment_reference' => 'nullable|string',
        ]);

        try {
            $cart = $this->cartService->getCart()->load(['items.product', 'customer']);

            if ($cart->items->isEmpty()) {
                return response()->json(['message' => 'Cart is empty'], 422);
            }

            $paymentDetails = [];
            if (!empty($data['cash_received'])) {
                $paymentDetails['cash_received'] = $data['cash_received'];
            }
            if (!empty($data['payment_reference'])) {
                $paymentDetails['reference'] = $data['payment_reference'];
            }

            $order = $this->orderService->createOrderFromCart(
                $cart,
                $data['payment_method'],
                $paymentDetails
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
        $yesterday = now()->subDay()->startOfDay();

        $ordersQuery = \App\Models\Order::query()
            ->when($storeId, fn($q, $id) => $q->where('store_id', $id));

        $todayOrders = (clone $ordersQuery)->whereDate('created_at', $today)->get();
        $yesterdayOrders = (clone $ordersQuery)->whereDate('created_at', $yesterday)->get();
        $monthOrders = (clone $ordersQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->get();

        // Low stock count from store_inventories
        $lowStockCount = \App\Models\StoreInventory::query()
            ->when($storeId, fn($q, $id) => $q->where('store_id', $id))
            ->whereRaw('quantity <= COALESCE(low_stock_threshold, 10)')
            ->distinct('product_id')
            ->count('product_id');

        // Inventory value calculation
        $inventoryValue = \App\Models\StoreInventory::query()
            ->join('products', 'store_inventories.product_id', '=', 'products.id')
            ->when($storeId, fn($q, $id) => $q->where('store_inventories.store_id', $id))
            ->selectRaw('SUM(store_inventories.quantity * products.cost_price) as total_value')
            ->value('total_value') ?? 0;

        // Customer count
        $customerCount = \App\Models\Customer::query()
            ->when($storeId, fn($q, $id) => $q->where('store_id', $id))
            ->count();

        // Product count
        $productCount = Product::active()
            ->when($storeId, fn($q, $id) => $q->forStore($id))
            ->count();

        // Calculate sales growth
        $todaySales = $todayOrders->sum('total');
        $yesterdaySales = $yesterdayOrders->sum('total');
        $salesGrowth = $yesterdaySales > 0
            ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100
            : ($todaySales > 0 ? 100 : 0);

        // Average order value
        $avgOrderValue = $todayOrders->count() > 0
            ? $todaySales / $todayOrders->count()
            : 0;

        return response()->json([
            'today' => [
                'total_sales' => (float) $todaySales,
                'total_orders' => (int) $todayOrders->count(),
                'average_order_value' => (float) round($avgOrderValue, 2),
            ],
            'month' => [
                'total_sales' => (float) $monthOrders->sum('total'),
                'total_orders' => (int) $monthOrders->count(),
            ],
            'sales_growth' => (float) round($salesGrowth, 1),
            'inventory' => [
                'low_stock_count' => (int) $lowStockCount,
                'total_value' => (float) round($inventoryValue, 2),
                'total_products' => (int) $productCount,
            ],
            'customers' => [
                'total' => (int) $customerCount,
            ],
            'pending_orders' => (int) (clone $ordersQuery)->where('status', 'pending')->count(),
        ]);
    }

    public function dashboardSalesChart(Request $request): JsonResponse
    {
        $fromDate = $request->from_date ? \Carbon\Carbon::parse($request->from_date)->startOfDay() : now()->subDays(30)->startOfDay();
        $toDate = $request->to_date ? \Carbon\Carbon::parse($request->to_date)->endOfDay() : now()->endOfDay();

        $sales = \App\Models\Order::query()
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as orders')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get();

        return response()->json(['data' => $sales]);
    }

    public function dashboardTopProducts(Request $request): JsonResponse
    {
        $fromDate = $request->from_date ? \Carbon\Carbon::parse($request->from_date)->startOfDay() : now()->subDays(30)->startOfDay();
        $toDate = $request->to_date ? \Carbon\Carbon::parse($request->to_date)->endOfDay() : now()->endOfDay();

        $topProducts = \App\Models\OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->when($request->store_id, fn($q, $id) => $q->where('orders.store_id', $id))
            ->whereBetween('orders.created_at', [$fromDate, $toDate])
            ->selectRaw('products.id, products.name, SUM(order_items.quantity) as total_qty, SUM(order_items.line_total) as total_sales')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit($request->limit ?? 10)
            ->get();

        return response()->json(['data' => $topProducts]);
    }

    public function dashboardLowStock(Request $request): JsonResponse
    {
        $storeId = $request->store_id;

        $lowStockItems = \App\Models\StoreInventory::query()
            ->with('product:id,name,sku,low_stock_threshold')
            ->when($storeId, fn($q, $id) => $q->where('store_id', $id))
            ->whereRaw('quantity <= COALESCE(low_stock_threshold, 10)')
            ->orderBy('quantity')
            ->limit(20)
            ->get()
            ->map(fn($inv) => [
                'id' => $inv->id,
                'product' => [
                    'id' => $inv->product_id,
                    'name' => $inv->product?->name,
                    'sku' => $inv->product?->sku,
                ],
                'quantity' => $inv->quantity,
                'low_stock_threshold' => $inv->low_stock_threshold ?? $inv->product?->low_stock_threshold ?? 10,
                'store_id' => $inv->store_id,
            ]);

        return response()->json($lowStockItems);
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
                'customer' => $o->customer ? [
                    'id' => $o->customer->id,
                    'full_name' => $o->customer->full_name,
                ] : null,
                'total' => (float) $o->total,
                'status' => $o->status?->value ?? $o->status,
                'created_at' => $o->created_at,
            ]);

        return response()->json($orders);
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

    /**
     * Server-side listing for DataGridTable
     */
    public function ordersListing(Request $request): JsonResponse
    {
        $query = \App\Models\Order::with('customer:id,first_name,last_name');

        // Search
        if ($request->search) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', "%{$term}%")
                    ->orWhereHas('customer', function ($q) use ($term) {
                        $q->where('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%");
                    });
            });
        }

        // Sorting
        if ($request->sort && count($request->sort) > 0) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderByDesc('created_at');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $orders = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer?->full_name ?? 'Walk-in',
                'total' => $order->total,
                'current_status' => $order->status,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at,
            ]);

        return response()->json([
            'data' => $orders,
            'total' => $total,
        ]);
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
