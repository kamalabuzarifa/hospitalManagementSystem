<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Set existing staff to inactive by default
        DB::table('staff')->update(['status' => false]);

        // Change default to false for future inserts
        Schema::table('staff', function (Blueprint $table) {
            $table->boolean('status')->default(false)->change();
        });
    }

    public function down(): void
    {
        // Revert default to true (previous behavior)
        Schema::table('staff', function (Blueprint $table) {
            $table->boolean('status')->default(true)->change();
        });
    }
};
