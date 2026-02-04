<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Staff;
use App\Models\Doctor;

class BackfillActivityLogs extends Command
{
    protected $signature = 'activity:backfill';
    protected $description = 'Backfill activity logs for existing staff and doctors';

    public function handle()
    {
        $this->info('Starting backfill of activity logs...');

        // Get admin user (or first user)
        $adminUser = DB::table('users')->first();
        if (!$adminUser) {
            $this->error('❌ No users found in database!');
            return;
        }

        // Get all staff members
        $staffMembers = Staff::with('user')->get();
        $doctorUsers = Doctor::with('user')->get();

        $count = 0;

        // Log created_staff for each staff member
        foreach ($staffMembers as $staff) {
            DB::table('activity_logs')->insert([
                'user_id' => $adminUser->id,
                'action' => 'created_staff',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Backfill Script',
                'meta' => json_encode([
                    'staff_name' => $staff->user->name,
                    'department' => $staff->department,
                ]),
                'created_at' => $staff->created_at,
                'updated_at' => $staff->created_at,
            ]);
            $count++;
            $this->line("✓ Logged: {$staff->user->name} (Staff)");
        }

        // Log created_doctor for each doctor
        foreach ($doctorUsers as $doctor) {
            DB::table('activity_logs')->insert([
                'user_id' => $adminUser->id,
                'action' => 'created_doctor',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Backfill Script',
                'meta' => json_encode([
                    'doctor_id' => $doctor->id,
                    'user_id' => $doctor->user_id,
                ]),
                'created_at' => $doctor->created_at,
                'updated_at' => $doctor->created_at,
            ]);
            $count++;
            $this->line("✓ Logged: {$doctor->user->name} (Doctor)");
        }

        $this->info("\n✅ Successfully backfilled {$count} activity logs!");
    }
}
