<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'reception_dashboard_notifications')) {
                $table->boolean('reception_dashboard_notifications')->default(true)->after('notification_new_patient');
            }
            if (!Schema::hasColumn('settings', 'pharmacy_dashboard_notifications')) {
                $table->boolean('pharmacy_dashboard_notifications')->default(true)->after('reception_dashboard_notifications');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'pharmacy_dashboard_notifications')) {
                $table->dropColumn('pharmacy_dashboard_notifications');
            }
            if (Schema::hasColumn('settings', 'reception_dashboard_notifications')) {
                $table->dropColumn('reception_dashboard_notifications');
            }
        });
    }
};
