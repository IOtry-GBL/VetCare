<?php

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\AppointmentScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function makeActiveAppointmentFixture(string $role): array
{
    $user = User::factory()->create([
        'role' => $role,
    ]);

    $owner = $role === 'Pet Owner'
        ? $user
        : User::factory()->create(['role' => 'Pet Owner']);

    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => 'Peanut',
        'species' => 'Dog',
        'breed' => 'Mixed Breed',
    ]);

    Appointment::factory()->create([
        'pet_id' => $pet->pet_id,
        'appointment_date' => now()->addDay()->toDateString(),
        'appointment_time' => '10:00',
        'consultation_mode' => 'In-clinic',
        'reason_for_visit' => 'Existing active visit',
        'status' => 'Pending',
    ]);

    return compact('user', 'owner', 'pet');
}

test('appointment schedule exposes quarter-hour starts within clinic hours', function () {
    $slots = AppointmentScheduleService::timeSlots();

    expect($slots)->toHaveCount(32)
        ->and($slots[0])->toBe('09:00')
        ->and($slots[1])->toBe('09:15')
        ->and($slots[31])->toBe('16:45');
});

test('emergency appointments occupy thirty minutes and block overlapping starts', function () {
    $fixture = makeActiveAppointmentFixture('Pet Owner');
    $appointment = Appointment::query()->firstOrFail();
    $appointment->update(['reason_for_visit' => 'Emergency']);

    $availableTimes = AppointmentScheduleService::availableTimesFor(
        $appointment->appointment_date->format('Y-m-d'),
        'Regular Check-up',
    );

    expect($availableTimes)->not->toContain('10:15')
        ->and($availableTimes)->toContain('10:30');

    expect(AppointmentScheduleService::availableTimesFor(
        $appointment->appointment_date->format('Y-m-d'),
        'Emergency',
    ))->not->toContain('16:45');
});

test('server accepts a regular appointment fifteen minutes after another appointment', function () {
    $fixture = makeActiveAppointmentFixture('Veterinarian');
    $newPet = Pet::factory()->create([
        'user_id' => $fixture['owner']->user_id,
    ]);
    $existingAppointment = Appointment::query()->firstOrFail();
    $date = $existingAppointment->appointment_date->format('Y-m-d');

    $this->actingAs($fixture['user'])
        ->post(route('vet.appointments.store'), [
            'pet_id' => $newPet->pet_id,
            'appointment_date' => $date,
            'appointment_time' => '10:15',
            'reason' => 'Regular Check-up',
            'status' => 'Pending',
        ])
        ->assertRedirect(route('vet.appointments'));

    expect(Appointment::count())->toBe(2);
});

test('server rejects appointments overlapping an emergency and accepts the next open slot', function () {
    $fixture = makeActiveAppointmentFixture('Veterinarian');
    $newPet = Pet::factory()->create([
        'user_id' => $fixture['owner']->user_id,
    ]);
    $existingAppointment = Appointment::query()->firstOrFail();
    $existingAppointment->update(['reason_for_visit' => 'Emergency']);
    $date = $existingAppointment->appointment_date->format('Y-m-d');

    $this->actingAs($fixture['user'])
        ->from(route('vet.appointments.create'))
        ->post(route('vet.appointments.store'), [
            'pet_id' => $newPet->pet_id,
            'appointment_date' => $date,
            'appointment_time' => '10:15',
            'reason' => 'Regular Check-up',
            'status' => 'Pending',
        ])
        ->assertRedirect(route('vet.appointments.create'))
        ->assertSessionHasErrors('appointment_time');

    $this->actingAs($fixture['user'])
        ->post(route('vet.appointments.store'), [
            'pet_id' => $newPet->pet_id,
            'appointment_date' => $date,
            'appointment_time' => '10:30',
            'reason' => 'Regular Check-up',
            'status' => 'Pending',
        ])
        ->assertRedirect(route('vet.appointments'));

    expect(Appointment::count())->toBe(2);
});

test('lead time blocks standard bookings but does not block emergencies', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 16, 9, 0, 0, 'Asia/Manila'));
    SystemSetting::setValue('appointment_lead_time_hours', 2, 'integer');

    $vet = User::factory()->create(['role' => 'Veterinarian']);
    $owner = User::factory()->create(['role' => 'Pet Owner']);
    $regularPet = Pet::factory()->create(['user_id' => $owner->user_id]);
    $emergencyPet = Pet::factory()->create(['user_id' => $owner->user_id]);

    $this->actingAs($vet)
        ->from(route('vet.appointments.create'))
        ->post(route('vet.appointments.store'), [
            'pet_id' => $regularPet->pet_id,
            'appointment_date' => '2026-05-16',
            'appointment_time' => '10:45',
            'reason' => 'Regular Check-up',
            'status' => 'Pending',
        ])
        ->assertRedirect(route('vet.appointments.create'))
        ->assertSessionHasErrors('appointment_time');

    $this->actingAs($vet)
        ->post(route('vet.appointments.store'), [
            'pet_id' => $emergencyPet->pet_id,
            'appointment_date' => '2026-05-16',
            'appointment_time' => '10:00',
            'reason' => 'Emergency',
            'status' => 'Pending',
        ])
        ->assertRedirect(route('vet.appointments'));

    expect(Appointment::count())->toBe(1);
    Carbon::setTestNow();
});

