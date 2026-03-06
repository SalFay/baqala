<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedBigInteger('selling_price_group_id')->nullable()->after('customer_id');
            $table->foreign('selling_price_group_id')->references('id')->on('selling_price_groups')->nullOnDelete();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->decimal('original_price', 12, 2)->nullable()->after('unit_price');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['selling_price_group_id']);
            $table->dropColumn('selling_price_group_id');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'discount_amount']);
        });
    }
};
