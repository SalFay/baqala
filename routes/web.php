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
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SellingPriceGroupController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\VariationTemplateController;
use App\Http\Controllers\ModifierSetController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ProductSerialController;
use App\Http\Controllers\ProductBatchController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\DiscountRuleController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerLedgerController;
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\LocationController;
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
            Route::post('/listing', 'listing')->name('listing');
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
            Route::post('/listing', 'listing')->name('listing');
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
            Route::post('/listing', 'listing')->name('listing');
            Route::post('/', 'store')->name('store');
            Route::put('/{category}', 'update')->name('update');
            Route::delete('/{category}', 'destroy')->name('destroy');
        });

        // ------------------------------------------
        // POS: Vendors CRUD
        // ------------------------------------------
        Route::controller(VendorController::class)->prefix('vendors')->name('vendors.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
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
            Route::post('/cart/discount', 'applyDiscount')->name('cart.discount');
            Route::delete('/cart/discount', 'removeDiscount')->name('cart.discount.remove');
            Route::post('/cart/hold', 'holdCart')->name('cart.hold');
            Route::get('/cart/hold', 'getHeldCarts')->name('cart.held');
            Route::post('/cart/hold/{cartId}/restore', 'restoreHeldCart')->name('cart.restore');
            Route::post('/cart/checkout', 'checkout')->name('checkout');
            Route::get('/customers/search', 'searchCustomers')->name('customers.search');
            Route::post('/customers', 'quickCreateCustomer')->name('customers.quick-create');
            Route::get('/customers/{customer}/loyalty', 'getCustomerLoyalty')->name('customers.loyalty');
            // Dashboard
            Route::get('/dashboard/stats', 'dashboardStats')->name('dashboard.stats');
            Route::get('/dashboard/sales-chart', 'dashboardSalesChart')->name('dashboard.sales-chart');
            Route::get('/dashboard/top-products', 'dashboardTopProducts')->name('dashboard.top-products');
            Route::get('/dashboard/low-stock', 'dashboardLowStock')->name('dashboard.low-stock');
            Route::get('/dashboard/recent-orders', 'dashboardRecentOrders')->name('dashboard.recent-orders');
            // Orders
            Route::get('/orders', 'orders')->name('orders.index');
            Route::post('/orders/listing', 'ordersListing')->name('orders.listing');
            Route::get('/orders/today', 'todayOrders')->name('orders.today');
            Route::get('/orders/recent', 'recentOrders')->name('orders.recent');
            Route::get('/orders/{id}', 'orderDetail')->name('orders.show');
            Route::get('/orders/{id}/receipt', 'orderReceipt')->name('orders.receipt');
            Route::post('/orders/{id}/cancel', 'cancelOrder')->name('orders.cancel');
            // Returns
            Route::get('/orders/search', 'searchOrdersForReturn')->name('orders.search');
            Route::post('/orders/{id}/return', 'processReturn')->name('orders.return');
        });

        // ------------------------------------------
        // POS: Business Types
        // ------------------------------------------
        Route::controller(BusinessTypeController::class)->prefix('business-types')->name('business-types.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/all', 'all')->name('all');
            Route::post('/', 'store')->name('store');
            Route::put('/{businessType}', 'update')->name('update');
            Route::delete('/{businessType}', 'destroy')->name('destroy');
            Route::get('/current', 'current')->name('current');
            Route::get('/{businessType}/preview', 'preview')->name('preview');
            Route::post('/{businessType}/apply', 'apply')->name('apply');
            Route::post('/seed', 'seedTypes')->name('seed');
        });

        // ------------------------------------------
        // POS: Selling Price Groups
        // ------------------------------------------
        Route::controller(SellingPriceGroupController::class)->prefix('price-groups')->name('price-groups.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/all', 'all')->name('all');
            Route::post('/', 'store')->name('store');
            Route::put('/{sellingPriceGroup}', 'update')->name('update');
            Route::delete('/{sellingPriceGroup}', 'destroy')->name('destroy');
        });

        // ------------------------------------------
        // POS: Customer Groups
        // ------------------------------------------
        Route::controller(CustomerGroupController::class)->prefix('customer-groups')->name('customer-groups.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/all', 'all')->name('all');
            Route::post('/', 'store')->name('store');
            Route::put('/{customerGroup}', 'update')->name('update');
            Route::delete('/{customerGroup}', 'destroy')->name('destroy');
        });

        // ------------------------------------------
        // POS: Variation Templates
        // ------------------------------------------
        Route::controller(VariationTemplateController::class)->prefix('variation-templates')->name('variation-templates.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/all', 'all')->name('all');
            Route::post('/', 'store')->name('store');
            Route::put('/{variationTemplate}', 'update')->name('update');
            Route::delete('/{variationTemplate}', 'destroy')->name('destroy');
            Route::get('/{variationTemplate}/combinations', 'generateCombinations')->name('combinations');
        });

        // ------------------------------------------
        // POS: Modifier Sets (Restaurant)
        // ------------------------------------------
        Route::controller(ModifierSetController::class)->prefix('modifier-sets')->name('modifier-sets.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/all', 'all')->name('all');
            Route::post('/', 'store')->name('store');
            Route::put('/{modifierSet}', 'update')->name('update');
            Route::delete('/{modifierSet}', 'destroy')->name('destroy');
        });

        // ------------------------------------------
        // POS: Units of Measure
        // ------------------------------------------
        Route::controller(UnitController::class)->prefix('units')->name('units.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/all', 'all')->name('all');
            Route::get('/base', 'baseUnits')->name('base');
            Route::post('/', 'store')->name('store');
            Route::put('/{unit}', 'update')->name('update');
            Route::delete('/{unit}', 'destroy')->name('destroy');
        });

        // ------------------------------------------
        // POS: Product Serials (IMEI Tracking)
        // ------------------------------------------
        Route::controller(ProductSerialController::class)->prefix('serials')->name('serials.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/lookup', 'lookup')->name('lookup');
            Route::get('/product/{productId}', 'forProduct')->name('for-product');
            Route::get('/statistics', 'statistics')->name('statistics');
            Route::post('/', 'store')->name('store');
            Route::post('/bulk', 'bulkStore')->name('bulk-store');
            Route::put('/{productSerial}', 'update')->name('update');
            Route::put('/{productSerial}/status', 'updateStatus')->name('status');
            Route::delete('/{productSerial}', 'destroy')->name('destroy');
        });

        // ------------------------------------------
        // POS: Product Batches (Expiry Tracking)
        // ------------------------------------------
        Route::controller(ProductBatchController::class)->prefix('batches')->name('batches.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/product/{productId}', 'forProduct')->name('for-product');
            Route::get('/statistics', 'statistics')->name('statistics');
            Route::get('/expiry-report', 'expiryReport')->name('expiry-report');
            Route::post('/', 'store')->name('store');
            Route::put('/{productBatch}', 'update')->name('update');
            Route::delete('/{productBatch}', 'destroy')->name('destroy');
            Route::post('/{productBatch}/expire', 'markAsExpired')->name('expire');
            Route::post('/{productBatch}/recall', 'recall')->name('recall');
            Route::post('/{productBatch}/quarantine', 'quarantine')->name('quarantine');
            Route::post('/{productBatch}/adjust', 'adjustQuantity')->name('adjust');
        });

        // ------------------------------------------
        // POS: Warranties
        // ------------------------------------------
        Route::controller(WarrantyController::class)->prefix('warranties')->name('warranties.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/all', 'all')->name('all');
            Route::post('/', 'store')->name('store');
            Route::put('/{warranty}', 'update')->name('update');
            Route::delete('/{warranty}', 'destroy')->name('destroy');
        });

        // ------------------------------------------
        // POS: Warranty Claims
        // ------------------------------------------
        Route::controller(WarrantyController::class)->prefix('warranty-claims')->name('warranty-claims.')->group(function () {
            Route::get('/', 'claimsIndex')->name('index');
            Route::post('/listing', 'claimsListing')->name('listing');
            Route::get('/statistics', 'claimStatistics')->name('statistics');
            Route::post('/', 'storeClaim')->name('store');
            Route::get('/{warrantyClaim}', 'showClaim')->name('show');
            Route::put('/{warrantyClaim}', 'updateClaim')->name('update');
            Route::delete('/{warrantyClaim}', 'destroyClaim')->name('destroy');
        });

        // ------------------------------------------
        // POS: Custom Fields
        // ------------------------------------------
        Route::controller(CustomFieldController::class)->prefix('custom-fields')->name('custom-fields.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/metadata', 'getMetadata')->name('metadata');
            Route::get('/entity/{entityType}', 'forEntity')->name('for-entity');
            Route::post('/', 'store')->name('store');
            Route::put('/{customField}', 'update')->name('update');
            Route::delete('/{customField}', 'destroy')->name('destroy');
        });

        // ------------------------------------------
        // POS: Discount Rules
        // ------------------------------------------
        Route::controller(DiscountRuleController::class)->prefix('discount-rules')->name('discount-rules.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::post('/', 'store')->name('store');
            Route::put('/{discountRule}', 'update')->name('update');
            Route::delete('/{discountRule}', 'destroy')->name('destroy');
        });

        // ------------------------------------------
        // POS: Coupons
        // ------------------------------------------
        Route::controller(CouponController::class)->prefix('coupons')->name('coupons.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/generate-code', 'generateCode')->name('generate-code');
            Route::post('/validate', 'validate')->name('validate');
            Route::post('/', 'store')->name('store');
            Route::put('/{coupon}', 'update')->name('update');
            Route::delete('/{coupon}', 'destroy')->name('destroy');
            Route::get('/{coupon}/statistics', 'statistics')->name('statistics');
        });

        // ------------------------------------------
        // POS: Customer Ledger (Credit Management)
        // ------------------------------------------
        Route::controller(CustomerLedgerController::class)->prefix('customer-ledger')->name('customer-ledger.')->group(function () {
            Route::get('/outstanding', 'outstanding')->name('outstanding');
            Route::get('/{customer}', 'show')->name('show');
            Route::post('/{customer}/listing', 'listing')->name('listing');
            Route::get('/{customer}/statement', 'statement')->name('statement');
            Route::get('/{customer}/aging', 'aging')->name('aging');
            Route::post('/{customer}/collect-payment', 'collectPayment')->name('collect-payment');
            Route::post('/{customer}/adjust', 'adjust')->name('adjust');
            Route::post('/{customer}/recalculate', 'recalculate')->name('recalculate');
        });

        // ------------------------------------------
        // POS: Cheques
        // ------------------------------------------
        Route::controller(ChequeController::class)->prefix('cheques')->name('cheques.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/summary', 'summary')->name('summary');
            Route::post('/', 'store')->name('store');
            Route::put('/{cheque}', 'update')->name('update');
            Route::delete('/{cheque}', 'destroy')->name('destroy');
            Route::post('/{cheque}/deposit', 'deposit')->name('deposit');
            Route::post('/{cheque}/clear', 'clear')->name('clear');
            Route::post('/{cheque}/bounce', 'bounce')->name('bounce');
            Route::post('/{cheque}/cancel', 'cancel')->name('cancel');
        });

        // ------------------------------------------
        // POS: Cash Registers
        // ------------------------------------------
        Route::controller(CashRegisterController::class)->prefix('cash-registers')->name('cash-registers.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/current', 'current')->name('current');
            Route::get('/denominations', 'denominations')->name('denominations');
            Route::get('/daily-report', 'dailyReport')->name('daily-report');
            Route::post('/open', 'open')->name('open');
            Route::post('/{cashRegister}/close', 'close')->name('close');
            Route::post('/{cashRegister}/pay-in', 'payIn')->name('pay-in');
            Route::post('/{cashRegister}/pay-out', 'payOut')->name('pay-out');
            Route::get('/{cashRegister}/summary', 'summary')->name('summary');
            Route::get('/{cashRegister}/transactions', 'transactions')->name('transactions');
        });

        // ------------------------------------------
        // POS: Locations
        // ------------------------------------------
        Route::controller(LocationController::class)->prefix('locations')->name('locations.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
            Route::get('/all', 'all')->name('all');
            Route::post('/', 'store')->name('store');
            Route::put('/{location}', 'update')->name('update');
            Route::delete('/{location}', 'destroy')->name('destroy');
            Route::get('/{location}/stock-summary', 'stockSummary')->name('stock-summary');
        });

        // ------------------------------------------
        // POS: Expenses
        // ------------------------------------------
        Route::controller(ExpenseController::class)->prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/listing', 'listing')->name('listing');
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
            Route::post('/listing', 'listing')->name('listing');
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
            Route::post('/listing', 'listing')->name('listing');
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
        Route::post('/listing', 'listing')->name('listing');
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
        Route::post('/listing', 'listing')->name('listing');
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
    Route::get('/products', [ProductController::class, 'index'])->name('products.page');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.page');
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
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.page');
    Route::get('/stores', fn () => inertia('Stores/Index'))->name('stores.page');

    // ==========================================
    // Roles Management
    // ==========================================
    Route::controller(RoleController::class)->prefix('roles')->name('role.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/listing', 'listing')->name('listing');
        Route::get('/permissions', 'permissions')->name('permissions');
        Route::post('/permissions', 'storePermissions')->name('storePermissions');
        Route::post('/', 'store')->name('store');
        Route::get('/{role}', 'edit')->name('edit');
        Route::put('/{role}', 'update')->name('update');
        Route::delete('/{role}', 'destroy')->name('delete');
        Route::post('/{role}/clone', 'clone')->name('clone');
        Route::get('/all/permissions', 'getPermissions')->name('getPermissions');
    });

    // ==========================================
    // Users Management
    // ==========================================
    Route::controller(UserController::class)->prefix('users')->name('user.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/listing', 'listing')->name('listing');
        Route::post('/', 'store')->name('store');
        Route::get('/{user}', 'edit')->name('edit');
        Route::put('/{user}', 'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('delete');
        Route::put('/{user}/password', 'updatePassword')->name('updatePassword');
        Route::post('/{id}/restore', 'restore')->name('restore');
    });

    // Redirect root to dashboard
    Route::get('/', fn () => redirect()->route('dashboard'));
});
