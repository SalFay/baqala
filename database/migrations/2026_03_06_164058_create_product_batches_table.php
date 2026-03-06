<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();

            // Batch Information
            $table->string('batch_number');
            $table->string('lot_number')->nullable();

            // Dates
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date');

            // Purchase Information
            $table->foreignId('purchase_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->date('received_date')->nullable();

            // Quantity tracking
            $table->decimal('quantity_purchased', 12, 4)->default(0);
            $table->decimal('quantity_available', 12, 4)->default(0);
            $table->decimal('quantity_sold', 12, 4)->default(0);
            $table->decimal('quantity_damaged', 12, 4)->default(0);
            $table->decimal('quantity_expired', 12, 4)->default(0);

            // Status
            $table->enum('status', ['active', 'low_stock', 'out_of_stock', 'expired', 'recalled', 'quarantine'])
                ->default('active');

            // Additional info
            $table->string('supplier_batch_ref')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['product_id', 'status']);
            $table->index('expiry_date');
            $table->index('batch_number');
            $table->unique(['product_id', 'batch_number']);
        });

        // Add enable_batch_tracking and enable_expiry_tracking to products
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('enable_batch_tracking')->default(false)->after('enable_serial_tracking');
            $table->boolean('enable_expiry_tracking')->default(false)->after('enable_batch_tracking');
            $table->integer('expiry_warning_days')->default(30)->after('enable_expiry_tracking');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['enable_batch_tracking', 'enable_expiry_tracking', 'expiry_warning_days']);
        });

        Schema::dropIfExists('product_batches');
    }
};
