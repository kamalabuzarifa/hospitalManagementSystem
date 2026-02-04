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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('language')->default('en');
            $table->string('time_format')->default('24'); // 12 or 24
            $table->string('date_format')->default('DD/MM/YYYY');
            $table->string('backup_frequency')->default('weekly'); // daily, weekly, monthly
            $table->string('backup_destination')->nullable();
            $table->boolean('notification_billing')->default(true);
            $table->boolean('notification_appointments')->default(true);
            $table->boolean('notification_updates')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
