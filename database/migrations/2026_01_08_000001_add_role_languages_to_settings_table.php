<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'language_admin')) {
                $table->string('language_admin')->nullable()->after('language');
            }
            if (!Schema::hasColumn('settings', 'language_doctor')) {
                $table->string('language_doctor')->nullable()->after('language_admin');
            }
            if (!Schema::hasColumn('settings', 'language_receptionist')) {
                $table->string('language_receptionist')->nullable()->after('language_doctor');
            }
            if (!Schema::hasColumn('settings', 'language_pharmacy')) {
                $table->string('language_pharmacy')->nullable()->after('language_receptionist');
            }
            if (!Schema::hasColumn('settings', 'language_patient')) {
                $table->string('language_patient')->nullable()->after('language_pharmacy');
            }
            if (!Schema::hasColumn('settings', 'language_staff')) {
                $table->string('language_staff')->nullable()->after('language_patient');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach ([
                'language_admin',
                'language_doctor',
                'language_receptionist',
                'language_pharmacy',
                'language_patient',
                'language_staff',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
