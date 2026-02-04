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
        Schema::table('patients', function (Blueprint $table) {
            $table->string('patient_code')->nullable()->unique()->after('user_id');
            $table->boolean('has_allergies')->default(false)->after('address');
            $table->text('allergy_details')->nullable()->after('has_allergies');
            $table->boolean('has_chronic')->default(false)->after('allergy_details');
            $table->text('chronic_details')->nullable()->after('has_chronic');
            $table->date('last_visit')->nullable()->after('chronic_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique(['patient_code']);
            $table->dropColumn([
                'patient_code',
                'has_allergies',
                'allergy_details',
                'has_chronic',
                'chronic_details',
                'last_visit',
            ]);
        });
    }
};
