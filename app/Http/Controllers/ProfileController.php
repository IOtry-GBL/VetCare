<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the profile settings page for the signed-in user.
     */
    public function edit()
    {
        return view('profile.settings', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the signed-in user's profile details.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if ($request->filled('name') && !$request->filled('full_name')) {
            $request->merge(['full_name' => $request->input('name')]);
        }

        if ($request->filled('phone') && !$request->filled('contact_no')) {
            $request->merge(['contact_no' => $request->input('phone')]);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')],
            'contact_no' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $payload = [
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'contact_no' => $validated['contact_no'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()->route('profile.settings')
            ->with('success', 'Profile settings updated successfully.');
    }
}
