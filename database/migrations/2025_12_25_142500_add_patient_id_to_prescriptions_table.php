<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('prescriptions', 'patient_id')) {
                $table->unsignedBigInteger('patient_id')->after('doctor_id')->nullable();
                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            if (Schema::hasColumn('prescriptions', 'patient_id')) {
                $table->dropForeign(['patient_id']);
                $table->dropColumn('patient_id');
            }
        });
    }
};
