<?php

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeSuperAdminFixture(): array
{
    $superAdmin = User::factory()->create([
        'role' => 'Staff',
        'is_super_admin' => true,
        'full_name' => 'System Root',
    ]);

    $owner = User::factory()->create([
        'role' => 'Pet Owner',
        'full_name' => 'Jamie Owner',
    ]);

    $vet = User::factory()->create([
        'role' => 'Veterinarian',
        'full_name' => 'Dr. Vera',
    ]);

    $staff = User::factory()->create([
        'role' => 'Staff',
        'full_name' => 'Sam Staff',
    ]);

    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => 'Pebble',
    ]);

    $appointment = Appointment::factory()->create([
        'pet_id' => $pet->pet_id,
        'status' => 'Pending',
    ]);

    return compact('superAdmin', 'owner', 'vet', 'staff', 'pet', 'appointment');
}

test('super admin dashboard loads', function () {
    $fixture = makeSuperAdminFixture();

    $response = $this->actingAs($fixture['superAdmin'])
        ->get(route('super-admin.dashboard'));

    $response->assertOk()
        ->assertViewIs('super-admin.dashboard')
        ->assertSee('Super Admin Dashboard')
        ->assertSee('Role Switching')
        ->assertSee('Total Users');
});

test('super admin users page loads', function () {
    $fixture = makeSuperAdminFixture();

    $response = $this->actingAs($fixture['superAdmin'])
        ->get(route('super-admin.users'));

    $response->assertOk()
        ->assertViewIs('super-admin.users')
        ->assertSee('Super Admin Users')
        ->assertSee('Jamie Owner')
        ->assertSee('System Root');
});

test('super admin analytics page loads', function () {
    $fixture = makeSuperAdminFixture();

    $response = $this->actingAs($fixture['superAdmin'])
        ->get(route('super-admin.analytics'));

    $response->assertOk()
        ->assertViewIs('super-admin.analytics')
        ->assertSee('Super Admin Analytics')
        ->assertSee('Users By Role')
        ->assertSee('Appointments By Status');
});
