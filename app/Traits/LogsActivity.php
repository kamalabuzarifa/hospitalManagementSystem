<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait LogsActivity
{
    /**
     * Log an activity to the activity_logs table
     *
     * @param string $action The action code (e.g., 'administered_medication')
     * @param array $meta Optional metadata
     * @return void
     */
    protected function logActivity(string $action, array $meta = []): void
    {
        $userId = auth()->id();
        if (!$userId) {
            return;
        }

        DB::table('activity_logs')->insert([
            'user_id' => $userId,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'meta' => !empty($meta) ? json_encode($meta) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
