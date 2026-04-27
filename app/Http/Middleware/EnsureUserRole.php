<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Super admin can access any role, but respect impersonating_role if set
        if ($user->isSuperAdmin()) {
            // If super admin is impersonating a role, check if they're trying to access that role
            if ($user->impersonating_role) {
                $requiredRole = $user->translateRole($role);
                $impersonatedRole = $user->translateRole($user->impersonating_role);
                // Only allow access if the required role matches the impersonated role
                if ($requiredRole !== $impersonatedRole) {
                    abort(403, 'You are impersonating a different role');
                }
            }
            return $next($request);
        }

        // Check if user has the required role
        if (!$user->hasRole($role)) {
            abort(403, 'Unauthorized role access');
        }

        return $next($request);
    }
}
