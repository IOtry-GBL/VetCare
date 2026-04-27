<?php

use App\Models\Appointment;
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
