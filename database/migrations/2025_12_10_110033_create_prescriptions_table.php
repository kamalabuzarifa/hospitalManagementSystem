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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->date('p_date')->nullable();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->string('medicine')->nullable();
            $table->string('dosage')->nullable();
            $table->string('duration')->nullable();
            $table->longText('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('prescriptions');
        Schema::enableForeignKeyConstraints();
    }
};
