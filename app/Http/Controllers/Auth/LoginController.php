<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        $defaults = config('super-admin.settings', []);

        return view('auth.login', [
            'clinic' => [
                'name' => SystemSetting::getValue('clinic_name', $defaults['clinic_name'] ?? 'Digos Veterinary Clinic and Supply'),
                'phone' => SystemSetting::getValue('clinic_phone', $defaults['clinic_phone'] ?? ''),
                'address' => SystemSetting::getValue('clinic_address', $defaults['clinic_address'] ?? ''),
                'description' => SystemSetting::getValue('clinic_description', $defaults['clinic_description'] ?? ''),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = auth()->user();

            if ($user->isPetOwner() && ! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
            
            // Redirect based on role
            if ($user->isSuperAdmin()) {
                return redirect('/super-admin/dashboard');
            }

            return match($user->role) {
                'Pet Owner' => redirect('/pet-owner/dashboard'),
                'Veterinarian' => redirect('/vet/dashboard'),
                'Staff' => redirect('/staff/dashboard'),
                'Admin' => redirect('/admin/dashboard'),
                default => redirect('/'),
            };
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
}
