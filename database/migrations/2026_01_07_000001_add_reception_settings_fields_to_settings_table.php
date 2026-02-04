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
            $table->boolean('allow_edit_appointments')->default(true);
            $table->unsignedSmallInteger('default_appointment_duration')->default(20);

            $table->boolean('auto_assign_doctor')->default(false);
            $table->boolean('show_short_medical_record')->default(true);

            $table->boolean('dark_mode')->default(false);

            $table->string('clinic_phone')->nullable();
            $table->string('clinic_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'allow_edit_appointments',
                'default_appointment_duration',
                'auto_assign_doctor',
                'show_short_medical_record',
                'dark_mode',
                'clinic_phone',
                'clinic_address',
            ]);
        });
    }
};
