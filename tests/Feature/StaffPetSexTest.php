<?php

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('staff edit form selects existing lowercase pet sex', function () {
    $staff = User::factory()->create([
        'role' => 'Staff',
    ]);
    $owner = User::factory()->create([
        'role' => 'Pet Owner',
    ]);
    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'sex' => 'female',
    ]);

    $this->actingAs($staff)
        ->get(route('staff.pets.edit', $pet->pet_id))
        ->assertOk()
        ->assertSee('value="Female" selected', false);
});

test('staff cannot save a pet without a sex', function () {
    $staff = User::factory()->create([
        'role' => 'Staff',
    ]);
    $owner = User::factory()->create([
        'role' => 'Pet Owner',
    ]);
    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'sex' => 'Female',
    ]);

    $this->actingAs($staff)
        ->put(route('staff.pets.update', $pet->pet_id), [
            'user_id' => $owner->user_id,
            'pet_name' => $pet->pet_name,
            'species' => $pet->species,
            'breed' => $pet->breed,
            'color' => $pet->color,
            'date_of_birth' => $pet->date_of_birth?->format('Y-m-d'),
            'weight' => $pet->weight,
            'sex' => '',
        ])
        ->assertSessionHasErrors('sex')
        ->assertRedirect();

    expect($pet->fresh()->sex)->toBe('Female');
});
