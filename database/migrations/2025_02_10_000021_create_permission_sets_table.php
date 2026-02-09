<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Permission Sets table
        Schema::create('permission_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot: permission_set_role
        Schema::create('permission_set_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_set_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['permission_set_id', 'role_id']);
        });

        // Pivot: permission_set_user
        Schema::create('permission_set_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_set_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['permission_set_id', 'user_id']);
        });

        // Add color and created_by_id to roles if not exists
        if (!Schema::hasColumn('roles', 'color')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('color', 20)->nullable()->after('permissions');
                $table->foreignId('created_by_id')->nullable()->after('color')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_set_user');
        Schema::dropIfExists('permission_set_role');
        Schema::dropIfExists('permission_sets');

        if (Schema::hasColumn('roles', 'color')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropForeign(['created_by_id']);
                $table->dropColumn(['color', 'created_by_id']);
            });
        }
    }
};