test('pet owners cannot create another appointment for a pet with an active appointment', function () {
    $fixture = makeActiveAppointmentFixture('Pet Owner');

    $response = $this->actingAs($fixture['user'])
        ->from(route('pet-owner.appointments.book'))
        ->post(route('pet-owner.appointments.store'), [
            'pet_id' => $fixture['pet']->pet_id,
            'appointment_date' => now()->addDays(2)->setTime(10, 15)->format('Y-m-d\TH:i'),
            'reason' => 'Follow-up visit',
        ]);

    $response->assertRedirect(route('pet-owner.appointments.book'))
        ->assertSessionHasErrors('pet_id');

    expect(Appointment::count())->toBe(1);
});

test('staff cannot create another appointment for a pet with an active appointment', function () {
    $fixture = makeActiveAppointmentFixture('Staff');
    $vet = User::factory()->create(['role' => 'Veterinarian']);

    $response = $this->actingAs($fixture['user'])
        ->from(route('staff.appointments.create'))
        ->post(route('staff.appointments.store'), [
            'pet_id' => $fixture['pet']->pet_id,
            'vet_id' => $vet->user_id,
            'appointment_date' => now()->addDays(2)->setTime(10, 15)->format('Y-m-d\TH:i'),
            'reason' => 'Second scheduled visit',
            'status' => 'approved',
        ]);

    $response->assertRedirect(route('staff.appointments.create'))
        ->assertSessionHasErrors('pet_id');

    expect(Appointment::count())->toBe(1);
});

test('veterinarians cannot create another appointment for a pet with an active appointment', function () {
    $fixture = makeActiveAppointmentFixture('Veterinarian');

    $response = $this->actingAs($fixture['user'])
        ->from(route('vet.appointments.create'))
        ->post(route('vet.appointments.store'), [
            'pet_id' => $fixture['pet']->pet_id,
            'appointment_date' => now()->addDays(2)->setTime(10, 15)->format('Y-m-d\TH:i'),
            'reason' => 'Second scheduled visit',
            'status' => 'approved',
        ]);

    $response->assertRedirect(route('vet.appointments.create'))
        ->assertSessionHasErrors('pet_id');

    expect(Appointment::count())->toBe(1);
});

test('veterinarians can create appointment when previous active-status appointment is already stale', function () {
    $user = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $owner = User::factory()->create(['role' => 'Pet Owner']);

    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => 'Peanut',
        'species' => 'Dog',
        'breed' => 'Mixed Breed',
    ]);

    Appointment::factory()->create([
        'pet_id' => $pet->pet_id,
        'appointment_date' => now()->subDay()->toDateString(),
        'appointment_time' => '10:00',
        'consultation_mode' => 'In-clinic',
        'reason_for_visit' => 'Old unfinished visit',
        'status' => 'Pending',
    ]);

    $response = $this->actingAs($user)
        ->from(route('vet.appointments.create'))
        ->post(route('vet.appointments.store'), [
            'pet_id' => $pet->pet_id,
            'appointment_date' => now()->addDays(2)->setTime(10, 15)->format('Y-m-d\TH:i'),
            'reason' => 'New scheduled visit',
            'status' => 'approved',
        ]);

    $response->assertRedirect(route('vet.appointments'));

    expect(Appointment::count())->toBe(2);
});

test('veterinarians can create appointment when previous active-status appointment time already passed today', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 16, 14, 0, 0, 'Asia/Manila'));

    $user = User::factory()->create([
        'role' => 'Veterinarian',
    ]);

    $owner = User::factory()->create(['role' => 'Pet Owner']);

    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => 'Peanut',
        'species' => 'Dog',
        'breed' => 'Mixed Breed',
    ]);

    Appointment::factory()->create([
        'pet_id' => $pet->pet_id,
        'appointment_date' => '2026-05-16',
        'appointment_time' => '10:00',
        'consultation_mode' => 'In-clinic',
        'reason_for_visit' => 'Earlier missed visit',
        'status' => 'Pending',
    ]);

    $response = $this->actingAs($user)
        ->from(route('vet.appointments.create'))
        ->post(route('vet.appointments.store'), [
            'pet_id' => $pet->pet_id,
            'appointment_date' => '2026-05-18T09:00',
            'reason' => 'New scheduled visit',
            'status' => 'approved',
        ]);

    $response->assertRedirect(route('vet.appointments'));

    expect(Appointment::count())->toBe(2);

    Carbon::setTestNow();
});
