<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find duplicate prescription_id values and nullify all but the first occurrence
        $duplicates = DB::table('medicines')
            ->select('prescription_id', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('prescription_id')
            ->groupBy('prescription_id')
            ->having('cnt', '>', 1)
            ->pluck('prescription_id');

        foreach ($duplicates as $code) {
            $rows = DB::table('medicines')
                ->where('prescription_id', $code)
                ->orderBy('id')
                ->pluck('id')
                ->toArray();

            // keep first, nullify the rest
            array_shift($rows);
            if (!empty($rows)) {
                DB::table('medicines')->whereIn('id', $rows)->update(['prescription_id' => null]);
            }
        }

        // Add unique index (nullable column allows multiple NULLs)
        Schema::table('medicines', function (Blueprint $table) {
            $table->unique('prescription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropUnique(['prescription_id']);
        });
    }
};
