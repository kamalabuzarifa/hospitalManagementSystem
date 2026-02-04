<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Modify the status column to add 'pending' to the enum
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['scheduled', 'checked_in', 'cancelled', 'completed', 'pending'])
                ->default('scheduled')
                ->change();
        });
    }

    public function down()
    {
        // Revert to old enum values
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['scheduled', 'checked_in', 'cancelled', 'completed'])
                ->default('scheduled')
                ->change();
        });
    }
};
