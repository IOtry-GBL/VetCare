<?php

use App\Models\Appointment;
use App\Models\AdherenceLog;
use App\Models\AdherenceNotification;
use App\Models\Consultation;
use App\Models\EPrescription;
use App\Models\MedicalRecord;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function makeVeterinarianPrescriptionFixture(User $vet, array $overrides = []): array
{
    $owner = User::factory()->create([
        'role' => 'Pet Owner',
    ]);

    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => $overrides['pet_name'] ?? 'Max',
        'species' => $overrides['species'] ?? 'Dog',
        'breed' => $overrides['breed'] ?? 'Golden Retriever',
    ]);

    $appointment = Appointment::factory()->create([
        'pet_id' => $pet->pet_id,
        'consultation_mode' => 'Teleconsultation',
        'status' => 'Completed',
    ]);

    $consultation = Consultation::create([
        'appointment_id' => $appointment->appointment_id,
        'veterinarian_id' => $vet->user_id,
        'chief_complaint' => $overrides['chief_complaint'] ?? 'Lethargy and limping',
        'ai_guidance_summary' => null,
        'consultation_notes' => $overrides['consultation_notes'] ?? 'Patient is stable.',
        'consultation_date' => $overrides['consultation_date'] ?? now()->subDay(),
        'status' => 'Completed',
    ]);

    $medicalRecord = MedicalRecord::create([
        'pet_id' => $pet->pet_id,
        'consultation_id' => $consultation->consultation_id,
        'diagnosis' => $overrides['diagnosis'] ?? 'Mild joint inflammation',
        'treatment_plan' => $overrides['treatment_plan'] ?? 'Rest and anti-inflammatory medication.',
        'vaccination_notes' => $overrides['vaccination_notes'] ?? 'Vaccinations up to date.',
        'follow_up_date' => $overrides['follow_up_date'] ?? now()->addWeek()->toDateString(),
    ]);

    $prescription = null;

    if (($overrides['with_prescription'] ?? true) === true) {
        $prescription = EPrescription::create([
            'record_id' => $medicalRecord->record_id,
            'medication_name' => $overrides['medication_name'] ?? 'Amoxicillin',
            'dosage' => $overrides['dosage'] ?? '250 mg',
            'frequency' => $overrides['frequency'] ?? 'Twice daily',
            'duration' => $overrides['duration'] ?? '7 days',
            'issued_at' => $overrides['issued_at'] ?? now()->subHours(2),
        ]);
    }

    return compact('owner', 'pet', 'appointment', 'consultation', 'medicalRecord', 'prescription');
}

test('veterinarian prescriptions page loads clinic-wide records and prescriptions', function () {
    $vet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $otherVet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $fixture = makeVeterinarianPrescriptionFixture($vet, [
        'pet_name' => 'Mochi',
        'diagnosis' => 'Skin allergy flare-up',
        'medication_name' => 'Cetirizine',
    ]);

    $otherFixture = makeVeterinarianPrescriptionFixture($otherVet, [
        'pet_name' => 'Shadow',
        'diagnosis' => 'Post-surgery check',
        'medication_name' => 'Carprofen',
    ]);

    $response = $this->actingAs($vet)->get(route('vet.prescriptions', [
        'record' => $fixture['medicalRecord']->record_id,
    ]));

    $response->assertOk()
        ->assertViewIs('veterinarian.prescriptions')
        ->assertSee('Mochi')
        ->assertSee('Cetirizine')
        ->assertSee(e(route('vet.prescriptions', [
            'record' => $fixture['medicalRecord']->record_id,
            'edit' => $fixture['prescription']->prescription_id,
        ])), false)
        ->assertSee('name="frequency_unit"', false)
        ->assertViewHas('selectedRecord', function (MedicalRecord $selectedRecord) use ($fixture) {
            return $selectedRecord->record_id === $fixture['medicalRecord']->record_id;
        })
        ->assertViewHas('medicalRecords', function ($medicalRecords) use ($fixture, $otherFixture) {
            $recordIds = $medicalRecords->pluck('record_id');

            return $recordIds->contains($fixture['medicalRecord']->record_id)
                && $recordIds->contains($otherFixture['medicalRecord']->record_id);
        })
        ->assertViewHas('prescriptions', function ($prescriptions) use ($fixture, $otherFixture) {
            $prescriptionIds = $prescriptions->pluck('prescription_id');

            return $prescriptionIds->contains($fixture['prescription']->prescription_id)
                && $prescriptionIds->contains($otherFixture['prescription']->prescription_id);
        });
});

