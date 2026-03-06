<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Discount Rules (automatic discounts)
        Schema::create('discount_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();

            // Discount type and amount
            $table->enum('discount_type', ['fixed', 'percentage'])->default('percentage');
            $table->decimal('discount_amount', 12, 2);

            // What does it apply to?
            $table->enum('applies_to', ['all', 'category', 'brand', 'product', 'customer_group'])->default('all');
            $table->json('applies_to_ids')->nullable(); // Array of category/brand/product IDs

            // Conditions (JSON structure for complex rules)
            $table->json('conditions')->nullable();
            /*
                {
                    "min_quantity": 5,
                    "min_total": 100.00,
                    "customer_group_ids": [1, 2],
                    "payment_method_ids": [1],
                    "days_of_week": [1, 2, 3, 4, 5],
                    "time_range": {"start": "09:00", "end": "17:00"}
                }
            */

            // Stacking and priority
            $table->integer('priority')->default(0); // Higher = applied first
            $table->boolean('is_stackable')->default(false); // Can combine with other discounts
            $table->boolean('stop_further_rules')->default(false); // Stop processing after this rule

            // Limits
            $table->integer('max_uses')->nullable(); // null = unlimited
            $table->integer('max_uses_per_customer')->nullable();
            $table->integer('current_uses')->default(0);

            // Validity period
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->index('priority');
        });

        // Promotions (marketing campaigns with multiple discount rules)
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('promotion_type'); // buy_x_get_y, bundle, clearance, seasonal, flash_sale

            // Buy X Get Y configuration
            $table->json('buy_x_get_y_config')->nullable();
            /*
                {
                    "buy_quantity": 2,
                    "buy_product_ids": [1, 2, 3],
                    "get_quantity": 1,
                    "get_product_ids": [4, 5],
                    "discount_type": "percentage",
                    "discount_amount": 100
                }
            */

            // Bundle configuration
            $table->json('bundle_config')->nullable();
            /*
                {
                    "products": [
                        {"product_id": 1, "quantity": 1},
                        {"product_id": 2, "quantity": 2}
                    ],
                    "bundle_price": 99.99
                }
            */

            // Limits
            $table->integer('max_uses')->nullable();
            $table->integer('max_uses_per_customer')->nullable();
            $table->integer('current_uses')->default(0);

            // Validity
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Coupons (codes that customers can apply)
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->text('description')->nullable();

            // Discount
            $table->enum('discount_type', ['fixed', 'percentage', 'free_shipping'])->default('percentage');
            $table->decimal('discount_amount', 12, 2)->nullable();

            // What does it apply to?
            $table->enum('applies_to', ['all', 'category', 'brand', 'product'])->default('all');
            $table->json('applies_to_ids')->nullable();

            // Conditions
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->decimal('max_discount_amount', 12, 2)->nullable(); // Cap for percentage discounts
            $table->json('customer_ids')->nullable(); // Specific customers only
            $table->json('customer_group_ids')->nullable();
            $table->boolean('first_order_only')->default(false);

            // Limits
            $table->integer('max_uses')->nullable();
            $table->integer('max_uses_per_customer')->default(1);
            $table->integer('current_uses')->default(0);

            // Validity
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index(['is_active', 'starts_at', 'ends_at']);
        });

        // Coupon usage tracking
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->decimal('discount_applied', 12, 2);
            $table->timestamps();

            $table->index(['coupon_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('discount_rules');
    }
};
