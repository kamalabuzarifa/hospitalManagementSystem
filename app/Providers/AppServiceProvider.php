<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Staff;
use App\Observers\AppointmentObserver;
use App\Observers\PatientObserver;
use App\Observers\PrescriptionObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Patient::observe(PatientObserver::class);
        Appointment::observe(AppointmentObserver::class);
        Prescription::observe(PrescriptionObserver::class);

        Blade::if('permission', function (string $permissionName): bool {
            $user = auth()->user();
            if (!$user || !$user->role) {
                return false;
            }

            if (($user->role->permission_level ?? null) === 'full_access') {
                return true;
            }

            return $user->role->hasPermission($permissionName);
        });

        Blade::if('anyPermission', function (...$permissionNames): bool {
            $user = auth()->user();
            if (!$user || !$user->role) {
                return false;
            }

            if (($user->role->permission_level ?? null) === 'full_access') {
                return true;
            }

            foreach ($permissionNames as $permissionName) {
                $permissionName = trim((string) $permissionName);
                if ($permissionName !== '' && $user->role->hasPermission($permissionName)) {
                    return true;
                }
            }

            return false;
        });

        // Sync staff active status with user login/logout
        Event::listen(Login::class, function ($event) {
            $userId = $event->user->id ?? null;
            if (!$userId) {
                return;
            }
            Staff::where('user_id', $userId)->update(['status' => true]);

            DB::table('activity_logs')->insert([
                'user_id' => $userId,
                'action' => 'login',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Event::listen(Logout::class, function ($event) {
            $userId = $event->user->id ?? null;
            if (!$userId) {
                return;
            }
            Staff::where('user_id', $userId)->update(['status' => false]);

            DB::table('activity_logs')->insert([
                'user_id' => $userId,
                'action' => 'logout',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}
