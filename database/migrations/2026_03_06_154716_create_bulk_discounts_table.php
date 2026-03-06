<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Bulk/Volume Discounts - "Buy X get Y% off" rules
        Schema::create('bulk_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('description')->nullable();

            // What this discount applies to (one of these)
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('brand_id')->nullable(); // Brands table may not exist

            // Quantity thresholds
            $table->decimal('min_quantity', 10, 3);
            $table->decimal('max_quantity', 10, 3)->nullable();

            // Discount amount
            $table->enum('discount_type', ['fixed', 'percentage'])->default('percentage');
            $table->decimal('discount_amount', 10, 2);

            // Optional restrictions
            $table->foreignId('selling_price_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_group_id')->nullable()->constrained()->nullOnDelete();

            // Priority for overlapping rules (higher = applied first)
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index for fast lookups
            $table->index(['product_id', 'is_active', 'starts_at', 'ends_at']);
            $table->index(['category_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_discounts');
    }
};