test('veterinarian can edit a prescription and reschedule pending future doses', function () {
    $vet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    Carbon::setTestNow(Carbon::create(2026, 4, 19, 0, 30, 0, 'UTC'));

    $fixture = makeVeterinarianPrescriptionFixture($vet);
    $takenDose = AdherenceLog::create([
        'prescription_id' => $fixture['prescription']->prescription_id,
        'scheduled_datetime' => now()->subDay()->utc(),
        'intake_status' => 'Taken',
        'confirmation_time' => now()->subDay()->utc(),
    ]);
    $pendingDose = AdherenceLog::create([
        'prescription_id' => $fixture['prescription']->prescription_id,
        'scheduled_datetime' => now()->addDay()->utc(),
        'confirmation_deadline' => now()->addDay()->addHours(3)->utc(),
        'intake_status' => 'Pending',
    ]);
    $notification = AdherenceNotification::create([
        'user_id' => $fixture['owner']->user_id,
        'adherence_id' => $pendingDose->adherence_id,
        'medication_name' => $fixture['prescription']->medication_name,
        'dosage' => $fixture['prescription']->dosage,
        'scheduled_at' => $pendingDose->scheduled_datetime,
        'confirmation_deadline' => $pendingDose->confirmation_deadline,
        'status' => 'Pending',
    ]);

    $editPage = $this->actingAs($vet)->get(route('vet.prescriptions', [
        'record' => $fixture['medicalRecord']->record_id,
        'edit' => $fixture['prescription']->prescription_id,
    ]));

    $editPage->assertOk()
        ->assertSee('Edit Prescription: Amoxicillin')
        ->assertSee('value="2"', false)
        ->assertSee('value="7"', false)
        ->assertSee('Update Prescription');

    $response = $this->actingAs($vet)->patch(route('vet.prescriptions.update', [
        'prescription' => $fixture['prescription']->prescription_id,
        'record' => $fixture['medicalRecord']->record_id,
        'edit' => $fixture['prescription']->prescription_id,
    ]), [
        'record_id' => $fixture['medicalRecord']->record_id,
        'medication_name' => 'Updated Amoxicillin',
        'dosage' => '500 mg',
        'frequency_amount' => '1',
        'frequency_unit' => 'week',
        'duration_amount' => '2',
        'duration_unit' => 'week',
    ]);

    $response->assertRedirect(route('vet.prescriptions', [
        'record' => $fixture['medicalRecord']->record_id,
    ]));

    $fixture['prescription']->refresh();
    expect($fixture['prescription']->medication_name)->toBe('Updated Amoxicillin')
        ->and($fixture['prescription']->dosage)->toBe('500 mg')
        ->and($fixture['prescription']->frequency)->toBe('1 time per week')
        ->and($fixture['prescription']->duration)->toBe('2 weeks');

    $this->assertDatabaseHas('adherence_logs', [
        'adherence_id' => $takenDose->adherence_id,
        'intake_status' => 'Taken',
    ]);
    $this->assertDatabaseMissing('adherence_logs', [
        'adherence_id' => $pendingDose->adherence_id,
    ]);
    $this->assertDatabaseMissing('adherence_notifications', [
        'notification_id' => $notification->notification_id,
    ]);
    expect($fixture['prescription']->adherenceLogs()->where('intake_status', 'Pending')->count())->toBe(2);

    Carbon::setTestNow();
});

test('veterinarian can create a prescription with a monthly frequency', function () {
    $vet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $fixture = makeVeterinarianPrescriptionFixture($vet, [
        'with_prescription' => false,
    ]);

    Carbon::setTestNow(Carbon::create(2026, 4, 19, 0, 30, 0, 'UTC'));

    $response = $this->actingAs($vet)->post(route('vet.prescriptions.store'), [
        'record_id' => $fixture['medicalRecord']->record_id,
        'medication_name' => 'Monthly medication',
        'dosage' => '1 tablet',
        'frequency_amount' => '2',
        'frequency_unit' => 'month',
        'duration_amount' => '2',
        'duration_unit' => 'month',
    ]);

    $response->assertRedirect(route('vet.prescriptions', [
        'record' => $fixture['medicalRecord']->record_id,
    ]));

    $prescription = EPrescription::where('record_id', $fixture['medicalRecord']->record_id)
        ->where('medication_name', 'Monthly medication')
        ->firstOrFail();

    expect($prescription->frequency)->toBe('2 times per month')
        ->and($prescription->duration)->toBe('2 months')
        ->and($prescription->adherenceLogs()->count())->toBe(4);

    Carbon::setTestNow();
});

test('veterinarian can create an e-prescription from any clinic medical record', function () {
    $vet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $otherVet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $fixture = makeVeterinarianPrescriptionFixture($otherVet, [
        'with_prescription' => false,
    ]);

    Carbon::setTestNow(Carbon::create(2026, 4, 19, 1, 30, 0, 'UTC'));

    $response = $this->actingAs($vet)->post(route('vet.prescriptions.store'), [
        'record_id' => $fixture['medicalRecord']->record_id,
        'medication_name' => 'Carprofen',
        'dosage' => '50 mg',
        'frequency' => '2',
        'duration' => '5',
    ]);

    $response->assertRedirect(route('vet.prescriptions', [
        'record' => $fixture['medicalRecord']->record_id,
    ]));

    $this->assertDatabaseHas('e_prescriptions', [
        'record_id' => $fixture['medicalRecord']->record_id,
        'medication_name' => 'Carprofen',
        'dosage' => '50 mg',
        'frequency' => 'Twice daily',
        'duration' => '5 days',
    ]);

    $prescription = EPrescription::where('record_id', $fixture['medicalRecord']->record_id)
        ->where('medication_name', 'Carprofen')
        ->firstOrFail();

    expect($prescription->adherenceLogs()->count())->toBe(10);
    expect($prescription->adherenceLogs()->whereNotNull('confirmation_deadline')->count())->toBe(10);

    $firstDose = $prescription->adherenceLogs()->orderBy('scheduled_datetime')->firstOrFail();

    expect($firstDose->scheduled_datetime->timezone('Asia/Manila')->format('Y-m-d H:i'))->toBe('2026-04-19 12:00');
    expect($firstDose->confirmation_deadline->timezone('Asia/Manila')->format('Y-m-d H:i'))->toBe('2026-04-19 15:00');

    Carbon::setTestNow();
});
