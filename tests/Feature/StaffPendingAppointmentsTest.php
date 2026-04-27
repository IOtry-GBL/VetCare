<?php

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('staff pending appointments route reuses the queue view', function () {
    $staff = User::factory()->create([
        'role' => 'Staff',
    ]);

    $owner = User::factory()->create([
        'role' => 'Pet Owner',
    ]);

    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => 'Peanut',
        'species' => 'Dog',
        'breed' => 'Mixed Breed',
    ]);

    $appointment = Appointment::factory()->create([
        'pet_id' => $pet->pet_id,
        'appointment_date' => now()->addDay()->toDateString(),
        'appointment_time' => '10:00',
        'consultation_mode' => 'In-clinic',
        'reason_for_visit' => 'Persistent scratching',
        'status' => 'Pending',
    ]);

    $response = $this->actingAs($staff)
        ->get(route('staff.appointments.pending'));

    $response->assertOk()
        ->assertViewIs('staff.queue')
        ->assertSee('Peanut')
        ->assertViewHas('pendingAppointments', function ($pendingAppointments) use ($appointment) {
            return $pendingAppointments->pluck('appointment_id')->contains($appointment->appointment_id);
        });
});
