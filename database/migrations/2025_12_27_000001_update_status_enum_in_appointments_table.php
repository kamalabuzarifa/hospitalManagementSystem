<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['scheduled', 'cancelled', 'completed', 'pending','checked_in','checked_out'])->default('scheduled')->change();
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // ضع هنا القيم القديمة إذا كانت مختلفة
            $table->enum('status', ['scheduled', 'cancelled', 'completed', 'pending'])->default('scheduled')->change();
        });
    }
};
