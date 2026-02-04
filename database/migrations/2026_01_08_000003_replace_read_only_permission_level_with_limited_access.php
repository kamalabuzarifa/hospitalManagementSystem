<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasColumn('roles', 'permission_level')) {
            return;
        }

        DB::table('roles')
            ->where('permission_level', 'read_only')
            ->update(['permission_level' => 'limited_access']);
    }

    public function down(): void
    {
        // No-op: reverting would reintroduce deprecated permission level.
    }
};
