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
        // Selling Price Groups (e.g., Retail, Wholesale, VIP)
        Schema::create('selling_price_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('price_calculation_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('price_calculation_amount', 10, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot table: Products can have different prices in different price groups
        Schema::create('product_price_group_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('selling_price_group_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 15, 2);
            $table->decimal('price_inc_tax', 15, 2)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'product_variant_id', 'selling_price_group_id'], 'product_price_group_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_price_group_prices');
        Schema::dropIfExists('selling_price_groups');
    }
};
