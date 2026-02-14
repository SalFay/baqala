<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sync versions table - tracks entity versions for delta sync
        Schema::create('sync_versions', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 100); // products, categories, customers, etc.
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('version')->default(1);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id']);
            $table->index(['entity_type', 'version']);
            $table->index(['entity_type', 'updated_at']);
        });

        // Terminal registrations - tracks POS terminals
        Schema::create('terminal_registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('terminal_id')->unique();
            $table->string('name')->nullable();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_info')->nullable();
            $table->string('app_version')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'is_active']);
        });

        // Sync conflicts - logs sync conflicts for review
        Schema::create('sync_conflicts', function (Blueprint $table) {
            $table->id();
            $table->uuid('conflict_id')->unique();
            $table->uuid('terminal_id');
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id');
            $table->json('client_data');
            $table->json('server_data');
            $table->string('resolution')->nullable(); // client_wins, server_wins, merged, manual
            $table->json('resolved_data')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['terminal_id', 'entity_type']);
            $table->index(['resolution', 'created_at']);
        });

        // Offline orders - stores orders created offline
        Schema::create('offline_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('offline_id')->unique();
            $table->uuid('terminal_id');
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->json('order_data');
            $table->string('status')->default('pending'); // pending, synced, failed, conflict
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('created_offline_at');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['terminal_id', 'status']);
            $table->index(['store_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        // Sync logs - audit trail for sync operations
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('terminal_id');
            $table->string('operation'); // push, pull, bootstrap, resolve
            $table->string('status'); // started, completed, failed
            $table->integer('records_sent')->default(0);
            $table->integer('records_received')->default(0);
            $table->integer('conflicts_count')->default(0);
            $table->integer('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['terminal_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('offline_orders');
        Schema::dropIfExists('sync_conflicts');
        Schema::dropIfExists('terminal_registrations');
        Schema::dropIfExists('sync_versions');
    }
};
