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
        Schema::table('payment_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_methods', 'store_id')) {
                $table->foreignId('store_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('payment_methods', 'description')) {
                $table->string('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('payment_methods', 'requires_reference')) {
                $table->boolean('requires_reference')->default(false)->after('code');
            }
            if (!Schema::hasColumn('payment_methods', 'reference_label')) {
                $table->string('reference_label')->nullable()->after('requires_reference');
            }
            if (!Schema::hasColumn('payment_methods', 'settings')) {
                $table->json('settings')->nullable()->after('reference_label');
            }
            if (!Schema::hasColumn('payment_methods', 'icon')) {
                $table->string('icon')->nullable()->after('settings');
            }
            if (!Schema::hasColumn('payment_methods', 'is_system')) {
                $table->boolean('is_system')->default(false)->after('icon');
            }
            if (!Schema::hasColumn('payment_methods', 'allow_partial')) {
                $table->boolean('allow_partial')->default(true)->after('is_system');
            }
            if (!Schema::hasColumn('payment_methods', 'min_amount')) {
                $table->decimal('min_amount', 15, 2)->nullable()->after('allow_partial');
            }
            if (!Schema::hasColumn('payment_methods', 'max_amount')) {
                $table->decimal('max_amount', 15, 2)->nullable()->after('min_amount');
            }
        });

        // Customer Ledger for credit sales
        if (!Schema::hasTable('customer_ledger')) {
            Schema::create('customer_ledger', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->string('transaction_type'); // sale, payment, refund, adjustment, opening
                $table->string('reference_type')->nullable(); // Order, Payment, etc.
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->decimal('balance_after', 15, 2)->default(0);
                $table->string('description')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['customer_id', 'created_at']);
                $table->index('reference_type');
            });
        }

        // Cheque management
        if (!Schema::hasTable('cheques')) {
            Schema::create('cheques', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->cascadeOnDelete();
                $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->string('cheque_number');
                $table->string('bank_name');
                $table->string('bank_branch')->nullable();
                $table->string('account_number')->nullable();
                $table->decimal('amount', 15, 2);
                $table->date('cheque_date');
                $table->date('due_date')->nullable();
                $table->string('status')->default('pending'); // pending, deposited, cleared, bounced, cancelled
                $table->datetime('deposited_at')->nullable();
                $table->datetime('cleared_at')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'due_date']);
            });
        }

        // Cash Registers
        if (!Schema::hasTable('cash_registers')) {
            Schema::create('cash_registers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('location_id')->nullable(); // FK added in Phase 6
                $table->string('name');
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status')->default('closed'); // closed, open
                $table->decimal('opening_balance', 15, 2)->default(0);
                $table->decimal('closing_balance', 15, 2)->nullable();
                $table->decimal('opening_cash', 15, 2)->default(0);
                $table->decimal('closing_cash', 15, 2)->nullable();
                $table->decimal('expected_cash', 15, 2)->nullable();
                $table->decimal('cash_difference', 15, 2)->nullable();
                $table->datetime('opened_at')->nullable();
                $table->datetime('closed_at')->nullable();
                $table->text('opening_note')->nullable();
                $table->text('closing_note')->nullable();
                $table->json('denominations_opening')->nullable();
                $table->json('denominations_closing')->nullable();
                $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['store_id', 'status']);
            });
        }

        // Cash Register Transactions
        if (!Schema::hasTable('cash_register_transactions')) {
            Schema::create('cash_register_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cash_register_id')->constrained()->cascadeOnDelete();
                $table->string('transaction_type'); // sale, refund, pay_in, pay_out, adjustment
                $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('amount', 15, 2);
                $table->string('reference_type')->nullable(); // Order, Payment, etc.
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('note')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['cash_register_id', 'transaction_type'], 'cr_trans_register_type_idx');
            });
        }

        // Add credit fields to customers
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'credit_limit')) {
                $table->decimal('credit_limit', 15, 2)->nullable()->after('customer_group_id');
            }
            if (!Schema::hasColumn('customers', 'current_balance')) {
                $table->decimal('current_balance', 15, 2)->default(0)->after('credit_limit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'credit_limit')) {
                $table->dropColumn('credit_limit');
            }
            if (Schema::hasColumn('customers', 'current_balance')) {
                $table->dropColumn('current_balance');
            }
        });

        Schema::dropIfExists('cash_register_transactions');
        Schema::dropIfExists('cash_registers');
        Schema::dropIfExists('cheques');
        Schema::dropIfExists('customer_ledger');

        Schema::table('payment_methods', function (Blueprint $table) {
            $columnsToCheck = [
                'description', 'requires_reference', 'reference_label',
                'settings', 'icon', 'is_system', 'allow_partial', 'min_amount', 'max_amount'
            ];

            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('payment_methods', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
