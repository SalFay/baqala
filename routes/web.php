<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Orders - Additional actions
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('/orders/{order}/status-history', [OrderController::class, 'statusHistory'])->name('orders.status-history');
    Route::get('/orders/{order}/activity-log', [OrderController::class, 'activityLog'])->name('orders.activity-log');
    Route::get('/orders/{order}/available-statuses', [OrderController::class, 'availableStatuses'])->name('orders.available-statuses');

    // POS
    Route::get('/pos', [POSController::class, 'index'])->name('pos');
    Route::get('/pos/products', [POSController::class, 'products'])->name('pos.products');
    Route::get('/pos/cart', [POSController::class, 'getCart'])->name('pos.cart');
    Route::post('/pos/cart/items', [POSController::class, 'addItem'])->name('pos.cart.add');
    Route::put('/pos/cart/items/{itemId}', [POSController::class, 'updateItem'])->name('pos.cart.update');
    Route::delete('/pos/cart/items/{itemId}', [POSController::class, 'removeItem'])->name('pos.cart.remove');
    Route::delete('/pos/cart', [POSController::class, 'clearCart'])->name('pos.cart.clear');
    Route::post('/pos/cart/customer', [POSController::class, 'setCustomer'])->name('pos.cart.customer');
    Route::post('/pos/cart/scan', [POSController::class, 'scanBarcode'])->name('pos.cart.scan');
    Route::post('/pos/cart/hold', [POSController::class, 'holdCart'])->name('pos.cart.hold');
    Route::get('/pos/cart/hold', [POSController::class, 'getHeldCarts'])->name('pos.cart.held');
    Route::post('/pos/cart/hold/{cartId}/restore', [POSController::class, 'restoreHeldCart'])->name('pos.cart.restore');
    Route::post('/pos/cart/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/customers/search', [POSController::class, 'searchCustomers'])->name('pos.customers.search');

    // Vendors
    Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/create', [VendorController::class, 'create'])->name('vendors.create');
    Route::post('/vendors', [VendorController::class, 'store'])->name('vendors.store');
    Route::get('/vendors/{vendor}', [VendorController::class, 'show'])->name('vendors.show');
    Route::get('/vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('vendors.edit');
    Route::put('/vendors/{vendor}', [VendorController::class, 'update'])->name('vendors.update');
    Route::delete('/vendors/{vendor}', [VendorController::class, 'destroy'])->name('vendors.destroy');

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');

    // Purchase Orders
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::get('/purchase-orders/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
    Route::put('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');

    // Stock Transfers
    Route::get('/stock-transfers', [StockTransferController::class, 'index'])->name('stock-transfers.index');
    Route::get('/stock-transfers/create', [StockTransferController::class, 'create'])->name('stock-transfers.create');
    Route::post('/stock-transfers', [StockTransferController::class, 'store'])->name('stock-transfers.store');
    Route::get('/stock-transfers/{stockTransfer}', [StockTransferController::class, 'show'])->name('stock-transfers.show');
    Route::post('/stock-transfers/{stockTransfer}/ship', [StockTransferController::class, 'ship'])->name('stock-transfers.ship');
    Route::post('/stock-transfers/{stockTransfer}/receive', [StockTransferController::class, 'receive'])->name('stock-transfers.receive');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Settings - Stores
    Route::get('/settings/stores', [SettingsController::class, 'stores'])->name('settings.stores');
    Route::post('/settings/stores', [SettingsController::class, 'storeStore'])->name('settings.stores.store');
    Route::put('/settings/stores/{store}', [SettingsController::class, 'updateStore'])->name('settings.stores.update');
    Route::delete('/settings/stores/{store}', [SettingsController::class, 'destroyStore'])->name('settings.stores.destroy');

    // Settings - Payment Methods
    Route::get('/settings/payment-methods', [SettingsController::class, 'paymentMethods'])->name('settings.payment-methods');
    Route::post('/settings/payment-methods', [SettingsController::class, 'storePaymentMethod'])->name('settings.payment-methods.store');
    Route::put('/settings/payment-methods/{paymentMethod}', [SettingsController::class, 'updatePaymentMethod'])->name('settings.payment-methods.update');
    Route::delete('/settings/payment-methods/{paymentMethod}', [SettingsController::class, 'destroyPaymentMethod'])->name('settings.payment-methods.destroy');

    // Settings - Tax Rates
    Route::get('/settings/tax-rates', [SettingsController::class, 'taxRates'])->name('settings.tax-rates');
    Route::post('/settings/tax-rates', [SettingsController::class, 'storeTaxRate'])->name('settings.tax-rates.store');
    Route::put('/settings/tax-rates/{taxRate}', [SettingsController::class, 'updateTaxRate'])->name('settings.tax-rates.update');
    Route::delete('/settings/tax-rates/{taxRate}', [SettingsController::class, 'destroyTaxRate'])->name('settings.tax-rates.destroy');

    // Settings - Users
    Route::get('/settings/users', [SettingsController::class, 'users'])->name('settings.users');
    Route::post('/settings/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
    Route::put('/settings/users/{user}', [SettingsController::class, 'updateUser'])->name('settings.users.update');
    Route::delete('/settings/users/{user}', [SettingsController::class, 'destroyUser'])->name('settings.users.destroy');
});

// Keep API routes in api.php, only web routes here
