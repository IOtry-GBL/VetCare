<?php

namespace Database\Factories;

use App\Models\Pet;
use Illuminate\Database\Eloquent\Factories\Factory;

class PetFactory extends Factory
{
    protected $model = Pet::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'pet_name' => fake()->firstName(),
            'species' => fake()->randomElement(['Dog', 'Cat', 'Bird', 'Rabbit', 'Hamster']),
            'breed' => fake()->word(),
            'sex' => fake()->randomElement(['Male', 'Female']),
            'date_of_birth' => fake()->dateTimeBetween('-5 years', 'now'),
            'weight' => fake()->randomFloat(2, 1, 50),
        ];
    }
}
