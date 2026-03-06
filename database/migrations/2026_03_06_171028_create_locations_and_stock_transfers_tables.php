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
        // Locations
        if (!Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('code')->nullable();
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->boolean('is_main')->default(false);
                $table->boolean('is_active')->default(true);
                $table->json('settings')->nullable();
                $table->foreignId('selling_price_group_id')->nullable()->constrained()->nullOnDelete();
                $table->string('invoice_prefix')->nullable();
                $table->unsignedInteger('invoice_counter')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['store_id', 'code']);
            });
        }

        // Add location fields to existing stock_transfers table
        if (Schema::hasTable('stock_transfers')) {
            Schema::table('stock_transfers', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_transfers', 'store_id')) {
                    $table->foreignId('store_id')->nullable()->after('id')->constrained()->nullOnDelete();
                }
                if (!Schema::hasColumn('stock_transfers', 'from_location_id')) {
                    $table->unsignedBigInteger('from_location_id')->nullable()->after('to_store_id');
                }
                if (!Schema::hasColumn('stock_transfers', 'to_location_id')) {
                    $table->unsignedBigInteger('to_location_id')->nullable()->after('from_location_id');
                }
                if (!Schema::hasColumn('stock_transfers', 'transfer_date')) {
                    $table->date('transfer_date')->nullable()->after('status');
                }
                if (!Schema::hasColumn('stock_transfers', 'expected_date')) {
                    $table->date('expected_date')->nullable()->after('transfer_date');
                }
                if (!Schema::hasColumn('stock_transfers', 'received_date')) {
                    $table->date('received_date')->nullable()->after('expected_date');
                }
                if (!Schema::hasColumn('stock_transfers', 'total_items')) {
                    $table->unsignedInteger('total_items')->default(0)->after('received_date');
                }
                if (!Schema::hasColumn('stock_transfers', 'total_quantity')) {
                    $table->decimal('total_quantity', 15, 2)->default(0)->after('total_items');
                }
                if (!Schema::hasColumn('stock_transfers', 'total_value')) {
                    $table->decimal('total_value', 15, 2)->default(0)->after('total_quantity');
                }
                if (!Schema::hasColumn('stock_transfers', 'shipping_details')) {
                    $table->text('shipping_details')->nullable()->after('notes');
                }
            });
        }

        // Add location fields to existing stock_transfer_items table
        if (Schema::hasTable('stock_transfer_items')) {
            Schema::table('stock_transfer_items', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_transfer_items', 'batch_id')) {
                    $table->unsignedBigInteger('batch_id')->nullable()->after('product_variant_id');
                }
                if (!Schema::hasColumn('stock_transfer_items', 'serial_id')) {
                    $table->unsignedBigInteger('serial_id')->nullable()->after('batch_id');
                }
                if (!Schema::hasColumn('stock_transfer_items', 'quantity_sent')) {
                    $table->decimal('quantity_sent', 15, 2)->nullable()->after('serial_id');
                }
                if (!Schema::hasColumn('stock_transfer_items', 'unit_cost')) {
                    $table->decimal('unit_cost', 15, 2)->default(0)->after('quantity_received');
                }
            });
        }

        // Add foreign key for location_id in cash_registers
        if (Schema::hasColumn('cash_registers', 'location_id')) {
            try {
                Schema::table('cash_registers', function (Blueprint $table) {
                    $table->foreign('location_id')->references('id')->on('locations')->nullOnDelete();
                });
            } catch (\Exception $e) {
                // Foreign key may already exist
            }
        }

        // Add location_id to orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable()->after('store_id');
            }
        });

        // Add location_id to products_stock (inventory per location)
        if (!Schema::hasTable('product_location_stock')) {
            Schema::create('product_location_stock', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('location_id')->constrained()->cascadeOnDelete();
                $table->decimal('quantity', 15, 2)->default(0);
                $table->decimal('reserved_quantity', 15, 2)->default(0);
                $table->timestamps();

                $table->unique(['product_id', 'product_variant_id', 'location_id'], 'product_location_stock_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'location_id')) {
                $table->dropColumn('location_id');
            }
        });

        if (Schema::hasColumn('cash_registers', 'location_id')) {
            try {
                Schema::table('cash_registers', function (Blueprint $table) {
                    $table->dropForeign(['location_id']);
                });
            } catch (\Exception $e) {
                // Foreign key may not exist
            }
        }

        Schema::dropIfExists('product_location_stock');
        Schema::dropIfExists('locations');
    }
};
