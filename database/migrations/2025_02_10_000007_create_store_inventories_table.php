<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('low_stock_threshold')->nullable();
            $table->string('location')->nullable(); // Bin location, shelf, etc.
            $table->timestamp('last_counted_at')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'product_id', 'product_variant_id'], 'store_inventory_unique');
            $table->index(['store_id', 'quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_inventories');
    }
};
