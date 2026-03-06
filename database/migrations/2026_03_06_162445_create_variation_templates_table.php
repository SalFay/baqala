<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variation_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('attributes')->nullable(); // [{name: "Size", values: ["S","M","L","XL"]}, {name: "Color", values: ["Red","Blue"]}]
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add variation_template_id and attributes JSON to product_variants
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedBigInteger('variation_template_id')->nullable()->after('product_id');
            $table->json('attributes')->nullable()->after('name'); // JSON storage for flexible attributes

            $table->foreign('variation_template_id')->references('id')->on('variation_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropForeign(['variation_template_id']);
            $table->dropColumn(['variation_template_id', 'attributes']);
        });

        Schema::dropIfExists('variation_templates');
    }
};
