<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\LoyaltyController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductAttributeController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PurchaseOrderController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\ReturnController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\StockTransferController;
use App\Http\Controllers\Api\V1\StoreController;
use App\Http\Controllers\Api\V1\TaxRateController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API v1 Routes
Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::get('settings/public', [SettingsController::class, 'public']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
        });

        // Dashboard
        Route::prefix('dashboard')->group(function () {
            Route::get('stats', [DashboardController::class, 'stats']);
            Route::get('sales-chart', [DashboardController::class, 'salesChart']);
            Route::get('top-products', [DashboardController::class, 'topProducts']);
            Route::get('low-stock', [DashboardController::class, 'lowStock']);
            Route::get('recent-orders', [DashboardController::class, 'recentOrders']);
        });

        // Products
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::post('/', [ProductController::class, 'store']);
            Route::get('search', [ProductController::class, 'search']);
            Route::get('barcode/{barcode}', [ProductController::class, 'findByBarcode']);
            Route::get('{product}', [ProductController::class, 'show']);
            Route::put('{product}', [ProductController::class, 'update']);
            Route::delete('{product}', [ProductController::class, 'destroy']);
            Route::post('{product}/variants', [ProductController::class, 'storeVariant']);
            Route::put('{product}/variants/{variant}', [ProductController::class, 'updateVariant']);
        });

        // Categories
        Route::apiResource('categories', CategoryController::class);

        // Cart
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'show']);
            Route::post('items', [CartController::class, 'addItem']);
            Route::put('items/{itemId}', [CartController::class, 'updateItem']);
            Route::delete('items/{itemId}', [CartController::class, 'removeItem']);
            Route::delete('/', [CartController::class, 'clear']);
            Route::post('customer', [CartController::class, 'setCustomer']);
            Route::post('discount', [CartController::class, 'applyDiscount']);
            Route::delete('discount', [CartController::class, 'removeDiscount']);
            Route::post('loyalty-points', [CartController::class, 'setLoyaltyPoints']);
            Route::post('checkout', [CartController::class, 'checkout']);
            Route::get('hold', [CartController::class, 'holdOrders']);
            Route::post('hold', [CartController::class, 'hold']);
            Route::post('hold/{cartId}/restore', [CartController::class, 'restore']);
            Route::post('scan', [CartController::class, 'scanBarcode']);
        });

        // Orders
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('today', [OrderController::class, 'todayOrders']);
            Route::get('recent', [OrderController::class, 'recentOrders']);
            Route::get('{order}', [OrderController::class, 'show']);
            Route::get('{order}/receipt', [OrderController::class, 'receipt']);
            Route::post('{order}/cancel', [OrderController::class, 'cancel']);
        });

        // Returns
        Route::prefix('returns')->group(function () {
            Route::get('/', [ReturnController::class, 'index']);
            Route::post('/', [ReturnController::class, 'store']);
            Route::get('order/{order}', [ReturnController::class, 'getReturnableItems']);
            Route::get('{return}', [ReturnController::class, 'show']);
            Route::post('{return}/approve', [ReturnController::class, 'approve']);
            Route::post('{return}/reject', [ReturnController::class, 'reject']);
            Route::post('{return}/process', [ReturnController::class, 'process']);
        });

        // Customers
        Route::prefix('customers')->group(function () {
            Route::get('/', [CustomerController::class, 'index']);
            Route::post('/', [CustomerController::class, 'store']);
            Route::get('search', [CustomerController::class, 'search']);
            Route::get('{customer}', [CustomerController::class, 'show']);
            Route::put('{customer}', [CustomerController::class, 'update']);
            Route::delete('{customer}', [CustomerController::class, 'destroy']);
            Route::get('{customer}/orders', [CustomerController::class, 'orders']);
            Route::get('{customer}/loyalty', [CustomerController::class, 'loyalty']);
            Route::get('{customer}/loyalty/transactions', [CustomerController::class, 'loyaltyTransactions']);
            Route::post('{customer}/credit', [CustomerController::class, 'adjustCredit']);
        });

        // Inventory
        Route::prefix('inventory')->group(function () {
            Route::get('/', [InventoryController::class, 'index']);
            Route::get('low-stock', [InventoryController::class, 'lowStock']);
            Route::post('adjust', [InventoryController::class, 'adjust']);
            Route::get('movements', [InventoryController::class, 'movements']);
            Route::post('count', [InventoryController::class, 'stockCount']);
            Route::post('initial', [InventoryController::class, 'setInitialStock']);
        });

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('sales', [ReportController::class, 'sales']);
            Route::get('sales/by-product', [ReportController::class, 'salesByProduct']);
            Route::get('sales/by-category', [ReportController::class, 'salesByCategory']);
            Route::get('sales/daily', [ReportController::class, 'dailySales']);
            Route::get('inventory', [ReportController::class, 'inventory']);
            Route::get('profit-loss', [ReportController::class, 'profitLoss']);
        });

        // Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingsController::class, 'index']);
            Route::get('groups', [SettingsController::class, 'groups']);
            Route::put('/', [SettingsController::class, 'update']);
            Route::post('logo', [SettingsController::class, 'uploadLogo']);
        });

        // Vendors
        Route::prefix('vendors')->group(function () {
            Route::get('/', [VendorController::class, 'index']);
            Route::post('/', [VendorController::class, 'store']);
            Route::get('search', [VendorController::class, 'search']);
            Route::get('{vendor}', [VendorController::class, 'show']);
            Route::put('{vendor}', [VendorController::class, 'update']);
            Route::delete('{vendor}', [VendorController::class, 'destroy']);
            Route::get('{vendor}/purchase-orders', [VendorController::class, 'purchaseOrders']);
            Route::get('{vendor}/balance', [VendorController::class, 'balance']);
        });

        // Users
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('{user}', [UserController::class, 'show']);
            Route::put('{user}', [UserController::class, 'update']);
            Route::delete('{user}', [UserController::class, 'destroy']);
            Route::post('{user}/stores', [UserController::class, 'assignStores']);
            Route::post('{user}/password', [UserController::class, 'changePassword']);
            Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus']);
        });

        // Roles
        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::get('permissions', [RoleController::class, 'permissions']);
            Route::get('permission-sets', [RoleController::class, 'permissionSets']);
            Route::post('permission-sets', [RoleController::class, 'storePermissionSet']);
            Route::put('permission-sets/{permissionSet}', [RoleController::class, 'updatePermissionSet']);
            Route::delete('permission-sets/{permissionSet}', [RoleController::class, 'destroyPermissionSet']);
            Route::get('{role}', [RoleController::class, 'show']);
            Route::put('{role}', [RoleController::class, 'update']);
            Route::delete('{role}', [RoleController::class, 'destroy']);
            Route::post('{role}/duplicate', [RoleController::class, 'duplicate']);
        });

        // Stores
        Route::prefix('stores')->group(function () {
            Route::get('/', [StoreController::class, 'index']);
            Route::post('/', [StoreController::class, 'store']);
            Route::get('{store}', [StoreController::class, 'show']);
            Route::put('{store}', [StoreController::class, 'update']);
            Route::delete('{store}', [StoreController::class, 'destroy']);
            Route::get('{store}/inventory', [StoreController::class, 'inventory']);
            Route::get('{store}/stats', [StoreController::class, 'stats']);
            Route::post('{store}/users', [StoreController::class, 'assignUsers']);
            Route::post('{store}/toggle-status', [StoreController::class, 'toggleStatus']);
        });

        // Purchase Orders
        Route::prefix('purchase-orders')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index']);
            Route::post('/', [PurchaseOrderController::class, 'store']);
            Route::get('{purchaseOrder}', [PurchaseOrderController::class, 'show']);
            Route::put('{purchaseOrder}', [PurchaseOrderController::class, 'update']);
            Route::delete('{purchaseOrder}', [PurchaseOrderController::class, 'destroy']);
            Route::post('{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit']);
            Route::post('{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve']);
            Route::post('{purchaseOrder}/order', [PurchaseOrderController::class, 'order']);
            Route::post('{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive']);
            Route::post('{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);
        });

        // Stock Transfers
        Route::prefix('stock-transfers')->group(function () {
            Route::get('/', [StockTransferController::class, 'index']);
            Route::post('/', [StockTransferController::class, 'store']);
            Route::get('{stockTransfer}', [StockTransferController::class, 'show']);
            Route::put('{stockTransfer}', [StockTransferController::class, 'update']);
            Route::delete('{stockTransfer}', [StockTransferController::class, 'destroy']);
            Route::post('{stockTransfer}/submit', [StockTransferController::class, 'submit']);
            Route::post('{stockTransfer}/ship', [StockTransferController::class, 'ship']);
            Route::post('{stockTransfer}/receive', [StockTransferController::class, 'receive']);
            Route::post('{stockTransfer}/cancel', [StockTransferController::class, 'cancel']);
        });

        // Tax Rates
        Route::prefix('tax-rates')->group(function () {
            Route::get('/', [TaxRateController::class, 'index']);
            Route::post('/', [TaxRateController::class, 'store']);
            Route::get('default', [TaxRateController::class, 'default']);
            Route::post('reorder', [TaxRateController::class, 'reorder']);
            Route::get('{taxRate}', [TaxRateController::class, 'show']);
            Route::put('{taxRate}', [TaxRateController::class, 'update']);
            Route::delete('{taxRate}', [TaxRateController::class, 'destroy']);
            Route::post('{taxRate}/set-default', [TaxRateController::class, 'setDefault']);
        });

        // Loyalty
        Route::prefix('loyalty')->group(function () {
            Route::get('overview', [LoyaltyController::class, 'overview']);
            Route::get('tiers', [LoyaltyController::class, 'tiers']);
            Route::post('tiers', [LoyaltyController::class, 'storeTier']);
            Route::put('tiers/{tier}', [LoyaltyController::class, 'updateTier']);
            Route::delete('tiers/{tier}', [LoyaltyController::class, 'destroyTier']);
            Route::get('customer/{customer}', [LoyaltyController::class, 'customerLoyalty']);
            Route::post('customer/{customer}/enroll', [LoyaltyController::class, 'enroll']);
            Route::post('customer/{customer}/award', [LoyaltyController::class, 'awardPoints']);
            Route::post('customer/{customer}/redeem', [LoyaltyController::class, 'redeemPoints']);
            Route::post('calculate', [LoyaltyController::class, 'calculate']);
            Route::get('transactions', [LoyaltyController::class, 'transactions']);
            Route::post('points-value', [LoyaltyController::class, 'pointsValue']);
        });

        // Product Attributes
        Route::prefix('product-attributes')->group(function () {
            Route::get('/', [ProductAttributeController::class, 'index']);
            Route::post('/', [ProductAttributeController::class, 'store']);
            Route::post('reorder', [ProductAttributeController::class, 'reorder']);
            Route::get('{attribute}', [ProductAttributeController::class, 'show']);
            Route::put('{attribute}', [ProductAttributeController::class, 'update']);
            Route::delete('{attribute}', [ProductAttributeController::class, 'destroy']);
            Route::post('{attribute}/values', [ProductAttributeController::class, 'storeValue']);
            Route::post('{attribute}/values/reorder', [ProductAttributeController::class, 'reorderValues']);
            Route::put('values/{value}', [ProductAttributeController::class, 'updateValue']);
            Route::delete('values/{value}', [ProductAttributeController::class, 'destroyValue']);
        });
    });
});
