<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Orders indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['store_id', 'status', 'created_at'], 'orders_store_status_date_idx');
            $table->index(['customer_id', 'created_at'], 'orders_customer_date_idx');
            $table->index(['user_id', 'created_at'], 'orders_user_date_idx');
            $table->index(['payment_status', 'created_at'], 'orders_payment_status_date_idx');
        });

        // Store inventories indexes
        Schema::table('store_inventories', function (Blueprint $table) {
            $table->index(['store_id', 'product_id'], 'store_inv_store_product_idx');
            $table->index(['product_variant_id'], 'store_inv_variant_idx');
        });

        // Inventory movements indexes
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->index(['store_id', 'product_id', 'created_at'], 'inv_mov_store_product_date_idx');
            $table->index(['type', 'created_at'], 'inv_mov_type_date_idx');
        });

        // Products indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index(['category_id', 'is_active'], 'products_category_active_idx');
            $table->index(['vendor_id'], 'products_vendor_idx');
            $table->index(['is_active', 'created_at'], 'products_active_date_idx');
        });

        // Order items indexes
        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['product_id', 'created_at'], 'order_items_product_date_idx');
        });

        // Customers indexes
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'customers_status_date_idx');
            $table->index(['phone'], 'customers_phone_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_store_status_date_idx');
            $table->dropIndex('orders_customer_date_idx');
            $table->dropIndex('orders_user_date_idx');
            $table->dropIndex('orders_payment_status_date_idx');
        });

        Schema::table('store_inventories', function (Blueprint $table) {
            $table->dropIndex('store_inv_store_product_idx');
            $table->dropIndex('store_inv_variant_idx');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex('inv_mov_store_product_date_idx');
            $table->dropIndex('inv_mov_type_date_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_category_active_idx');
            $table->dropIndex('products_vendor_idx');
            $table->dropIndex('products_active_date_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_product_date_idx');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_status_date_idx');
            $table->dropIndex('customers_phone_idx');
        });
    }
};
