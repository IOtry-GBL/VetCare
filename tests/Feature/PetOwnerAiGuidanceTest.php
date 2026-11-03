<?php

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('pet page AI sidebar opens the symptom checker', function () {
    $owner = User::factory()->create(['role' => 'Pet Owner']);

    $this->actingAs($owner)
        ->get(route('pet-owner.pets'))
        ->assertOk()
        ->assertSee('window.location.assign('.json_encode(route('pet-owner.symptom-checker')), false);
});

test('pet owner symptom checker offers only their own pets for selection', function () {
    $owner = User::factory()->create(['role' => 'Pet Owner']);
    $otherOwner = User::factory()->create(['role' => 'Pet Owner']);

    Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => 'Coco',
    ]);
    Pet::factory()->create([
        'user_id' => $otherOwner->user_id,
        'pet_name' => 'Shadow',
    ]);
    Http::fake(['127.0.0.1:8001/symptoms' => Http::response(['symptoms' => ['vomiting', 'weakness']])]);

    $this->actingAs($owner)
        ->get(route('pet-owner.symptom-checker'))
        ->assertOk()
        ->assertViewIs('pet-owner.symptom-checker')
        ->assertSee('Pet to scan')
        ->assertSee('Search dataset symptoms')
        ->assertSee('name="symptoms[]"', false)
        ->assertSee('Vomiting')
        ->assertSee('Coco')
        ->assertDontSee('Shadow');
});

test('pet owner can request guidance for an owned pet using its saved profile', function () {
    $owner = User::factory()->create(['role' => 'Pet Owner']);
    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => 'Coco',
        'species' => 'Dog',
        'breed' => 'Poodle',
        'date_of_birth' => now()->subYears(3)->subMonths(2)->toDateString(),
        'weight' => 12.5,
    ]);

    config([
        'services.groq.key' => 'test-key',
        'services.groq.model' => 'test-model',
    ]);
    Http::fake([
        'api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Monitor symptoms and contact a veterinarian if they persist.',
                    ],
                ],
            ],
        ]),
    ]);

    $response = $this->actingAs($owner)->post(route('pet-owner.symptom-checker.submit'), [
        'pet_id' => $pet->pet_id,
        'symptoms' => ['vomiting', 'weakness'],
    ]);

    $response->assertOk()
        ->assertViewIs('pet-owner.symptom-checker')
        ->assertSee('Coco')
        ->assertSee('Species:')
        ->assertSee('Reported symptoms')
        ->assertSee('Educational pet-care guidance')
        ->assertSee('Monitor symptoms and contact a veterinarian');

    Http::assertSent(fn (Request $request) =>
        $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
        && $request['model'] === 'test-model'
        && $request['messages'][1]['content'] !== ''
    );
});

test('pet owner cannot request guidance for another owners pet', function () {
    $owner = User::factory()->create(['role' => 'Pet Owner']);
    $otherOwner = User::factory()->create(['role' => 'Pet Owner']);
    $otherPet = Pet::factory()->create(['user_id' => $otherOwner->user_id]);

    $this->actingAs($owner)
        ->post(route('pet-owner.symptom-checker.submit'), [
            'pet_id' => $otherPet->pet_id,
            'symptoms' => 'vomiting',
        ])
        ->assertNotFound();
});

test('pet owner cannot submit symptoms that are not in the dataset vocabulary', function () {
    $owner = User::factory()->create(['role' => 'Pet Owner']);
    $pet = Pet::factory()->create(['user_id' => $owner->user_id]);
    Http::fake(['127.0.0.1:8001/symptoms' => Http::response(['symptoms' => ['vomiting', 'weakness']])]);

    $this->actingAs($owner)
        ->post(route('pet-owner.symptom-checker.submit'), [
            'pet_id' => $pet->pet_id,
            'symptoms' => ['invented symptom'],
        ])
        ->assertSessionHasErrors('symptoms.0');
});