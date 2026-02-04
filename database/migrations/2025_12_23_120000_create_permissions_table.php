<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create pivot table for role_permission
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->timestamps();
        });

        // تم حذف سطر إزالة عمود permission_level ليبقى العمود موجوداً في جدول roles
    }

    public function down()
    {
        // Add permission_level column back to roles table only if not exists
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'permission_level')) {
                $table->string('permission_level')->default('read_only');
            }
        });
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
    }
};
