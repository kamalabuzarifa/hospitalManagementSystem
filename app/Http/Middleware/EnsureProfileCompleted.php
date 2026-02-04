<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProfileCompleted
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && !$user->completed_registration) {
            // Allow access only to the complete-registration route
            if (!$request->routeIs('complete-registration') && !$request->routeIs('complete-registration.submit')) {
                return redirect()->route('complete-registration');
            }
        }
        return $next($request);
    }
}
