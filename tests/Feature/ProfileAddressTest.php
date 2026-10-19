<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('users can view and update their address in profile settings', function () {
    $user = User::factory()->create([
        'role' => 'Pet Owner',
        'address' => 'Old address',
    ]);

    $this->actingAs($user)
        ->get(route('profile.settings'))
        ->assertOk()
        ->assertSee('name="address"', false)
        ->assertSee('Old address');

    $this->actingAs($user)
        ->put(route('profile.settings.update'), [
            'full_name' => $user->full_name,
            'email' => $user->email,
            'contact_no' => $user->contact_no,
            'address' => '42 New Street, Springvale',
        ])
        ->assertRedirect(route('profile.settings'));

    $this->assertDatabaseHas('users', [
        'user_id' => $user->user_id,
        'address' => '42 New Street, Springvale',
    ]);
});

test('super admin profile stays on the super admin dashboard regardless of impersonated role', function () {
    $superAdmin = User::factory()->create([
        'role' => 'Staff',
        'is_super_admin' => true,
        'impersonating_role' => 'admin',
        'address' => 'Old address',
    ]);

    $this->actingAs($superAdmin)
        ->get(route('profile.settings'))
        ->assertOk()
        ->assertSee('href="'.route('super-admin.dashboard').'"', false)
        ->assertDontSee('href="'.route('vet.dashboard').'"', false)
        ->assertDontSee('href="'.route('staff.dashboard').'"', false)
        ->assertDontSee('href="'.route('admin.dashboard').'"', false)
        ->assertDontSee('>Role</label>', false);

    $this->put(route('profile.settings.update'), [
        'full_name' => 'Updated Super Admin',
        'email' => $superAdmin->email,
        'contact_no' => $superAdmin->contact_no,
        'address' => 'Updated address',
    ])
        ->assertRedirect(route('profile.settings'));

    $this->assertDatabaseHas('users', [
        'user_id' => $superAdmin->user_id,
        'full_name' => 'Updated Super Admin',
        'address' => 'Updated address',
        'impersonating_role' => 'admin',
    ]);

    $this->get(route('profile.settings'))
        ->assertOk()
        ->assertSee('href="'.route('super-admin.dashboard').'"', false)
        ->assertDontSee('href="'.route('vet.dashboard').'"', false);
});

test('staff can add an address when registering a client', function () {
    $staff = User::factory()->create([
        'role' => 'Staff',
    ]);

    $this->actingAs($staff)
        ->post(route('staff.store-client'), [
            'full_name' => 'New Client',
            'email' => 'new-client@example.com',
            'contact_no' => '09170000000',
            'address' => '15 Clinic Road',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertRedirect(route('staff.clients'));

    $this->assertDatabaseHas('users', [
        'email' => 'new-client@example.com',
        'address' => '15 Clinic Road',
        'role' => 'Pet Owner',
    ]);
});
