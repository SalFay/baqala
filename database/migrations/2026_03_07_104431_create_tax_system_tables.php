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
        // Extend tax_rates table with new columns
        Schema::table('tax_rates', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->boolean('is_compound')->default(false)->after('is_default');
            $table->boolean('is_recoverable')->default(true)->after('is_compound');
            $table->string('tax_number')->nullable()->after('is_recoverable');
            $table->softDeletes();
        });

        // Create tax_groups table
        Schema::create('tax_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Create pivot table for tax_group_rates
        Schema::create('tax_group_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_rate_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tax_group_id', 'tax_rate_id']);
        });

        // Create product_tax_settings table
        Schema::create('product_tax_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_rate_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tax_group_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_tax_exempt')->default(false);
            $table->timestamps();

            $table->unique('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_tax_settings');
        Schema::dropIfExists('tax_group_rates');
        Schema::dropIfExists('tax_groups');

        Schema::table('tax_rates', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['store_id', 'is_compound', 'is_recoverable', 'tax_number']);
        });
    }
};
