<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('prescriptions', 'dispensed_at')) {
                $table->timestamp('dispensed_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('prescriptions', 'dispensed_by_user_id')) {
                $table->foreignId('dispensed_by_user_id')->nullable()->after('dispensed_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            if (Schema::hasColumn('prescriptions', 'dispensed_by_user_id')) {
                $table->dropForeign(['dispensed_by_user_id']);
                $table->dropColumn('dispensed_by_user_id');
            }
            if (Schema::hasColumn('prescriptions', 'dispensed_at')) {
                $table->dropColumn('dispensed_at');
            }
        });
    }
};
