<?php

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function petOwnerDateOfBirthPayload(array $overrides = []): array
{
    return array_merge([
        'pet_name' => 'Coco',
        'species' => 'Dog',
        'breed' => 'Mixed breed',
        'date_of_birth' => now()->subMonths(4)->toDateString(),
        'weight' => 4.5,
        'sex' => 'female',
    ], $overrides);
}

test('pet owner can create a pet with a calendar date of birth', function () {
    $owner = User::factory()->create(['role' => 'Pet Owner']);

    $this->actingAs($owner)
        ->post(route('pet-owner.pets.store'), petOwnerDateOfBirthPayload())
        ->assertRedirect(route('pet-owner.pets'));

    $this->assertDatabaseHas('pets', [
        'user_id' => $owner->user_id,
        'pet_name' => 'Coco',
        'date_of_birth' => now()->subMonths(4)->toDateString(),
    ]);
});

test('pet owner can select and save a pet color', function () {
    $owner = User::factory()->create(['role' => 'Pet Owner']);

    $this->actingAs($owner)
        ->post(route('pet-owner.pets.store'), petOwnerDateOfBirthPayload([
            'color' => 'Golden',
        ]))
        ->assertRedirect(route('pet-owner.pets'));

    $this->assertDatabaseHas('pets', [
        'user_id' => $owner->user_id,
        'pet_name' => 'Coco',
        'color' => 'Golden',
    ]);
});

test('pet owner can update a pet date of birth', function () {
    $owner = User::factory()->create(['role' => 'Pet Owner']);
    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'date_of_birth' => now()->subMonths(5)->toDateString(),
    ]);

    $this->actingAs($owner)
        ->put(route('pet-owner.pets.update', $pet->pet_id), petOwnerDateOfBirthPayload([
            'date_of_birth' => now()->subYears(2)->subMonths(3)->toDateString(),
        ]))
        ->assertRedirect(route('pet-owner.pets'));

    $this->assertDatabaseHas('pets', [
        'pet_id' => $pet->pet_id,
        'date_of_birth' => now()->subYears(2)->subMonths(3)->toDateString(),
    ]);
});

test('owner pet forms use a date picker and prevent future birth dates', function () {
    $owner = User::factory()->create(['role' => 'Pet Owner']);
    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'date_of_birth' => now()->subMonths(5)->toDateString(),
    ]);

    $this->actingAs($owner)
        ->get(route('pet-owner.pets.create'))
        ->assertOk()
        ->assertSee('type="date"', false)
        ->assertSee('name="date_of_birth"', false)
        ->assertSee('max="'.now()->toDateString().'"', false)
        ->assertSee('name="color"', false);

    $this->actingAs($owner)
        ->get(route('pet-owner.pets.edit', $pet->pet_id))
        ->assertOk()
        ->assertSee('type="date"', false)
        ->assertSee('name="date_of_birth"', false)
        ->assertSee('max="'.now()->toDateString().'"', false)
        ->assertSee('name="color"', false);
});

test('pet age display recalculates from its date of birth', function () {
    $pet = Pet::factory()->create([
        'date_of_birth' => now()->subMonths(4)->toDateString(),
    ]);

    expect($pet->age_display)->toBe('4 months');

    $pet->date_of_birth = now()->subYears(2)->subMonths(3)->toDateString();
    $pet->save();

    expect($pet->fresh()->age_display)->toBe('2 years, 3 months');
});