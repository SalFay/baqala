<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->integer('min_points')->default(0);
            $table->decimal('points_multiplier', 3, 2)->default(1.00);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->json('benefits')->nullable();
            $table->string('badge_color')->nullable();
            $table->string('badge_icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('customer_loyalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('loyalty_tier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('card_number')->nullable()->unique();
            $table->integer('points_balance')->default(0);
            $table->integer('points_earned_total')->default(0);
            $table->integer('points_redeemed_total')->default(0);
            $table->integer('points_expired_total')->default(0);
            $table->decimal('lifetime_spend', 12, 2)->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('tier_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['customer_id', 'is_active']);
        });

        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_loyalty_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // earn, redeem, expire, adjust, bonus, refund
            $table->integer('points');
            $table->integer('points_balance_after');
            $table->string('reference_type')->nullable(); // Order, OrderReturn, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['customer_loyalty_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('customer_loyalties');
        Schema::dropIfExists('loyalty_tiers');
    }
};
