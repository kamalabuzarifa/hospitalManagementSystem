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
            $table->dropColumn(['notification_appointments', 'notification_updates']);
            $table->boolean('notification_new_appointment')->default(true);
            $table->boolean('notification_new_patient')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['notification_new_appointment', 'notification_new_patient']);
            $table->boolean('notification_appointments')->default(true);
            $table->boolean('notification_updates')->default(true);
        });
    }
};
