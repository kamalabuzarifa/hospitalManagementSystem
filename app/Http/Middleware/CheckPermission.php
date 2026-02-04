<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Check if the authenticated user's role has the required permission(s).
     *
     * Usage: ->middleware('permission:manage_patients')
     * Multiple can be passed as comma-separated OR multiple middleware args.
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $user = $request->user();

        if (!$user) {
            abort(403, __('messages.unauthorized'));
        }

        $role = $user->role;
        if (!$role) {
            abort(403, __('messages.no_role_assigned'));
        }

        if (($role->permission_level ?? null) === 'full_access') {
            return $next($request);
        }

        $required = [];
        foreach ($permissions as $permissionArg) {
            foreach (explode(',', (string) $permissionArg) as $permissionName) {
                $permissionName = trim($permissionName);
                if ($permissionName !== '') {
                    $required[] = $permissionName;
                }
            }
        }

        if ($required === []) {
            abort(403, __('messages.no_permission_specified'));
        }

        foreach ($required as $permissionName) {
            if ($role->hasPermission($permissionName)) {
                return $next($request);
            }
        }

        abort(403, __('messages.forbidden'));
    }
}
