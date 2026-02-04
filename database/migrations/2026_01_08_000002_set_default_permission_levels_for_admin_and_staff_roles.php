<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        if (!Schema::hasColumn('roles', 'permission_level')) {
            return;
        }

        // Keep this conservative: only update known role names.
        DB::table('roles')->where('name', 'admin')->update(['permission_level' => 'full_access']);
        DB::table('roles')->where('name', 'staff')->update(['permission_level' => 'limited_access']);
    }

    public function down(): void
    {
        // No-op: reverting would be ambiguous.
    }
};
