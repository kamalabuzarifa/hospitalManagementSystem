<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescription_medicine', function (Blueprint $table) {
            if (!Schema::hasColumn('prescription_medicine', 'quantity')) {
                $table->unsignedInteger('quantity')->default(1)->after('medicine_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prescription_medicine', function (Blueprint $table) {
            if (Schema::hasColumn('prescription_medicine', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });
    }
};
