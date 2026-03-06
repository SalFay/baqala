<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();

            // Serial/IMEI Information
            $table->string('serial_number')->unique();
            $table->string('imei')->nullable()->unique();
            $table->string('imei2')->nullable()->unique(); // For dual-SIM devices

            // Purchase Information
            $table->foreignId('purchase_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->date('purchase_date')->nullable();

            // Sale Information
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->timestamp('sold_at')->nullable();

            // Status tracking
            $table->enum('status', ['available', 'reserved', 'sold', 'returned', 'damaged', 'lost'])
                ->default('available');

            // Warranty
            $table->unsignedBigInteger('warranty_id')->nullable();
            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();

            // Additional info
            $table->string('color')->nullable();
            $table->string('storage_capacity')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common lookups
            $table->index(['product_id', 'status']);
            $table->index('status');
            $table->index('purchase_date');
            $table->index('sold_at');
        });

        // Add enable_serial_tracking to products
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('enable_serial_tracking')->default(false)->after('track_inventory');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('enable_serial_tracking');
        });

        Schema::dropIfExists('product_serials');
    }
};
