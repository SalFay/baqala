<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Custom field definitions
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entity_type'); // 'product', 'customer', 'order', etc.
            $table->string('name');
            $table->string('label');
            $table->string('field_type'); // text, number, select, date, boolean, textarea
            $table->json('options')->nullable(); // For select type: [{value: '', label: ''}]
            $table->string('default_value')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->boolean('show_in_list')->default(false);
            $table->boolean('show_in_pos')->default(false);
            $table->text('validation_rules')->nullable(); // Laravel validation rules
            $table->text('help_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['store_id', 'entity_type', 'name']);
            $table->index(['entity_type', 'is_active']);
        });

        // Custom field values (EAV pattern)
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained()->onDelete('cascade');
            $table->morphs('entity'); // entity_type + entity_id
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['custom_field_id', 'entity_type', 'entity_id']);
        });

        // Add custom_fields JSON column to products for quick access
        Schema::table('products', function (Blueprint $table) {
            $table->json('custom_fields')->nullable()->after('meta');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });

        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
    }
};
