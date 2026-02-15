<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Single credits table with polymorphic relationship
        if (!Schema::hasTable('credits')) {
            Schema::create('credits', function (Blueprint $table) {
                $table->id();
                $table->morphs('creditable'); // creditable_type, creditable_id
                $table->decimal('amount', 12, 2);
                $table->enum('type', ['credit', 'debit']);
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->decimal('balance_after', 12, 2);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['creditable_type', 'creditable_id', 'created_at'], 'credits_creditable_date_index');
            });
        }

        // Drop old tables if they exist
        Schema::dropIfExists('customer_credits');
        Schema::dropIfExists('vendor_credits');
    }

    public function down(): void
    {
        Schema::dropIfExists('credits');
    }
};
