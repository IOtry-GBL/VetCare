<?php

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\ConsultationMessage;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pet owners can view only their teleconsultations', function () {
    $owner = User::factory()->create([
        'role' => 'Pet Owner',
    ]);

    $otherOwner = User::factory()->create([
        'role' => 'Pet Owner',
    ]);

    $vet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $ownerPet = Pet::factory()->create([
        'user_id' => $owner->user_id,
    ]);

    $otherPet = Pet::factory()->create([
        'user_id' => $otherOwner->user_id,
    ]);

    $ownerAppointment = Appointment::factory()->create([
        'pet_id' => $ownerPet->pet_id,
        'consultation_mode' => 'Teleconsultation',
    ]);

    $otherAppointment = Appointment::factory()->create([
        'pet_id' => $otherPet->pet_id,
        'consultation_mode' => 'Teleconsultation',
    ]);

    $ownerConsultation = Consultation::create([
        'appointment_id' => $ownerAppointment->appointment_id,
        'veterinarian_id' => $vet->user_id,
        'chief_complaint' => 'Limping for three days',
        'ai_guidance_summary' => 'Monitor mobility and swelling.',
        'consultation_notes' => 'Schedule a follow-up if symptoms persist.',
        'consultation_date' => now(),
        'status' => 'Open',
    ]);

    $otherConsultation = Consultation::create([
        'appointment_id' => $otherAppointment->appointment_id,
        'veterinarian_id' => $vet->user_id,
        'chief_complaint' => 'Loss of appetite',
        'ai_guidance_summary' => null,
        'consultation_notes' => null,
        'consultation_date' => now()->subHour(),
        'status' => 'Open',
    ]);

    $response = $this->actingAs($owner)->get(route('pet-owner.teleconsultation'));

    $response->assertOk()
        ->assertViewIs('pet-owner.teleconsultation')
        ->assertViewHas('consultations', function ($consultations) use ($ownerConsultation, $otherConsultation) {
            $consultationIds = $consultations->pluck('consultation_id');

            return $consultationIds->contains($ownerConsultation->consultation_id)
                && ! $consultationIds->contains($otherConsultation->consultation_id)
                && $consultations->every(fn (Consultation $consultation) => $consultation->relationLoaded('appointment') && $consultation->relationLoaded('veterinarian'));
        });
});

test('pet owners can send messages in their teleconsultations', function () {
    $owner = User::factory()->create([
        'role' => 'Pet Owner',
    ]);

    $vet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
    ]);

    $appointment = Appointment::factory()->create([
        'pet_id' => $pet->pet_id,
        'consultation_mode' => 'Teleconsultation',
    ]);

    $consultation = Consultation::create([
        'appointment_id' => $appointment->appointment_id,
        'veterinarian_id' => $vet->user_id,
        'chief_complaint' => 'Vomiting after meals',
        'consultation_date' => now(),
        'status' => 'Open',
    ]);

    $response = $this->actingAs($owner)->post(
        route('pet-owner.teleconsultation.messages.store', $consultation),
        ['message_body' => 'My pet has been vomiting since yesterday evening.']
    );

    $response->assertRedirect(route('pet-owner.teleconsultation', [
        'consultation' => $consultation->consultation_id,
    ]));

    $this->assertDatabaseHas('consultation_messages', [
        'consultation_id' => $consultation->consultation_id,
        'sender_id' => $owner->user_id,
        'message_body' => 'My pet has been vomiting since yesterday evening.',
    ]);

    $this->actingAs($owner)
        ->get(route('pet-owner.teleconsultation', ['consultation' => $consultation->consultation_id]))
        ->assertOk()
        ->assertSee('My pet has been vomiting since yesterday evening.');
});

test('staff can review owner teleconsultation messages and reply', function () {
    $owner = User::factory()->create([
        'role' => 'Pet Owner',
    ]);

    $staff = User::factory()->create([
        'role' => 'Staff',
    ]);

    $vet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
    ]);

    $appointment = Appointment::factory()->create([
        'pet_id' => $pet->pet_id,
        'consultation_mode' => 'Teleconsultation',
    ]);

    $consultation = Consultation::create([
        'appointment_id' => $appointment->appointment_id,
        'veterinarian_id' => $vet->user_id,
        'chief_complaint' => 'Coughing and low energy',
        'consultation_date' => now(),
        'status' => 'Open',
    ]);

    ConsultationMessage::create([
        'consultation_id' => $consultation->consultation_id,
        'sender_id' => $owner->user_id,
        'message_body' => 'My dog has been coughing all morning.',
    ]);

    $this->actingAs($staff)
        ->get(route('staff.consultations', ['consultation' => $consultation->consultation_id]))
        ->assertOk()
        ->assertSee($owner->name)
        ->assertSee('My dog has been coughing all morning.');

    $response = $this->actingAs($staff)->post(
        route('staff.consultations.messages.store', $consultation),
        ['message_body' => 'Thank you for the update. Please keep your dog hydrated while the vet reviews the case.']
    );

    $response->assertRedirect(route('staff.consultations', [
        'consultation' => $consultation->consultation_id,
    ]));

    $this->assertDatabaseHas('consultation_messages', [
        'consultation_id' => $consultation->consultation_id,
        'sender_id' => $staff->user_id,
        'message_body' => 'Thank you for the update. Please keep your dog hydrated while the vet reviews the case.',
    ]);

    $this->actingAs($owner)
        ->get(route('pet-owner.teleconsultation', ['consultation' => $consultation->consultation_id]))
        ->assertOk()
        ->assertSee('My dog has been coughing all morning.')
        ->assertSee('Thank you for the update. Please keep your dog hydrated while the vet reviews the case.');
});
