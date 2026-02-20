<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BusinessTypeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\StockTakeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| V2: SPA-first architecture
| - All UI routes are handled by the React SPA
| - /pos/* routes are JSON API endpoints for the SPA
*/

// ==========================================
// Authentication (Guest)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// POS Auth check (no auth required - returns 401 if not logged in)
Route::get('/pos/auth/me', [POSController::class, 'me'])->name('pos.auth.me');

// ==========================================
// Authenticated Routes
// ==========================================
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ==========================================
    // POS API Routes (JSON responses for React SPA)
    // ==========================================
    Route::prefix('pos')->name('pos.')->group(function () {

        // ------------------------------------------
        // POS: Customers CRUD
        // ------------------------------------------
        Route::controller(CustomerController::class)->prefix('customers')->name('customers.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{customer}', 'show')->name('show');
            Route::put('/{customer}', 'update')->name('update');
            Route::delete('/{customer}', 'destroy')->name('destroy');
        });

        // ------------------------------------------
        // POS: Products CRUD
        // ------------------------------------------
        Route::controller(ProductController::class)->prefix('products')->name('products.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{product}', 'edit')->name('show');
            Route::put('/{product}', 'update')->name('update');
            Route::delete('/{product}', 'destroy')->name('destroy');
        });

        // ------------------------------------------
        // POS: Categories CRUD
        // ------------------------------------------
        Route::controller(CategoryController::class)->prefix('categories')->name('categories.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{category}', 'update')->name('update');
            Route::delete('/{category}', 'destroy')->name('destroy');
        });

        // ------------------------------------------
        // POS: Vendors CRUD
        // ------------------------------------------
        Route::controller(VendorController::class)->prefix('vendors')->name('vendors.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{vendor}', 'show')->name('show');
            Route::put('/{vendor}', 'update')->name('update');
            Route::delete('/{vendor}', 'destroy')->name('destroy');
        });

        // ------------------------------------------
        // POS: Cart & Checkout
        // ------------------------------------------
        Route::controller(POSController::class)->group(function () {
            Route::get('/products', 'products')->name('products');
            // Cart
            Route::get('/cart', 'getCart')->name('cart');
            Route::post('/cart/items', 'addItem')->name('cart.add');
            Route::put('/cart/items/{itemId}', 'updateItem')->name('cart.update');
            Route::delete('/cart/items/{itemId}', 'removeItem')->name('cart.remove');
            Route::delete('/cart', 'clearCart')->name('cart.clear');
            Route::post('/cart/customer', 'setCustomer')->name('cart.customer');
            Route::post('/cart/scan', 'scanBarcode')->name('cart.scan');
            Route::post('/cart/hold', 'holdCart')->name('cart.hold');
            Route::get('/cart/hold', 'getHeldCarts')->name('cart.held');
            Route::post('/cart/hold/{cartId}/restore', 'restoreHeldCart')->name('cart.restore');
            Route::post('/cart/checkout', 'checkout')->name('checkout');
            Route::get('/customers/search', 'searchCustomers')->name('customers.search');
            // Dashboard
            Route::get('/dashboard/stats', 'dashboardStats')->name('dashboard.stats');
            Route::get('/dashboard/sales-chart', 'dashboardSalesChart')->name('dashboard.sales-chart');
            Route::get('/dashboard/top-products', 'dashboardTopProducts')->name('dashboard.top-products');
            Route::get('/dashboard/low-stock', 'dashboardLowStock')->name('dashboard.low-stock');
            Route::get('/dashboard/recent-orders', 'dashboardRecentOrders')->name('dashboard.recent-orders');
            // Orders
            Route::get('/orders', 'orders')->name('orders.index');
            Route::get('/orders/today', 'todayOrders')->name('orders.today');
            Route::get('/orders/recent', 'recentOrders')->name('orders.recent');
            Route::get('/orders/{id}', 'orderDetail')->name('orders.show');
            Route::get('/orders/{id}/receipt', 'orderReceipt')->name('orders.receipt');
            Route::post('/orders/{id}/cancel', 'cancelOrder')->name('orders.cancel');
        });

        // ------------------------------------------
        // POS: Business Types
        // ------------------------------------------
        Route::controller(BusinessTypeController::class)->prefix('business-types')->name('business-types.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/current', 'current')->name('current');
            Route::get('/{businessType}/preview', 'preview')->name('preview');
            Route::post('/{businessType}/apply', 'apply')->name('apply');
            Route::post('/seed', 'seedTypes')->name('seed');
        });

        // ------------------------------------------
        // POS: Expenses
        // ------------------------------------------
        Route::controller(ExpenseController::class)->prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/summary', 'summary')->name('summary');
            Route::get('/categories', 'categories')->name('categories');
            Route::get('/categories/flat', 'categoriesFlat')->name('categories.flat');
            Route::get('/vendors', 'vendors')->name('vendors');
            Route::post('/', 'store')->name('store');
            Route::get('/{expense}', 'show')->name('show');
            Route::put('/{expense}', 'update')->name('update');
            Route::delete('/{expense}', 'destroy')->name('destroy');
            Route::post('/{expense}/approve', 'approve')->name('approve');
            Route::post('/{expense}/reject', 'reject')->name('reject');
            Route::post('/{expense}/paid', 'markPaid')->name('paid');
            Route::post('/{expense}/receipt', 'uploadReceipt')->name('receipt');
        });

        // ------------------------------------------
        // POS: Returns
        // ------------------------------------------
        Route::controller(ReturnController::class)->prefix('returns')->name('returns.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/reasons', 'reasons')->name('reasons');
            Route::get('/order/{order}', 'getReturnableItems')->name('returnable');
            Route::post('/', 'store')->name('store');
            Route::get('/{return}', 'show')->name('show');
            Route::post('/{return}/approve', 'approve')->name('approve');
            Route::post('/{return}/reject', 'reject')->name('reject');
            Route::post('/{return}/process', 'process')->name('process');
        });

        // ------------------------------------------
        // POS: Stock Takes
        // ------------------------------------------
        Route::controller(StockTakeController::class)->prefix('stock-takes')->name('stock-takes.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/summary', 'summary')->name('summary');
            Route::post('/', 'store')->name('store');
            Route::get('/{stockTake}', 'show')->name('show');
            Route::delete('/{stockTake}', 'destroy')->name('destroy');
            Route::post('/{stockTake}/start', 'start')->name('start');
            Route::post('/{stockTake}/complete', 'complete')->name('complete');
            Route::post('/{stockTake}/cancel', 'cancel')->name('cancel');
            Route::post('/{stockTake}/items/{item}/count', 'countItem')->name('count-item');
            Route::post('/{stockTake}/scan', 'scanBarcode')->name('scan');
        });

        // ------------------------------------------
        // POS: Statements (Customer & Vendor)
        // ------------------------------------------
        Route::controller(StatementController::class)->group(function () {
            // Customer Statements
            Route::get('/customers/{customer}/statement', 'customerStatement')->name('statements.customer');
            Route::get('/customers/{customer}/statement/pdf', 'customerStatementPdf')->name('statements.customer.pdf');
            Route::get('/customers/{customer}/credits', 'customerCredits')->name('statements.customer.credits');
            Route::post('/customers/{customer}/credits', 'addCustomerCredit')->name('statements.customer.credits.add');
            // Vendor Statements
            Route::get('/vendors/{vendor}/statement', 'vendorStatement')->name('statements.vendor');
            Route::get('/vendors/{vendor}/statement/pdf', 'vendorStatementPdf')->name('statements.vendor.pdf');
            Route::get('/vendors/{vendor}/credits', 'vendorCredits')->name('statements.vendor.credits');
            Route::post('/vendors/{vendor}/credits', 'addVendorCredit')->name('statements.vendor.credits.add');
        });

    });

    // ==========================================
    // Settings API Routes
    // ==========================================
    Route::controller(SettingsController::class)->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/', 'update')->name('update');
        // Stores
        Route::get('/stores', 'stores')->name('stores');
        Route::post('/stores', 'storeStore')->name('stores.store');
        Route::put('/stores/{store}', 'updateStore')->name('stores.update');
        Route::delete('/stores/{store}', 'destroyStore')->name('stores.destroy');
        // Payment Methods
        Route::get('/payment-methods', 'paymentMethods')->name('payment-methods');
        Route::post('/payment-methods', 'storePaymentMethod')->name('payment-methods.store');
        Route::put('/payment-methods/{paymentMethod}', 'updatePaymentMethod')->name('payment-methods.update');
        Route::delete('/payment-methods/{paymentMethod}', 'destroyPaymentMethod')->name('payment-methods.destroy');
        // Tax Rates
        Route::get('/tax-rates', 'taxRates')->name('tax-rates');
        Route::post('/tax-rates', 'storeTaxRate')->name('tax-rates.store');
        Route::put('/tax-rates/{taxRate}', 'updateTaxRate')->name('tax-rates.update');
        Route::delete('/tax-rates/{taxRate}', 'destroyTaxRate')->name('tax-rates.destroy');
        // Users
        Route::get('/users', 'users')->name('users');
        Route::post('/users', 'storeUser')->name('users.store');
        Route::put('/users/{user}', 'updateUser')->name('users.update');
        Route::delete('/users/{user}', 'destroyUser')->name('users.destroy');
    });

    // ==========================================
    // Inventory API Routes
    // ==========================================
    Route::controller(InventoryController::class)->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/adjust', 'adjust')->name('adjust');
        Route::get('/movements', 'movements')->name('movements');
        Route::get('/low-stock', 'lowStock')->name('low-stock');
    });

    // ==========================================
    // Purchase Orders API Routes
    // ==========================================
    Route::controller(PurchaseOrderController::class)->prefix('purchase-orders')->name('purchase-orders.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{purchaseOrder}', 'show')->name('show');
        Route::put('/{purchaseOrder}', 'update')->name('update');
        Route::post('/{purchaseOrder}/receive', 'receive')->name('receive');
    });

    // ==========================================
    // Stock Transfers API Routes
    // ==========================================
    Route::controller(StockTransferController::class)->prefix('stock-transfers')->name('stock-transfers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{stockTransfer}', 'show')->name('show');
        Route::post('/{stockTransfer}/ship', 'ship')->name('ship');
        Route::post('/{stockTransfer}/receive', 'receive')->name('receive');
    });

    // ==========================================
    // Reports API Routes
    // ==========================================
    Route::controller(ReportController::class)->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/sales', 'sales')->name('sales');
        Route::get('/inventory', 'inventory')->name('inventory');
        Route::get('/customers', 'customers')->name('customers');
        Route::get('/export', 'export')->name('export');
    });

    // ==========================================
    // Inertia Page Routes
    // ==========================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pos', fn () => inertia('POS/Index'))->name('pos');
    Route::get('/products', fn () => inertia('Products/Index'))->name('products.page');
    Route::get('/categories', fn () => inertia('Categories/Index'))->name('categories.page');
    Route::get('/customers', fn () => inertia('Customers/Index'))->name('customers.page');
    Route::get('/orders', fn () => inertia('Orders/Index'))->name('orders.page');
    Route::get('/returns', fn () => inertia('Returns/Index'))->name('returns.page');
    Route::get('/inventory', fn () => inertia('Inventory/Index'))->name('inventory.page');
    Route::get('/stock-takes', fn () => inertia('StockTakes/Index'))->name('stock-takes.page');
    Route::get('/purchase-orders', fn () => inertia('PurchaseOrders/Index'))->name('purchase-orders.page');
    Route::get('/stock-transfers', fn () => inertia('StockTransfers/Index'))->name('stock-transfers.page');
    Route::get('/vendors', fn () => inertia('Vendors/Index'))->name('vendors.page');
    Route::get('/reports', fn () => inertia('Reports/Index'))->name('reports.page');
    Route::get('/expenses', fn () => inertia('Expenses/Index'))->name('expenses.page');
    Route::get('/statements', fn () => inertia('Statements/Index'))->name('statements.page');
    Route::get('/settings', fn () => inertia('Settings/Index'))->name('settings.page');
    Route::get('/users', fn () => inertia('Users/Index'))->name('users.page');
    Route::get('/roles', fn () => inertia('Roles/Index'))->name('roles.page');
    Route::get('/stores', fn () => inertia('Stores/Index'))->name('stores.page');

    // Redirect root to dashboard
    Route::get('/', fn () => redirect()->route('dashboard'));
});
