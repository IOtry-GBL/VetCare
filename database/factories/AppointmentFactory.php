<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'pet_id' => 1,
            'appointment_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'appointment_time' => fake()->time('H:i'),
            'consultation_mode' => fake()->randomElement(['In-clinic', 'Teleconsultation']),
            'reason_for_visit' => fake()->sentence(),
            'status' => fake()->randomElement(['Pending', 'Approved', 'Completed', 'Cancelled', 'Missed']),
            'proof_of_payment' => null,
        ];
    }
}
