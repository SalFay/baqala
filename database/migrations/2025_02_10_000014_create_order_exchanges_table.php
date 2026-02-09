<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_exchanges', function (Blueprint $table) {
            $table->id();
            $table->string('exchange_number')->unique();
            $table->foreignId('order_return_id')->constrained()->onDelete('cascade');
            $table->foreignId('new_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->decimal('price_difference', 12, 2)->default(0);
            $table->string('difference_action')->nullable(); // collect, refund
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_exchanges');
    }
};
