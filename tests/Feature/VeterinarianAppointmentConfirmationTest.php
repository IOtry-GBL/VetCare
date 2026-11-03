<?php

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makePendingVeterinarianAppointment(): array
{
    $vet = User::factory()->create([
        'role' => 'Veterinarian',
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

    return compact('vet', 'appointment');
}

test('veterinarian can open and confirm an unassigned pending appointment', function () {
    ['vet' => $vet, 'appointment' => $appointment] = makePendingVeterinarianAppointment();

    $this->actingAs($vet)
        ->get(route('vet.appointments'))
        ->assertOk()
        ->assertSee(route('vet.appointments.confirm', $appointment), false)
        ->assertSee('Review & Confirm');

    $this->actingAs($vet)
        ->get(route('vet.appointments.confirm', $appointment))
        ->assertOk()
        ->assertViewIs('staff.confirm-appointment')
        ->assertSee('This appointment will be assigned to you when confirmed.')
        ->assertSee('name="appointment_date"', false)
        ->assertSee('name="appointment_time"', false);

    $otherVet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $response = $this->actingAs($vet)
        ->post(route('vet.appointments.approve', $appointment), [
            'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
            'appointment_time' => $appointment->appointment_time->format('H:i'),
            'consultation_mode' => 'In-clinic',
            'status' => 'Approved',
            'vet_id' => $otherVet->user_id,
        ]);

    $response->assertRedirect(route('vet.appointments'));

    $this->assertDatabaseHas('appointments', [
        'appointment_id' => $appointment->appointment_id,
        'status' => 'Approved',
    ]);

    $this->assertDatabaseHas('consultations', [
        'appointment_id' => $appointment->appointment_id,
        'veterinarian_id' => $vet->user_id,
        'status' => 'Open',
    ]);
});

test('veterinarian cannot confirm an appointment assigned to another veterinarian', function () {
    ['vet' => $vet, 'appointment' => $appointment] = makePendingVeterinarianAppointment();
    $otherVet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    Consultation::create([
        'appointment_id' => $appointment->appointment_id,
        'veterinarian_id' => $otherVet->user_id,
        'chief_complaint' => 'Existing assignment',
        'consultation_date' => $appointment->appointment_date,
        'status' => 'Open',
    ]);

    $this->actingAs($vet)
        ->get(route('vet.appointments.confirm', $appointment))
        ->assertNotFound();
});
