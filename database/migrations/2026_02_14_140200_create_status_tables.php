<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Statuses table
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('category_type'); // 'Order', 'PurchaseOrder', 'StockTransfer'
            $table->string('code');          // 'pending', 'completed', etc.
            $table->string('name');
            $table->string('color')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category_type', 'code']);
        });

        // Status histories table
        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->foreignId('status_id')->constrained()->cascadeOnDelete();
            $table->foreignId('previous_status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_system_change')->default(false);
            $table->timestamps();

            $table->index(['model_type', 'model_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_histories');
        Schema::dropIfExists('statuses');
    }
};
