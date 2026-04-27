<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\MedicalRecord;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class MedicalRecordsWithoutPrescriptionSeeder extends Seeder
{
    /**
     * Seed example medical records that do not yet have linked e-prescriptions.
     */
    public function run(): void
    {
        $vet = User::updateOrCreate(
            ['email' => 'sarah.johnson@vetcare.test'],
            [
                'full_name' => 'Dr. Sarah Johnson',
                'password' => Hash::make('password'),
                'contact_no' => '0917-111-0001',
                'role' => 'Veterinarian',
                'status' => 'Active',
                'is_super_admin' => false,
                'impersonating_role' => null,
            ]
        );

        $owner = User::updateOrCreate(
            ['email' => 'carla.noprescription@example.com'],
            [
                'full_name' => 'Carla Reyes',
                'password' => Hash::make('password'),
                'contact_no' => '0917-444-0001',
                'role' => 'Pet Owner',
                'status' => 'Active',
                'is_super_admin' => false,
                'impersonating_role' => null,
            ]
        );

        $coco = Pet::updateOrCreate(
            ['user_id' => $owner->user_id, 'pet_name' => 'Coco'],
            [
                'species' => 'Dog',
                'breed' => 'Poodle',
                'date_of_birth' => '2022-01-17',
                'weight' => 8.35,
                'sex' => 'Female',
            ]
        );

        $rocky = Pet::updateOrCreate(
            ['user_id' => $owner->user_id, 'pet_name' => 'Rocky'],
            [
                'species' => 'Cat',
                'breed' => 'Domestic Shorthair',
                'date_of_birth' => '2021-08-09',
                'weight' => 5.10,
                'sex' => 'Male',
            ]
        );

        $cocoAppointment = Appointment::firstOrCreate(
            [
                'pet_id' => $coco->pet_id,
                'appointment_date' => '2026-03-19',
                'appointment_time' => '10:00:00',
            ],
            [
                'consultation_mode' => 'In-clinic',
                'reason_for_visit' => 'Routine dental cleaning assessment and oral exam.',
                'status' => 'Completed',
                'proof_of_payment' => null,
            ]
        );

        $rockyAppointment = Appointment::firstOrCreate(
            [
                'pet_id' => $rocky->pet_id,
                'appointment_date' => '2026-03-21',
                'appointment_time' => '13:30:00',
            ],
            [
                'consultation_mode' => 'Teleconsultation',
                'reason_for_visit' => 'Monitoring appetite and hydration after mild stomach upset.',
                'status' => 'Completed',
                'proof_of_payment' => null,
            ]
        );

        $cocoConsultation = Consultation::firstOrCreate(
            ['appointment_id' => $cocoAppointment->appointment_id],
            [
                'veterinarian_id' => $vet->user_id,
                'chief_complaint' => 'Dental tartar buildup and mild gum redness.',
                'ai_guidance_summary' => 'Dental prophylaxis recommended; no medication needed at this time.',
                'consultation_notes' => 'Patient can be managed with cleaning, brushing, and diet guidance.',
                'consultation_date' => Carbon::create(2026, 3, 19, 10, 0),
                'status' => 'Completed',
            ]
        );

        $rockyConsultation = Consultation::firstOrCreate(
            ['appointment_id' => $rockyAppointment->appointment_id],
            [
                'veterinarian_id' => $vet->user_id,
                'chief_complaint' => 'Recent stomach upset with appetite now improving.',
                'ai_guidance_summary' => 'Continue bland diet and observation; no medication indicated unless symptoms return.',
                'consultation_notes' => 'Hydration is good and symptoms are trending down without pharmaceuticals.',
                'consultation_date' => Carbon::create(2026, 3, 21, 13, 30),
                'status' => 'Follow-up',
            ]
        );

        MedicalRecord::updateOrCreate(
            ['consultation_id' => $cocoConsultation->consultation_id],
            [
                'pet_id' => $coco->pet_id,
                'diagnosis' => 'Routine dental prophylaxis candidate',
                'treatment_plan' => 'Proceed with dental cleaning schedule, begin daily brushing, and switch to dental-support diet.',
                'vaccination_notes' => 'Vaccination record reviewed and current.',
                'follow_up_date' => '2026-04-02',
            ]
        );

        MedicalRecord::updateOrCreate(
            ['consultation_id' => $rockyConsultation->consultation_id],
            [
                'pet_id' => $rocky->pet_id,
                'diagnosis' => 'Resolved mild gastritis',
                'treatment_plan' => 'Continue bland diet for 48 hours and monitor stool consistency at home.',
                'vaccination_notes' => 'No vaccination issues discussed during this follow-up.',
                'follow_up_date' => '2026-03-28',
            ]
        );
    }
}
