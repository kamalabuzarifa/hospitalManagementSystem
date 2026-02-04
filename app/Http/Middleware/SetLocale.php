<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Settings;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = null;

        try {
            if (Schema::hasTable('settings')) {
                $settings = Settings::first();
            }
        } catch (QueryException $e) {
            $settings = null;
        }
        view()->share('appSettings', $settings);

        // Prioritize session locale if present
        $sessionLocale = $request->session()->get('locale');
        if ($sessionLocale) {
            app()->setLocale($sessionLocale);
        } else {
            // If user is authenticated, prefer role-specific language from settings
            try {
                if (Auth::check() && $settings) {
                    $role = Auth::user()->role?->name;
                    $roleMap = [
                        'admin' => 'language_admin',
                        'doctor' => 'language_doctor',
                        'receptionist' => 'language_receptionist',
                        'pharmacy' => 'language_pharmacy',
                        'patient' => 'language_patient',
                        'staff' => 'language_staff',
                    ];
                    if ($role && isset($roleMap[$role]) && $settings->{$roleMap[$role]}) {
                        app()->setLocale($settings->{$roleMap[$role]});
                    } elseif ($settings && $settings->language) {
                        app()->setLocale($settings->language);
                    }
                } elseif ($settings && $settings->language) {
                    app()->setLocale($settings->language);
                }
            } catch (\Exception $e) {
                // fallback to config locale on any error
            }
        }

        return $next($request);
    }
}
