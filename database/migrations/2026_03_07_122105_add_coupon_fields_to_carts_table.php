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
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->string('coupon_code', 50)->nullable()->after('coupon_id');
            $table->decimal('coupon_discount', 10, 2)->default(0)->after('discount_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_id', 'coupon_code', 'coupon_discount']);
        });
    }
};
