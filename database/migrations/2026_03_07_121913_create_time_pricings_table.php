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
        Schema::create('time_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();

            // Discount configuration
            $table->enum('discount_type', ['percentage', 'fixed', 'special_price'])->default('percentage');
            $table->decimal('discount_value', 10, 2);

            // Scope: what products this applies to
            $table->enum('applies_to', ['all', 'products', 'categories', 'brands'])->default('all');
            $table->json('product_ids')->nullable();
            $table->json('category_ids')->nullable();
            $table->json('brand_ids')->nullable();

            // Time schedule
            $table->json('days_of_week')->nullable(); // [1,2,3,4,5,6,7] = Mon-Sun
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Date range
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->index(['start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_pricings');
    }
};
