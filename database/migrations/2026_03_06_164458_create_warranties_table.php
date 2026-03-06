<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Warranty templates/types
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // e.g., "1 Year Manufacturer Warranty"
            $table->text('description')->nullable();
            $table->integer('duration'); // Duration value
            $table->enum('duration_type', ['days', 'months', 'years'])->default('months');
            $table->text('terms')->nullable(); // Warranty terms and conditions
            $table->text('coverage')->nullable(); // What's covered
            $table->text('exclusions')->nullable(); // What's not covered
            $table->boolean('is_transferable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Warranty claims
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('claim_number')->unique();

            // What is being claimed
            $table->foreignId('warranty_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_serial_id')->nullable()->constrained('product_serials')->nullOnDelete();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('order_item_id')->nullable();

            // Customer
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();

            // Claim details
            $table->date('claim_date');
            $table->text('issue_description');
            $table->json('symptoms')->nullable(); // Array of symptoms/issues

            // Diagnosis
            $table->text('diagnosis')->nullable();
            $table->enum('fault_type', ['manufacturing', 'user_damage', 'wear_and_tear', 'unknown', 'other'])->nullable();

            // Resolution
            $table->enum('resolution_type', ['repair', 'replace', 'refund', 'rejected', 'pending'])->nullable();
            $table->text('resolution_notes')->nullable();
            $table->decimal('repair_cost', 12, 2)->nullable();
            $table->unsignedBigInteger('replacement_serial_id')->nullable();

            // Status tracking
            $table->enum('status', ['pending', 'in_review', 'approved', 'in_progress', 'completed', 'rejected', 'cancelled'])
                ->default('pending');

            // Dates
            $table->timestamp('received_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            // Internal tracking
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->integer('priority')->default(0); // 0=normal, 1=high, 2=urgent

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'claim_date']);
            $table->index('customer_id');
        });

        // Link products to warranty templates
        Schema::create('product_warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warranty_id')->constrained()->onDelete('cascade');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'warranty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_warranties');
        Schema::dropIfExists('warranty_claims');
        Schema::dropIfExists('warranties');
    }
};
