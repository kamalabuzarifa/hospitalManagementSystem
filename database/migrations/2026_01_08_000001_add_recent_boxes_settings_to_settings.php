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
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'dashboard_recent_appointments')) {
                $table->boolean('dashboard_recent_appointments')->default(true);
            }
            if (!Schema::hasColumn('settings', 'dashboard_recent_patient')) {
                $table->boolean('dashboard_recent_patient')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'dashboard_recent_appointments')) {
                $table->dropColumn('dashboard_recent_appointments');
            }
            if (Schema::hasColumn('settings', 'dashboard_recent_patient')) {
                $table->dropColumn('dashboard_recent_patient');
            }
        });
    }
};
