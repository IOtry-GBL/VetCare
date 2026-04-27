<?php

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\EPrescription;
use App\Models\MedicalRecord;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeAdminViewFixture(): array
{
    $admin = User::factory()->create([
        'role' => 'Staff',
        'is_super_admin' => true,
        'impersonating_role' => 'admin',
        'full_name' => 'Admin View User',
    ]);

    $owner = User::factory()->create([
        'role' => 'Pet Owner',
        'full_name' => 'Owner Example',
    ]);

    $vet = User::factory()->create([
        'role' => 'Veterinarian',
        'full_name' => 'Dr. Admin Fixture',
    ]);

    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => 'Pixel',
    ]);

    $appointment = Appointment::factory()->create([
        'pet_id' => $pet->pet_id,
        'status' => 'Completed',
    ]);

    $consultation = Consultation::create([
        'appointment_id' => $appointment->appointment_id,
        'veterinarian_id' => $vet->user_id,
        'chief_complaint' => 'Routine wellness visit',
        'ai_guidance_summary' => null,
        'consultation_notes' => 'Stable and ready for discharge.',
        'consultation_date' => now(),
        'status' => 'Completed',
    ]);

    $medicalRecord = MedicalRecord::create([
        'pet_id' => $pet->pet_id,
        'consultation_id' => $consultation->consultation_id,
        'diagnosis' => 'Routine wellness visit',
        'treatment_plan' => 'Continue observation.',
        'vaccination_notes' => 'Up to date.',
        'follow_up_date' => now()->addWeek()->toDateString(),
    ]);

    EPrescription::create([
        'record_id' => $medicalRecord->record_id,
        'medication_name' => 'Vitamin Supplement',
        'dosage' => '1 tablet',
        'frequency' => 'Once daily',
        'duration' => '7 days',
        'issued_at' => now(),
    ]);

    return compact('admin', 'owner', 'vet', 'pet', 'appointment', 'consultation', 'medicalRecord');
}

test('admin dashboard loads with analytics link available', function () {
    $fixture = makeAdminViewFixture();

    $response = $this->actingAs($fixture['admin'])
        ->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertViewIs('admin.dashboard')
        ->assertSee('System administration and analytics overview.')
        ->assertSee('View Analytics');
});

test('admin users page loads with analytics sidebar link', function () {
    $fixture = makeAdminViewFixture();

    $response = $this->actingAs($fixture['admin'])
        ->get(route('admin.users'));

    $response->assertOk()
        ->assertViewIs('admin.users')
        ->assertSee('User Management')
        ->assertSee('Analytics');
});

test('admin analytics page loads', function () {
    $fixture = makeAdminViewFixture();

    $response = $this->actingAs($fixture['admin'])
        ->get(route('admin.analytics'));

    $response->assertOk()
        ->assertViewIs('admin.analytics')
        ->assertSee('System Analytics');
});
