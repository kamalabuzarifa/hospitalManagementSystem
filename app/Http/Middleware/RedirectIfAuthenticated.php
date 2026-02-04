<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (auth()->guard($guard)->check()) {
                $user = auth()->guard($guard)->user();
                $role = $user->role?->name;

                return match($role) {
                    'admin' => redirect('/admin/dashboard'),
                    'doctor' => redirect('/doctor/dashboard'),
                    'patient' => redirect('/patient/dashboard'),
                    'pharmacy' => redirect('/pharmacy/dashboard'),
                    'receptionist' => redirect('/reception/patients'),
                    default => redirect('/dashboard'),
                };
            }
        }

        return $next($request);
    }
}
