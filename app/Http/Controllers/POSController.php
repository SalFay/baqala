<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Services\Cart\CartService;
use App\Services\Loyalty\LoyaltyService;
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
        protected OrderService $orderService,
        protected LoyaltyService $loyaltyService
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
        $cart = $this->cartService->getCart()->load(['items.product', 'customer', 'coupon']);
        $cartData = $cart->toApiArray();

        $subtotal = (float) ($cartData['subtotal'] ?? 0);
        $taxAmount = (float) ($cartData['tax_amount'] ?? 0);
        // Calculate effective tax rate from amounts
        $taxRate = $subtotal > 0 ? round(($taxAmount / $subtotal) * 100, 1) : 0;

        // Calculate actual discount amount
        $discountAmount = 0;
        if ($cart->discount > 0) {
            $discountAmount = $cart->discount_type === 'percentage'
                ? ($subtotal * $cart->discount) / 100
                : $cart->discount;
        }

        // Coupon discount
        $couponDiscount = (float) ($cart->coupon_discount ?? 0);

        return response()->json([
            'cart' => [
                'id' => $cartData['id'],
                'items' => $cartData['items'],
                'customer' => $cartData['customer'],
                'coupon_code' => $cart->coupon_code,
            ],
            'summary' => [
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'tax_rate' => $taxRate,
                'discount' => $discountAmount,
                'discount_value' => (float) ($cart->discount ?? 0),
                'discount_type' => $cart->discount_type,
                'discount_reason' => $cart->discount_reason,
                'coupon_code' => $cart->coupon_code,
                'coupon_discount' => $couponDiscount,
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

        try {
            $this->cartService->addItem(
                Product::find($data['product_id']),
                $data['quantity'] ?? 1,
                ($data['variant_id'] ?? null) ? \App\Models\ProductVariant::find($data['variant_id']) : null
            );

            return $this->getCart();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
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

    public function applyDiscount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:percentage,fixed',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $this->cartService->applyDiscount(
                (float) $data['amount'],
                $data['type'],
                $data['reason'] ?? null
            );
            return $this->getCart();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function removeDiscount(): JsonResponse
    {
        $this->cartService->removeDiscount();
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
        $hasSplitPayments = is_array($request->payments) && count($request->payments) > 0;

        $rules = [
            'payment_method' => $hasSplitPayments ? 'nullable' : 'required|in:cash,card,mobile,bank_transfer',
            'cash_received' => 'nullable|numeric|min:0',
            'payment_reference' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
            'payments' => 'nullable|array',
            'payments.*.method' => 'required_with:payments|in:cash,card,mobile,bank_transfer',
            'payments.*.amount' => 'required_with:payments|numeric|min:0.01',
            'payments.*.reference' => 'nullable|string',
        ];

        // Require payment reference for non-cash single payments
        if (!$hasSplitPayments && $request->payment_method && $request->payment_method !== 'cash') {
            $rules['payment_reference'] = 'required|string|min:3';
        }

        $data = $request->validate($rules);

        try {
            $cart = $this->cartService->getCart()->load(['items.product', 'customer']);

            if ($cart->items->isEmpty()) {
                return response()->json(['message' => 'Cart is empty'], 422);
            }

            // Handle split payments
            $splitPayments = [];
            if ($hasSplitPayments) {
                $totalPaid = array_sum(array_column($data['payments'], 'amount'));
                if (abs($totalPaid - $cart->total) > 0.01) {
                    return response()->json([
                        'message' => 'Split payment total must equal the cart total'
                    ], 422);
                }

                // Validate references for non-cash payments
                foreach ($data['payments'] as $payment) {
                    if ($payment['method'] !== 'cash' && empty($payment['reference'])) {
                        return response()->json([
                            'message' => 'Reference is required for ' . ucfirst($payment['method']) . ' payment'
                        ], 422);
                    }
                }

                $splitPayments = $data['payments'];
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
                $data['payment_method'] ?? 'split',
                $paymentDetails,
                $data['notes'] ?? null,
                $splitPayments
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

    public function getCustomerLoyalty(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $loyaltyInfo = $this->loyaltyService->getCustomerLoyaltyInfo($customer);

        // Add points to be earned on current cart
        $cart = $this->cartService->getCart();
        $pointsToEarn = $this->loyaltyService->calculatePointsForPurchase($cart->total);
        $maxRedeemable = $this->loyaltyService->getMaxRedeemablePoints($customer, $cart->total);

        return response()->json([
            ...$loyaltyInfo,
            'points_to_earn' => $pointsToEarn,
            'max_redeemable' => $maxRedeemable,
            'point_value' => $this->loyaltyService->getPointValue(),
        ]);
    }

    public function quickCreateCustomer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        // Check for duplicate phone
        if (Customer::where('phone', $data['phone'])->exists()) {
            return response()->json([
                'message' => 'A customer with this phone number already exists'
            ], 422);
        }

        $customer = Customer::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? '',
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'status' => 'active',
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'customer' => $customer->only(['id', 'first_name', 'last_name', 'full_name', 'phone', 'email']),
            'message' => 'Customer created successfully',
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

    /**
     * Apply a coupon code to the cart
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $code = $request->validate(['code' => 'required|string|max:50'])['code'];

        $result = $this->cartService->applyCoupon($code);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        $cart = $this->getCart()->getData(true);

        return response()->json([
            ...$cart,
            'coupon' => $result['coupon'],
            'notifications' => [['type' => 'success', 'message' => $result['message']]],
        ]);
    }

    /**
     * Remove coupon from cart
     */
    public function removeCoupon(): JsonResponse
    {
        $this->cartService->removeCoupon();
        return $this->getCart();
    }

    /**
     * Get cart with all applicable promotions/discounts
     */
    public function getCartWithPromotions(Request $request): JsonResponse
    {
        $paymentMethod = $request->payment_method;

        $result = $this->cartService->getCartWithPromotions($paymentMethod);

        return response()->json([
            'cart' => $result['cart']->toApiArray(),
            'promotions' => $result['promotions'],
            'effective_total' => $result['effective_total'],
            'savings' => $result['savings'],
            'savings_percentage' => $result['savings_percentage'],
            'free_shipping' => $result['free_shipping'],
            'free_items' => $result['free_items'],
        ]);
    }

    /**
     * Get available promotions that could apply to the cart
     */
    public function getAvailablePromotions(): JsonResponse
    {
        $promotions = $this->cartService->getAvailablePromotions();

        return response()->json([
            'data' => $promotions,
        ]);
    }

    /**
     * Search orders for return/exchange
     */
    public function searchOrdersForReturn(Request $request): JsonResponse
    {
        $query = $request->q;

        if (!$query || strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $orders = \App\Models\Order::with('customer:id,first_name,last_name,phone')
            ->where(function ($q) use ($query) {
                $q->where('order_number', 'like', "%{$query}%")
                    ->orWhereHas('customer', function ($q) use ($query) {
                        $q->where('first_name', 'like', "%{$query}%")
                            ->orWhere('last_name', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");
                    });
            })
            ->whereIn('status', ['completed', 'partially_returned'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer?->full_name ?? 'Walk-in',
                'customer_phone' => $order->customer?->phone,
                'total' => (float) $order->total,
                'status' => $order->status?->value ?? $order->status,
                'payment_type' => $order->payment_type,
                'created_at' => $order->created_at,
            ]);

        return response()->json(['data' => $orders]);
    }

    /**
     * Get order details with items for return
     */
    public function orderDetailForReturn($id): JsonResponse
    {
        $order = \App\Models\Order::with(['customer', 'items.product', 'payments'])
            ->findOrFail($id);

        return response()->json([
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer?->full_name ?? 'Walk-in',
            'total' => (float) $order->total,
            'sub_total' => (float) $order->sub_total,
            'tax_amount' => (float) $order->tax_amount,
            'discount' => (float) $order->discount,
            'status' => $order->status?->value ?? $order->status,
            'payment_type' => $order->payment_type,
            'created_at' => $order->created_at,
            'items' => $order->items->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name ?? $item->display_name,
                'variant_name' => $item->variant?->name,
                'quantity' => (int) $item->quantity,
                'returned_quantity' => (int) ($item->returned_quantity ?? 0),
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
                'sku' => $item->product?->sku,
            ]),
        ]);
    }

    /**
     * Process a return
     */
    public function processReturn(Request $request, $orderId): JsonResponse
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'reason_notes' => 'nullable|string',
            'refund_method' => 'required|in:original,cash,store_credit',
            'return_mode' => 'required|in:refund,exchange',
            'restock' => 'boolean',
        ]);

        $order = \App\Models\Order::with('items')->findOrFail($orderId);

        // Validate items belong to order and quantities are valid
        $totalRefund = 0;
        $returnItems = [];

        foreach ($data['items'] as $itemData) {
            $orderItem = $order->items->find($itemData['order_item_id']);

            if (!$orderItem) {
                return response()->json([
                    'message' => 'Item does not belong to this order'
                ], 422);
            }

            $maxReturnable = $orderItem->quantity - ($orderItem->returned_quantity ?? 0);

            if ($itemData['quantity'] > $maxReturnable) {
                return response()->json([
                    'message' => "Cannot return more than {$maxReturnable} units of {$orderItem->product?->name}"
                ], 422);
            }

            $returnItems[] = [
                'order_item' => $orderItem,
                'quantity' => $itemData['quantity'],
                'refund_amount' => $orderItem->unit_price * $itemData['quantity'],
            ];

            $totalRefund += $orderItem->unit_price * $itemData['quantity'];
        }

        // Start transaction
        \DB::beginTransaction();

        try {
            // Create return record
            $return = \App\Models\OrderReturn::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'store_id' => $order->store_id,
                'processed_by' => Auth::id(),
                'reason' => $data['reason'],
                'notes' => $data['reason_notes'] ?? null,
                'refund_method' => $data['refund_method'],
                'refund_amount' => $totalRefund,
                'total_amount' => $totalRefund,
                'type' => $data['return_mode'],
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Process each item
            foreach ($returnItems as $returnItem) {
                $orderItem = $returnItem['order_item'];

                // Create return item record
                \App\Models\OrderReturnItem::create([
                    'order_return_id' => $return->id,
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'product_variant_id' => $orderItem->product_variant_id,
                    'quantity' => $returnItem['quantity'],
                    'unit_price' => $orderItem->unit_price,
                    'total' => $returnItem['refund_amount'],
                    'restock' => $data['restock'] ?? true,
                ]);

                // Update order item returned quantity
                $orderItem->increment('returned_quantity', $returnItem['quantity']);

                // Restock inventory if requested
                if ($data['restock'] ?? true) {
                    $inventory = \App\Models\StoreInventory::firstOrCreate(
                        [
                            'product_id' => $orderItem->product_id,
                            'store_id' => $order->store_id ?? 1,
                        ],
                        ['quantity' => 0]
                    );
                    $inventory->increment('quantity', $returnItem['quantity']);
                }
            }

            // Check if all items are returned
            $allReturned = $order->items->every(function ($item) {
                return $item->returned_quantity >= $item->quantity;
            });

            // Update order status
            $order->update([
                'status' => $allReturned ? 'refunded' : 'partially_returned',
            ]);

            // Process refund based on method
            if ($data['refund_method'] === 'store_credit' && $order->customer_id) {
                // Add store credit to customer
                $order->customer->increment('store_credit', $totalRefund);
            }

            \DB::commit();

            return response()->json([
                'message' => 'Return processed successfully',
                'return' => [
                    'id' => $return->id,
                    'refund_amount' => $totalRefund,
                    'refund_method' => $data['refund_method'],
                ],
                'order' => [
                    'id' => $order->id,
                    'status' => $order->fresh()->status,
                ],
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'message' => 'Failed to process return: ' . $e->getMessage()
            ], 500);
        }
    }
}
