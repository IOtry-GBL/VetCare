<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    /**
     * Show super admin dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }

        $stats = [
            'total_users' => \App\Models\User::count(),
            'owners' => \App\Models\User::where('role', 'Pet Owner')->count(),
            'vets' => \App\Models\User::where('role', 'Veterinarian')->count(),
            'staff' => \App\Models\User::where('role', 'Staff')->count(),
            'admins' => \App\Models\User::where('is_super_admin', true)->count(),
            'total_pets' => \App\Models\Pet::count(),
            'total_appointments' => \App\Models\Appointment::count(),
        ];

        return view('super-admin.dashboard', [
            'stats' => $stats,
            'currentRole' => $user->impersonating_role ?? $user->role,
        ]);
    }

    /**
     * Switch to a specific role dashboard
     */
    public function switchRole(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }

        $role = $request->input('role');
        $validRoles = ['owner', 'vet', 'staff', 'admin'];

        if (!in_array($role, $validRoles)) {
            return redirect()->back()->with('error', 'Invalid role selected');
        }

        // Set the impersonating role
        $user->update([
            'impersonating_role' => $role,
        ]);

        // Redirect to the appropriate dashboard
        return match ($role) {
            'owner' => redirect()->route('pet-owner.dashboard')->with('success', "Switched to Pet Owner view"),
            'vet' => redirect()->route('vet.dashboard')->with('success', "Switched to Veterinarian view"),
            'staff' => redirect()->route('staff.dashboard')->with('success', "Switched to Staff view"),
            'admin' => redirect()->route('admin.dashboard')->with('success', "Switched to Admin view"),
        };
    }

    /**
     * Reset to super admin view
     */
    public function resetToSuperAdmin()
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }

        $user->update([
            'impersonating_role' => null,
        ]);

        return redirect()->route('super-admin.dashboard')->with('success', 'Reset to Super Admin view');
    }

    /**
     * View all users
     */
    public function users()
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }

        $users = \App\Models\User::orderBy('created_at', 'desc')->paginate(20);
        $roleStats = [
            'owners' => \App\Models\User::where('role', 'Pet Owner')->count(),
            'vets' => \App\Models\User::where('role', 'Veterinarian')->count(),
            'staff' => \App\Models\User::where('role', 'Staff')->count(),
            'super_admins' => \App\Models\User::where('is_super_admin', true)->count(),
        ];

        return view('super-admin.users', [
            'users' => $users,
            'roleStats' => $roleStats,
        ]);
    }

    /**
     * View system analytics
     */
    public function analytics()
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }

        $analytics = [
            'users_by_role' => \App\Models\User::selectRaw('role, count(*) as count')
                ->groupBy('role')
                ->get(),
            'appointments_by_status' => \App\Models\Appointment::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get(),
            'recent_users' => \App\Models\User::orderBy('created_at', 'desc')->limit(10)->get(),
        ];

        return view('super-admin.analytics', $analytics);
    }
}
