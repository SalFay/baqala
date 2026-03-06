<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Units of measure (e.g., piece, kg, liter, box)
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // e.g., "Kilogram", "Piece", "Box"
            $table->string('short_name'); // e.g., "kg", "pc", "box"
            $table->boolean('is_base_unit')->default(true); // If true, this is a base unit
            $table->unsignedBigInteger('base_unit_id')->nullable(); // Reference to base unit for conversion
            $table->decimal('conversion_rate', 12, 4)->default(1); // How many base units = 1 of this unit
            $table->boolean('allow_decimal')->default(true); // Allow decimal quantities
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('base_unit_id')->references('id')->on('units')->nullOnDelete();
        });

        // Product-Unit relationship with specific pricing
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->boolean('is_purchase_unit')->default(false); // Can be used for purchasing
            $table->boolean('is_sale_unit')->default(true); // Can be used for selling
            $table->boolean('is_default')->default(false); // Default unit for this product
            $table->decimal('multiplier', 12, 4)->default(1); // e.g., 1 box = 12 pieces
            $table->decimal('price_per_unit', 12, 2)->nullable(); // Specific price when sold in this unit
            $table->timestamps();

            $table->unique(['product_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
        Schema::dropIfExists('units');
    }
};
