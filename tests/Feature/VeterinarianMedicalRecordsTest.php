<?php

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\EPrescription;
use App\Models\MedicalRecord;
use App\Models\Pet;
use App\Models\User;
use Database\Seeders\ExampleClinicSeeder;
use Database\Seeders\MedicalRecordsWithoutPrescriptionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeVeterinarianMedicalRecordFixture(User $vet, array $overrides = []): array
{
    $owner = User::factory()->create([
        'role' => 'Pet Owner',
    ]);

    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => $overrides['pet_name'] ?? 'Buddy',
        'species' => $overrides['species'] ?? 'Dog',
        'breed' => $overrides['breed'] ?? 'Shih Tzu',
    ]);

    $appointment = Appointment::factory()->create([
        'pet_id' => $pet->pet_id,
        'consultation_mode' => $overrides['consultation_mode'] ?? 'In-clinic',
        'status' => 'Completed',
    ]);

    $consultation = Consultation::create([
        'appointment_id' => $appointment->appointment_id,
        'veterinarian_id' => $vet->user_id,
        'chief_complaint' => $overrides['chief_complaint'] ?? 'Loss of appetite',
        'ai_guidance_summary' => null,
        'consultation_notes' => $overrides['consultation_notes'] ?? 'Patient is stable and responsive.',
        'consultation_date' => $overrides['consultation_date'] ?? now()->subDay(),
        'status' => 'Completed',
    ]);

    $medicalRecord = MedicalRecord::create([
        'pet_id' => $pet->pet_id,
        'consultation_id' => $consultation->consultation_id,
        'diagnosis' => $overrides['diagnosis'] ?? 'Mild gastritis',
        'treatment_plan' => $overrides['treatment_plan'] ?? 'Small frequent meals and hydration support.',
        'vaccination_notes' => $overrides['vaccination_notes'] ?? 'Vaccination schedule is current.',
        'follow_up_date' => $overrides['follow_up_date'] ?? now()->addWeek()->toDateString(),
    ]);

    $prescription = EPrescription::create([
        'record_id' => $medicalRecord->record_id,
        'medication_name' => $overrides['medication_name'] ?? 'Probiotic Paste',
        'dosage' => $overrides['dosage'] ?? '2 mL',
        'frequency' => $overrides['frequency'] ?? 'Twice daily',
        'duration' => $overrides['duration'] ?? '5 days',
        'issued_at' => $overrides['issued_at'] ?? now()->subHours(2),
    ]);

    return compact('owner', 'pet', 'appointment', 'consultation', 'medicalRecord', 'prescription');
}

test('veterinarian medical records page loads clinic-wide patient records', function () {
    $vet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $otherVet = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $fixture = makeVeterinarianMedicalRecordFixture($vet, [
        'pet_name' => 'Milo',
        'diagnosis' => 'Canine dermatitis',
    ]);

    $otherFixture = makeVeterinarianMedicalRecordFixture($otherVet, [
        'pet_name' => 'Shadow',
        'diagnosis' => 'Ear infection',
    ]);

    $response = $this->actingAs($vet)->get(route('vet.medical-records', [
        'patient' => $fixture['pet']->pet_id,
    ]));

    $response->assertOk()
        ->assertViewIs('veterinarian.medical-records')
        ->assertSee('Milo')
        ->assertSee('Shadow')
        ->assertSee('Canine dermatitis')
        ->assertViewHas('selectedPatient', function (Pet $selectedPatient) use ($fixture) {
            return $selectedPatient->pet_id === $fixture['pet']->pet_id;
        })
        ->assertViewHas('selectedPatientRecords', function ($selectedPatientRecords) use ($fixture, $otherFixture) {
            $recordIds = $selectedPatientRecords->pluck('record_id');

            return $recordIds->contains($fixture['medicalRecord']->record_id)
                && ! $recordIds->contains($otherFixture['medicalRecord']->record_id);
        })
        ->assertViewHas('patients', function ($patients) use ($fixture, $otherFixture) {
            $patientIds = $patients->pluck('pet_id');

            return $patientIds->contains($fixture['pet']->pet_id)
                && $patientIds->contains($otherFixture['pet']->pet_id);
        });

    $otherPatientResponse = $this->actingAs($vet)->get(route('vet.medical-records', [
        'patient' => $otherFixture['pet']->pet_id,
    ]));

    $otherPatientResponse->assertOk()
        ->assertSee('Shadow')
        ->assertSee('Ear infection');
});

test('example clinic seeder creates current medical record and prescription data', function () {
    $this->seed(ExampleClinicSeeder::class);

    $this->assertDatabaseHas('users', [
        'email' => 'sarah.johnson@vetcare.test',
        'role' => 'Veterinarian',
    ]);

    $this->assertDatabaseHas('pets', [
        'pet_name' => 'Max',
        'species' => 'Dog',
    ]);

    $this->assertDatabaseHas('medical_records', [
        'diagnosis' => 'Seasonal allergic dermatitis',
    ]);

    $this->assertDatabaseHas('e_prescriptions', [
        'medication_name' => 'Cetirizine',
        'duration' => '14 days',
    ]);
});

test('medical records without prescription seeder creates records with no linked e-prescriptions', function () {
    $this->seed(MedicalRecordsWithoutPrescriptionSeeder::class);

    $record = MedicalRecord::where('diagnosis', 'Routine dental prophylaxis candidate')->first();

    expect($record)->not->toBeNull();

    $this->assertDatabaseHas('medical_records', [
        'diagnosis' => 'Resolved mild gastritis',
    ]);

    $this->assertDatabaseMissing('e_prescriptions', [
        'record_id' => $record->record_id,
    ]);
});
