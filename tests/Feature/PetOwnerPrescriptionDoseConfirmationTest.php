<?php

use App\Models\AdherenceLog;
use App\Models\AdherenceNotification;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\EPrescription;
use App\Models\MedicalRecord;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function makePetOwnerPrescriptionDoseFixture(array $overrides = []): array
{
    $owner = User::factory()->create([
        'role' => 'Pet Owner',
    ]);

    $vet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => 'Toffee',
        'species' => 'Dog',
    ]);

    $appointment = Appointment::factory()->create([
        'pet_id' => $pet->pet_id,
        'status' => 'Completed',
    ]);

    $consultation = Consultation::create([
        'appointment_id' => $appointment->appointment_id,
        'veterinarian_id' => $vet->user_id,
        'chief_complaint' => 'Itching',
        'ai_guidance_summary' => null,
        'consultation_notes' => 'Monitor daily.',
        'consultation_date' => now()->subDay(),
        'status' => 'Completed',
    ]);

    $record = MedicalRecord::create([
        'pet_id' => $pet->pet_id,
        'consultation_id' => $consultation->consultation_id,
        'diagnosis' => 'Mild dermatitis',
        'treatment_plan' => 'Daily medication for one week.',
        'vaccination_notes' => null,
        'follow_up_date' => now()->addWeek()->toDateString(),
    ]);

    $prescription = EPrescription::create([
        'record_id' => $record->record_id,
        'medication_name' => $overrides['medication_name'] ?? 'Cetirizine',
        'dosage' => $overrides['dosage'] ?? '10 mg',
        'frequency' => $overrides['frequency'] ?? 'Twice daily',
        'duration' => $overrides['duration'] ?? '5 days',
        'issued_at' => $overrides['issued_at'] ?? now()->subHours(2),
    ]);

    $adherenceLog = AdherenceLog::create([
        'prescription_id' => $prescription->prescription_id,
        'scheduled_datetime' => $overrides['scheduled_datetime'] ?? now()->subMinutes(30),
        'confirmation_deadline' => $overrides['confirmation_deadline'] ?? now()->addHours(2)->addMinutes(30),
        'intake_status' => $overrides['intake_status'] ?? 'Pending',
        'is_notified' => $overrides['is_notified'] ?? false,
    ]);

    if (($overrides['with_notification'] ?? true) === true) {
        AdherenceNotification::create([
            'user_id' => $owner->user_id,
            'adherence_id' => $adherenceLog->adherence_id,
            'medication_name' => $prescription->medication_name,
            'dosage' => $prescription->dosage,
            'scheduled_at' => $adherenceLog->scheduled_datetime,
            'confirmation_deadline' => $adherenceLog->confirmation_deadline,
            'status' => 'Pending',
        ]);
    }

    return compact('owner', 'vet', 'pet', 'record', 'prescription', 'adherenceLog');
}

test('pet owner prescriptions page shows direct dose confirmation controls', function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 20, 2, 0, 0, 'UTC'));

    $fixture = makePetOwnerPrescriptionDoseFixture([
        'scheduled_datetime' => Carbon::create(2026, 4, 20, 1, 30, 0, 'UTC'),
        'confirmation_deadline' => Carbon::create(2026, 4, 20, 4, 30, 0, 'UTC'),
        'with_notification' => true,
    ]);

    $response = $this->actingAs($fixture['owner'])
        ->get(route('pet-owner.prescriptions'));

    $response->assertOk()
        ->assertSee('Dose Confirmations')
        ->assertSee('Confirm Dose')
        ->assertSee('View Full History')
        ->assertSee($fixture['prescription']->medication_name);

    Carbon::setTestNow();
});

test('pet owner can confirm a due dose directly from the prescriptions page', function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 20, 2, 0, 0, 'UTC'));

    $fixture = makePetOwnerPrescriptionDoseFixture([
        'scheduled_datetime' => Carbon::create(2026, 4, 20, 1, 30, 0, 'UTC'),
        'confirmation_deadline' => Carbon::create(2026, 4, 20, 4, 30, 0, 'UTC'),
        'with_notification' => false,
    ]);

    $response = $this->actingAs($fixture['owner'])
        ->from(route('pet-owner.prescriptions'))
        ->post(route('adherence.confirm-dose', $fixture['adherenceLog']));

    $response->assertRedirect(route('pet-owner.prescriptions'));

    $this->assertDatabaseHas('adherence_logs', [
        'adherence_id' => $fixture['adherenceLog']->adherence_id,
        'intake_status' => 'Taken',
    ]);

    $this->assertDatabaseHas('adherence_notifications', [
        'adherence_id' => $fixture['adherenceLog']->adherence_id,
        'status' => 'Confirmed',
    ]);

    Carbon::setTestNow();
});
