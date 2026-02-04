<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'doctor_profile_id')) {
                $table->foreignId('doctor_profile_id')
                    ->nullable()
                    ->after('doctor_id')
                    ->constrained('doctors')
                    ->nullOnDelete();

                $table->index('doctor_profile_id');
            }

            if (!Schema::hasColumn('appointments', 'scheduled_at')) {
                $table->dateTime('scheduled_at')->nullable()->after('time');
                $table->index('scheduled_at');
            }
        });

        // Backfill scheduled_at from date + time
        if (Schema::hasColumn('appointments', 'scheduled_at') && Schema::hasColumn('appointments', 'date') && Schema::hasColumn('appointments', 'time')) {
            DB::table('appointments')
                ->whereNull('scheduled_at')
                ->whereNotNull('date')
                ->whereNotNull('time')
                ->orderBy('id')
                ->chunkById(200, function ($appointments): void {
                    foreach ($appointments as $appointment) {
                        $scheduledAt = null;

                        try {
                            $scheduledAt = Carbon::parse($appointment->date . ' ' . $appointment->time);
                        } catch (\Throwable $e) {
                            // Skip invalid legacy date/time rows
                            continue;
                        }

                        DB::table('appointments')
                            ->where('id', $appointment->id)
                            ->update(['scheduled_at' => $scheduledAt]);
                    }
                });
        }

        // Backfill doctor_profile_id from legacy doctor_id (users.id) via doctors.user_id
        if (Schema::hasColumn('appointments', 'doctor_profile_id') && Schema::hasColumn('appointments', 'doctor_id')) {
            $doctorIdByUserId = DB::table('doctors')
                ->whereNotNull('user_id')
                ->pluck('id', 'user_id');

            DB::table('appointments')
                ->whereNull('doctor_profile_id')
                ->whereNotNull('doctor_id')
                ->orderBy('id')
                ->chunkById(200, function ($appointments) use ($doctorIdByUserId): void {
                    foreach ($appointments as $appointment) {
                        $mappedDoctorProfileId = $doctorIdByUserId[$appointment->doctor_id] ?? null;
                        if (!$mappedDoctorProfileId) {
                            continue;
                        }

                        DB::table('appointments')
                            ->where('id', $appointment->id)
                            ->update(['doctor_profile_id' => $mappedDoctorProfileId]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'doctor_profile_id')) {
                $table->dropConstrainedForeignId('doctor_profile_id');
            }

            if (Schema::hasColumn('appointments', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
        });
    }
};
