<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('current_status_id')->nullable()->after('status')->constrained('statuses')->nullOnDelete();
            $table->foreignId('created_by_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->after('created_by_id')->constrained('users')->nullOnDelete();
            $table->string('invoice_no')->nullable()->after('order_number');
            $table->string('customer_name')->nullable()->after('notes');
            $table->string('cashier_name')->nullable()->after('customer_name');
            $table->decimal('sub_total', 12, 2)->default(0)->after('subtotal');
            $table->decimal('vat', 12, 2)->default(0)->after('tax_amount');
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable()->after('discount');
            $table->string('payment_type')->nullable()->after('payment_status');
            $table->date('date')->nullable()->after('status');
        });

        // Add to purchase_orders table
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('current_status_id')->nullable()->after('status')->constrained('statuses')->nullOnDelete();
            $table->foreignId('created_by_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->after('created_by_id')->constrained('users')->nullOnDelete();
        });

        // Add to stock_transfers table
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->foreignId('current_status_id')->nullable()->after('status')->constrained('statuses')->nullOnDelete();
            $table->foreignId('created_by_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->after('created_by_id')->constrained('users')->nullOnDelete();
        });

        // Add to order_returns table
        Schema::table('order_returns', function (Blueprint $table) {
            $table->foreignId('current_status_id')->nullable()->after('status')->constrained('statuses')->nullOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_status_id');
            $table->dropConstrainedForeignId('created_by_id');
            $table->dropConstrainedForeignId('updated_by_id');
            $table->dropColumn(['invoice_no', 'customer_name', 'cashier_name', 'sub_total', 'vat', 'discount_type', 'payment_type', 'date']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_status_id');
            $table->dropConstrainedForeignId('created_by_id');
            $table->dropConstrainedForeignId('updated_by_id');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_status_id');
            $table->dropConstrainedForeignId('created_by_id');
            $table->dropConstrainedForeignId('updated_by_id');
        });

        Schema::table('order_returns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_status_id');
            $table->dropConstrainedForeignId('created_by_id');
            $table->dropConstrainedForeignId('updated_by_id');
        });
    }
};
