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
        // Restaurant Tables
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // e.g., "Table 1", "T-01"
            $table->integer('capacity')->default(4);
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
            $table->foreignId('current_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('section')->nullable(); // e.g., "Indoor", "Outdoor", "VIP"
            $table->string('floor')->nullable(); // e.g., "Ground Floor", "1st Floor"
            $table->integer('position_x')->nullable(); // For floor plan positioning
            $table->integer('position_y')->nullable();
            $table->string('shape')->default('square'); // square, rectangle, circle
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Table Reservations
        Schema::create('table_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('table_id')->constrained('restaurant_tables')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->integer('party_size')->default(1);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');
            $table->text('special_requests')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reservation_date', 'start_time']);
        });

        // Kitchen Orders (KDS - Kitchen Display System)
        Schema::create('kitchen_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->cascadeOnDelete();
            $table->enum('status', ['pending', 'preparing', 'ready', 'served', 'cancelled'])->default('pending');
            $table->string('station')->nullable(); // e.g., "Grill", "Salad", "Drinks"
            $table->enum('priority', ['normal', 'rush', 'vip'])->default('normal');
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('estimated_time')->nullable(); // in minutes
            $table->timestamps();

            $table->index(['status', 'station']);
            $table->index(['order_id', 'status']);
        });

        // Kitchen Stations (optional, for organizing KDS)
        Schema::create('kitchen_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_stations');
        Schema::dropIfExists('kitchen_orders');
        Schema::dropIfExists('table_reservations');
        Schema::dropIfExists('restaurant_tables');
    }
};
