<?php
		
		use App\Http\Controllers\Admin\DashboardController;
		use App\Http\Controllers\Admin\InventoryController;
		use App\Http\Controllers\Admin\OrderController;
		use App\Http\Controllers\Admin\OrderUpdateController;
		use App\Http\Controllers\Admin\PaymentController;
		use App\Http\Controllers\Admin\ReportController;
		use App\Http\Controllers\Admin\SettingController;
		use App\Http\Controllers\Admin\StockController;
		use App\Http\Controllers\Api\AccountController;
		use App\Http\Controllers\Api\BankController;
		use App\Http\Controllers\Api\CategoryController;
		use App\Http\Controllers\Api\CustomerController;
		use App\Http\Controllers\Api\PaymentMethodsController;
		use App\Http\Controllers\Api\ProductController;
		use App\Http\Controllers\Api\RoleController;
		use App\Http\Controllers\Api\Select2Controller;
		use App\Http\Controllers\Api\UserController;
		use App\Http\Controllers\Api\VendorController;
		use App\Http\Controllers\LogController;
		use App\Http\Controllers\PointOfSale;
		use App\Http\Controllers\SMSController;
		use Illuminate\Support\Facades\Route;
		
		Route::get( '/',
				[ DashboardController::class, 'index' ] )->name( 'dashboard' )->middleware( 'can:access dashboard' );
		/**
			* Invoices
			*/
		Route::get( '/customer-invoices',
				[ DashboardController::class, 'customerInvoices' ] )->name( 'customer-invoices' )->middleware( 'can:access invoices' );
		
		Route::post( '/customerAjax', [ DashboardController::class, 'customerInvoices' ] )
		     ->name( 'customerAjax' )->middleware( 'can:access invoices' );
		
		Route::get( '/vendor-invoices',
				[ DashboardController::class, 'vendorInvoices' ] )->name( 'vendor-invoices' )->middleware( 'can:access invoices' );
		
		Route::post( '/vendorAjax', [ DashboardController::class, 'vendorInvoices' ] )
		     ->name( 'vendorAjax' )->middleware( 'can:access invoices' );
		
		/**
			* Users
			*/
		Route::prefix( 'users' )->group( function() {
				Route::get( '/', [ \App\Http\Controllers\Admin\UserController::class, 'index' ] )
				     ->name( 'admin.users' )->middleware( 'can:access users' );
				
				Route::post( '/ajax', [ \App\Http\Controllers\Admin\UserController::class, 'index' ] )
				     ->name( 'users.ajax' )->middleware( 'can:access users' );
				
				Route::post( '/add', [ UserController::class, 'store' ] )
				     ->name( 'users.add' )->middleware( 'can:add user' );
				
				Route::get( '/edit/{user}', [ \App\Http\Controllers\Admin\UserController::class, 'edit' ] )
				     ->name( 'users.edit' )->middleware( 'can:update user' );
				
				Route::post( '/edit/{user}', [ UserController::class, 'update' ] )
				     ->name( 'users.update' )->middleware( 'can:update user' );
				
				Route::put( '/verify/{user}', [ UserController::class, 'verify' ] )
				     ->name( 'users.verify' )->middleware( 'can:verify user' );
				
				Route::post( '/delete/{user}', [ UserController::class, 'destroy' ] )
				     ->name( 'users.delete' )->middleware( 'can:delete user' );
				
		} );
		
		/**
			* Customers
			*/
		Route::prefix( 'customers' )->group( function() {
				Route::get( '/', [ \App\Http\Controllers\Admin\CustomerController::class, 'index' ] )
				     ->name( 'admin.customers' )->middleware( 'can:access customers' );
				
				Route::post( '/ajax', [ \App\Http\Controllers\Admin\CustomerController::class, 'index' ] )
				     ->name( 'customers.ajax' )->middleware( 'can:access customers' );
				
				Route::post( '/add', [ CustomerController::class, 'store' ] )
				     ->name( 'customers.add' )->middleware( 'can:add customer' );
				
				Route::get( '/edit/{customer}', [ \App\Http\Controllers\Admin\CustomerController::class, 'edit' ] )
				     ->name( 'customers.edit' )->middleware( 'can:update customer' );
				
				Route::get( '/statement/{customer}', [ CustomerController::class, 'statement' ] )
				     ->name( 'customers.statement' )->middleware( 'can:view customer statement' );
				
				Route::post( '/edit/{customer}', [ CustomerController::class, 'update' ] )
				     ->name( 'customers.update' )->middleware( 'can:update customer' );
				
				Route::post( '/delete/{customer}', [ CustomerController::class, 'destroy' ] )
				     ->name( 'customers.delete' )->middleware( 'can:delete customer' );
				
				Route::get( '/payments/{customer}', [ PaymentController::class, 'index' ] )
				     ->name( 'customers.payments' )->middleware( 'can:access paymentMethods' );
				
				Route::get( '/orders/{customer}',
						[ OrderController::class, 'index' ] )
				     ->name( 'customers.orders' )->middleware( 'can:add order' );
				
		} );
		
		//Payments Method
		
		Route::prefix( 'payments' )->group( function() {
				
				Route::post( '/add', [ PaymentMethodsController::class, 'store' ] )
				     ->name( 'payments.add' )->middleware( 'can:add paymentMethod' );
				
				Route::get( '/edit/{payment}', [ PaymentMethodsController::class, 'edit' ] )
				     ->name( 'payments.edit' )->middleware( 'can:update paymentMethod' );
				
				Route::post( '/edit/{payment}', [ PaymentMethodsController::class, 'update' ] )
				     ->name( 'payments.update' )->middleware( 'can:update paymentMethod' );
				
				Route::post( '/delete/{payment}', [ PaymentMethodsController::class, 'destroy' ] )
				     ->name( 'payments.delete' )->middleware( 'can:delete paymentMethod' );
				
		} );
		
		/**
			* Vendors
			*/
		Route::prefix( 'vendors' )->group( function() {
				Route::get( '/', [ \App\Http\Controllers\Admin\VendorController::class, 'index' ] )
				     ->name( 'admin.vendors' )->middleware( 'can:access vendors' );
				
				Route::post( '/ajax', [ \App\Http\Controllers\Admin\VendorController::class, 'index' ] )
				     ->name( 'vendors.ajax' )->middleware( 'can:access vendors' );
				
				Route::post( '/add', [ VendorController::class, 'store' ] )
				     ->name( 'vendors.add' )->middleware( 'can:add vendor' );
				
				Route::get( '/edit/{vendor}', [ \App\Http\Controllers\Admin\VendorController::class, 'edit' ] )
				     ->name( 'vendors.edit' )->middleware( 'can:update vendor' );
				
				Route::post( '/edit/{vendor}', [ VendorController::class, 'update' ] )
				     ->name( 'vendors.update' )->middleware( 'can:update vendor' );
				
				Route::get( '/statement/{vendor}', [ VendorController::class, 'statement' ] )
				     ->name( 'vendors.statement' )->middleware( 'can:view vendor statement' );
				
				Route::post( '/delete/{vendor}', [ VendorController::class, 'destroy' ] )
				     ->name( 'vendors.delete' )->middleware( 'can:delete vendor' );
				
				Route::get( '/payments/{vendor}', [ PaymentController::class, 'vendorIndex' ] )
				     ->name( 'vendors.payments' )->middleware( 'can:access paymentMethods' );
				
		} );
		
		/**
			* Products
			*/
		Route::prefix( 'products' )->group( function() {
				Route::get( '/', [ \App\Http\Controllers\Admin\ProductController::class, 'index' ] )
				     ->name( 'admin.products' )->middleware( 'can:access products' );
				
				Route::post( '/ajax', [ \App\Http\Controllers\Admin\ProductController::class, 'index' ] )
				     ->name( 'products.ajax' )->middleware( 'can:access products' );
				
				Route::post( '/get', [ OrderController::class, 'get' ] )
				     ->name( 'products.get' )->middleware( 'can:access products' );
				
				Route::post( '/add', [ ProductController::class, 'store' ] )
				     ->name( 'products.add' )->middleware( 'can:add product' );
				
				Route::get( '/print', [ \App\Http\Controllers\Admin\ProductController::class, 'print' ] )
				     ->name( 'products.print' )->middleware( 'can:print barcode' );
				
				Route::get( '/sticker/{product}', [ \App\Http\Controllers\Admin\ProductController::class, 'sticker' ] )
				     ->name( 'products.sticker' )->middleware( 'can:print sticker' );
				
				Route::get( '/edit/{product}', [ \App\Http\Controllers\Admin\ProductController::class, 'edit' ] )
				     ->name( 'products.edit' )->middleware( 'can:update product' );
				
				Route::post( '/edit/{product}', [ ProductController::class, 'update' ] )
				     ->name( 'products.update' )->middleware( 'can:update product' );
				
				Route::post( '/delete/{product}', [ ProductController::class, 'destroy' ] )
				     ->name( 'products.delete' )->middleware( 'can:delete product' );
				
		} );
		
		/**
			* Categories
			*/
		Route::prefix( 'categories' )->group( function() {
				Route::get( '/', [ \App\Http\Controllers\Admin\CategoryController::class, 'index' ] )
				     ->name( 'admin.categories' )->middleware( 'can:access categories' );
				
				Route::post( '/ajax', [ \App\Http\Controllers\Admin\CategoryController::class, 'index' ] )
				     ->name( 'categories.ajax' )->middleware( 'can:access categories' );
				
				Route::post( '/add', [ CategoryController::class, 'store' ] )
				     ->name( 'categories.add' )->middleware( 'can:add category' );
				
				Route::get( '/edit/{category}', [ \App\Http\Controllers\Admin\CategoryController::class, 'edit' ] )
				     ->name( 'categories.edit' )->middleware( 'can:update category' );
				
				Route::post( '/edit/{category}', [ CategoryController::class, 'update' ] )
				     ->name( 'categories.update' )->middleware( 'can:update category' );
				
				Route::post( '/delete/{category}', [ CategoryController::class, 'destroy' ] )
				     ->name( 'categories.delete' )->middleware( 'can:delete category' );
				
		} );
		
		/**
			* Roles
			*/
		Route::prefix( 'roles' )->group( function() {
				Route::get( '/', [ \App\Http\Controllers\Admin\RoleController::class, 'index' ] )
				     ->name( 'admin.roles' )->middleware( 'can:access roles' );
				
				Route::post( '/ajax', [ \App\Http\Controllers\Admin\RoleController::class, 'indexAjax' ] )
				     ->name( 'roles.ajax' )->middleware( 'can:access roles' );
				
				Route::post( '/add', [ RoleController::class, 'store' ] )
				     ->name( 'roles.add' )->middleware( 'can:add role' );
				
				Route::get( '/edit/{role}', [ \App\Http\Controllers\Admin\RoleController::class, 'edit' ] )
				     ->name( 'roles.edit' )->middleware( 'can:update roles' );
				
				Route::post( '/edit/{role}', [ RoleController::class, 'update' ] )
				     ->name( 'users.update' )->middleware( 'can:update roles' );
				
				Route::post( '/delete/{role}', [ RoleController::class, 'destroy' ] )
				     ->name( 'roles.delete' )->middleware( 'can:delete roles' );
				
				Route::get( '/permissions/{role}', [ \App\Http\Controllers\Admin\RoleController::class, 'editPermissions' ] )
				     ->name( 'roles.permissions.edit' )->middleware( 'can:update permissions' );
				
				Route::post( '/permissions/{role}', [ \App\Http\Controllers\Admin\RoleController::class, 'updatePermissions' ] )
				     ->name( 'roles.permissions.update' )->middleware( 'can:update permissions' );
				
		} );
		
		/**
			* Stock
			*/
		Route::prefix( 'inventories' )->group( function() {
				Route::post( '/products', [ InventoryController::class, 'products' ] )
				     ->name( 'inventories.products' );
		} );
		
		Route::prefix( 'select2' )->group( static function() {
				Route::post( 'roles',
						[ Select2Controller::class, 'roles' ] )->name( 'select2.roles' );
				Route::post( 'categories',
						[ Select2Controller::class, 'categories' ] )->name( 'select2.categories' );
				Route::post( 'products',
						[ Select2Controller::class, 'products' ] )->name( 'select2.products' );
				Route::post( 'vendors',
						[ Select2Controller::class, 'vendors' ] )->name( 'select2.vendors' );
				
				Route::post( 'customers',
						[ Select2Controller::class, 'customers' ] )->name( 'select2.customers' );
				
				Route::post( 'status',
						[ Select2Controller::class, 'status' ] )->name( 'select2.status' );
				
				Route::post( 'countries',
						[ Select2Controller::class, 'countries' ] )->name( 'select2.countries' );
				
				Route::post( 'paymentMethods',
						[ Select2Controller::class, 'paymentMethods' ] )->name( 'select2.paymentMethods' );
				
				Route::post( 'vendorPayment/{id}',
						[ Select2Controller::class, 'vendorPayment' ] )->name( 'select2.vendorPayment' );
				Route::post( 'customerPayment/{id}',
						[ Select2Controller::class, 'customerPayment' ] )->name( 'select2.customerPayment' );
				Route::post( 'banks',
						[ Select2Controller::class, 'banks' ] )->name( 'select2.banks' );
				Route::post( 'expense',
						[ Select2Controller::class, 'expense' ] )->name( 'select2.expense' );
				
		} );
		
		/* --------------------------------------------------------------
			*  Settings
			* --------------------------------------------------------------
			*/
		Route::prefix( 'settings' )->group( static function() {
				Route::get( '/', [ SettingController::class, 'index' ] )
				     ->name( 'setup' )->middleware( 'can:access settings' );
				
				Route::get( '/roles', [ SettingController::class, 'rolesIndex' ] )
				     ->name( 'setup.roles' )->middleware( 'can:access settings' );
				
				Route::post( '/roles', [ SettingController::class, 'rolesUpdate' ] )
				     ->name( 'setup.roles.update' )->middleware( 'can:access settings' );
				
				Route::get( '/redirects', [ SettingController::class, 'redirectionIndex' ] )
				     ->name( 'setup.redirects' )->middleware( 'can:access settings' );
				
				Route::post( '/redirects', [ SettingController::class, 'redirectionUpdate' ] )
				     ->name( 'setup.redirects.update' )->middleware( 'can:access settings' );
				
				Route::get( '/general', [ SettingController::class, 'generalIndex' ] )
				     ->name( 'setup.general' )->middleware( 'can:access settings' );
				
				Route::post( '/general', [ SettingController::class, 'generalUpdate' ] )
				     ->name( 'setup.general.update' )->middleware( 'can:access settings' );
				
				Route::get( '/backups', [ SettingController::class, 'backups' ] )
				     ->name( 'setup.backups' )->middleware( 'can:access settings' );
				
				Route::get( '/download/{file}', [ SettingController::class, 'download' ] )
				     ->name( 'setup.backups.download' )->middleware( 'can:access settings' );
				
				Route::get( '/delete/{file}', [ SettingController::class, 'delete' ] )
				     ->name( 'setup.backups.delete' )->middleware( 'can:access settings' );
		} );
		
		/**
			* Inventory
			*/
		Route::prefix( 'inventory' )->group( function() {
				Route::get( 'invoice/{order}', [ StockController::class, 'invoice' ] )
				     ->name( 'inventory.invoice' )->middleware( 'can:access vendor invoice' );
				Route::prefix( 'add/' )->group( function() {
						Route::get( '/{vendor}', [ StockController::class, 'index' ] )
						     ->name( 'inventory.add' )->middleware( 'can:add inventory' );
						Route::post( '/{vendor}', [ StockController::class, 'store' ] )
						     ->name( 'inventory.add' )->middleware( 'can:add inventory' );
						Route::patch( '{vendor}/cart', [ StockController::class, 'showCart' ] )
						     ->name( 'inventory.show_cart' )->middleware( 'can:show inventory cart' );
						Route::post( '{vendor}/cart', [ StockController::class, 'addToCart' ] )
						     ->name( 'inventory.add_to_cart' )->middleware( 'can:add to inventory cart' );
						Route::post( '{vendor}/deleteFromCart', [ StockController::class, 'deleteFromCart' ] )
						     ->name( 'inventory.delete_from_cart' )->middleware( 'can:remove from inventory cart' );
						Route::post( '{vendor}/delete', [ StockController::class, 'emptyCart' ] )
						     ->name( 'inventory.empty_cart' )->middleware( 'can:empty inventory cart' );
				} );
		} );
		
		/**
			* Sale
			*/
		Route::prefix( 'order' )->group( function() {
				
				Route::post( '/products', [ InventoryController::class, 'customerProducts' ] )
				     ->name( 'order.products' );
				Route::get( 'invoice/{order}', [ OrderController::class, 'invoice' ] )
				     ->name( 'order.invoice' )->middleware( 'can:access customer invoice' );
				Route::post( '{order}/delete', [ OrderController::class, 'destroy' ] )
				     ->name( 'order.delete' )->middleware( 'can:access delete invoice' );
				Route::prefix( 'add/' )->group( function() {
						Route::get( '/{customer}', [ OrderController::class, 'index' ] )
						     ->name( 'order.add' )->middleware( 'can:add order' );
						
						Route::post( '/{customer}', [ OrderController::class, 'store' ] )
						     ->name( 'order.add' )->middleware( 'can:add order' );
						
						Route::patch( '{customer}/cart', [ OrderController::class, 'showCart' ] )
						     ->name( 'order.show_cart' )->middleware( 'can:show order cart' );
						Route::post( '{customer}/cart', [ OrderController::class, 'addToCart' ] )
						     ->name( 'order.add_to_cart' )->middleware( 'can:add to order cart' );
						Route::post( '{customer}/deleteFromCart', [ OrderController::class, 'deleteFromCart' ] )
						     ->name( 'order.delete_from_cart' )->middleware( 'can:remove from order cart' );
						Route::post( '{customer}/delete', [ OrderController::class, 'emptyCart' ] )
						     ->name( 'order.empty_cart' )->middleware( 'can:empty order cart' );
						
				} );
				
		} );
		Route::prefix( 'orders' )->group( function() {
				Route::get( 'edit/{order}', [ OrderUpdateController::class, 'edit' ] )
				     ->name( 'orders.edit' )->middleware( 'can:update customer invoice' );
				Route::post( '/{order}', [ OrderUpdateController::class, 'update' ] )
				     ->name( 'orders.update' )->middleware( 'can:update customer invoice' );
				Route::patch( '{order}/cart', [ OrderUpdateController::class, 'showCart' ] )
				     ->name( 'orders.show_cart' )->middleware( 'can:show order cart' );
				Route::post( '{order}/cart', [ OrderUpdateController::class, 'addToCart' ] )
				     ->name( 'orders.add_to_cart' )->middleware( 'can:add to order cart' );
				Route::post( '{order}/deleteFromCart', [ OrderUpdateController::class, 'deleteFromCart' ] )
				     ->name( 'orders.delete_from_cart' )->middleware( 'can:remove from order cart' );
				Route::post( '{order}/delete', [ OrderUpdateController::class, 'emptyCart' ] )
				     ->name( 'orders.empty_cart' )->middleware( 'can:empty order cart' );
				
		} );
		
		/**
			* POS
			*/
		
		Route::prefix( 'pos' )->group( function() {
				Route::get( '/', [ PointOfSale::class, 'index' ] )
				     ->name( 'admin.pos' )->middleware( 'can:show pos' );
		} );
		
		/**
			* Reports
			*/
		Route::prefix( 'reports' )->group( function() {
				Route::get( '/available-stock', [ ReportController::class, 'availableStock' ] )
				     ->name( 'reports.available-stock' )->middleware( 'can:access stocks report' );
				Route::post( '/available-stock', [ ReportController::class, 'availableStock' ] )
				     ->name( 'reports.available-stock.ajax' )->middleware( 'can:access stocks report' );
				
				Route::get( '/order', [ ReportController::class, 'orders' ] )
				     ->name( 'reports.order' )->middleware( 'can:access orders report' );
    Route::post( '/order', [ ReportController::class, 'orders' ] )
         ->name( 'reports.order.ajax' )->middleware( 'can:access orders report' );
				Route::get( '/order-items', [ ReportController::class, 'ordersItem' ] )
				     ->name( 'reports.order-items' )->middleware( 'can:access profit report' );
				Route::get( '/stock', [ ReportController::class, 'stock' ] )
				     ->name( 'reports.stock' )->middleware( 'can:access stock invoices report' );
				Route::get( '/inventory', [ ReportController::class, 'inventory' ] )
				     ->name( 'reports.inventory' )->middleware( 'can:access inventory report' );
				Route::post( '/inventory', [ ReportController::class, 'inventory' ] )
				     ->name( 'reports.inventory.ajax' )->middleware( 'can:access inventory report' );
		} );
		
		/**
			* Account s- Credit / Debit
			*/
		Route::prefix( 'accounts' )->group( function() {
				Route::get( '/', [ \App\Http\Controllers\Admin\AccountController::class, 'index' ] )
				     ->name( 'admin.accounts' )->middleware( 'can:access accounts' );
				
				Route::post( '/ajax', [ \App\Http\Controllers\Admin\AccountController::class, 'index' ] )
				     ->name( 'accounts.ajax' )->middleware( 'can:access accounts' );
				
				Route::post( '/add', [ AccountController::class, 'store' ] )
				     ->name( 'accounts.add' )->middleware( 'can:add account' );
				
				Route::post( '/expenses', [ AccountController::class, 'expenses' ] )
				     ->name( 'accounts.expenses' )->middleware( 'can:add expenses' );
				
				Route::post( '/cash', [ AccountController::class, 'cash' ] )
				     ->name( 'accounts.cash' )->middleware( 'can:add cash' );
				
				Route::post( '/transfer', [ AccountController::class, 'transfer' ] )
				     ->name( 'accounts.transfer' )->middleware( 'can:add transfer' );
				
				Route::get( '/edit/{account}', [ \App\Http\Controllers\Admin\AccountController::class, 'edit' ] )
				     ->name( 'accounts.edit' )->middleware( 'can:update account' );
				
				Route::post( '/edit/{account}', [ AccountController::class, 'update' ] )
				     ->name( 'accounts.update' )->middleware( 'can:update account' );
				
				Route::post( '/delete/{account}', [ AccountController::class, 'destroy' ] )
				     ->name( 'accounts.delete' )->middleware( 'can:delete account' );
				
		} );
		
		/**
			* Banks
			*/
		Route::prefix( 'bank' )->group( function() {
				Route::get( '/', [ \App\Http\Controllers\Admin\BankController::class, 'index' ] )
				     ->name( 'admin.bank' )->middleware( 'can:access banks' );
				
				Route::post( '/ajax', [ \App\Http\Controllers\Admin\BankController::class, 'index' ] )
				     ->name( 'bank.ajax' )->middleware( 'can:access banks' );
				
				Route::post( '/add', [ BankController::class, 'store' ] )
				     ->name( 'bank.add' )->middleware( 'can:add bank' );
				
				Route::get( '/edit/{bank}', [ \App\Http\Controllers\Admin\BankController::class, 'edit' ] )
				     ->name( 'bank.edit' )->middleware( 'can:update bank' );
				
				Route::get( '/statement/{bank}', [ BankController::class, 'statement' ] )
				     ->name( 'bank.statement' )->middleware( 'can:view bank statement' );
				
				Route::post( '/edit/{bank}', [ BankController::class, 'update' ] )
				     ->name( 'bank.update' )->middleware( 'can:update bank' );
				
				Route::post( '/delete/{bank}', [ BankController::class, 'destroy' ] )
				     ->name( 'bank.delete' )->middleware( 'can:delete bank' );
				
		} );
		
		Route::prefix( 'sms' )->group( function() {
				Route::get( '/', [ SMSController::class, 'index' ] )
				     ->name( 'admin.numbers.list' );
				Route::get( '/emails', [ SMSController::class, 'emails' ] )
				     ->name( 'admin.numbers.emails' );
				Route::get( '/file', [ SMSController::class, 'file' ] )
				     ->name( 'admin.sms.file' );
				Route::post( '/send-message', [ SMSController::class, 'sendMessage' ] )
				     ->name( 'admin.sms.send-message' );
				Route::post( '/sendMessages', [ SMSController::class, 'sendMessageToUsers' ] )
				     ->name( 'admin.sendMessages' );
				
		} );
		
		Route::prefix( 'logs' )->group( function() {
				
				Route::get( '/', [ LogController::class, 'index' ] )
				     ->name( 'admin.logs' )->middleware( 'can:access logs' );
				
				Route::post( '/filter', [ LogController::class, 'filter' ] )
				     ->name( 'admin.logs.filter' );
				
		} );

