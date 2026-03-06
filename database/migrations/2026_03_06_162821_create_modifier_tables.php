<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modifier sets (groups of modifiers like "Toppings", "Sauce", "Size")
        Schema::create('modifier_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // e.g., "Extra Toppings", "Sauce Selection"
            $table->text('description')->nullable();
            $table->string('selection_type')->default('multiple'); // single, multiple
            $table->boolean('is_required')->default(false);
            $table->integer('min_selections')->default(0);
            $table->integer('max_selections')->nullable(); // null = unlimited
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Individual modifiers within a set
        Schema::create('modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modifier_set_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Extra Cheese", "Mushrooms"
            $table->decimal('price_adjustment', 12, 2)->default(0); // Additional cost
            $table->string('price_type')->default('fixed'); // fixed, percentage
            $table->boolean('is_default')->default(false); // Pre-selected
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot: Which products use which modifier sets
        Schema::create('product_modifier_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('modifier_set_id')->constrained()->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'modifier_set_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_modifier_sets');
        Schema::dropIfExists('modifiers');
        Schema::dropIfExists('modifier_sets');
    }
};
