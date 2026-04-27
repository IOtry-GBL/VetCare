<?php

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function makeStaffNotificationAppointmentFixture(array $overrides = []): array
{
    $owner = User::factory()->create([
        'role' => 'Pet Owner',
        'full_name' => $overrides['owner_name'] ?? 'Jamie Owner',
    ]);

    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => $overrides['pet_name'] ?? 'Milo',
        'species' => $overrides['species'] ?? 'Dog',
    ]);

    $appointment = Appointment::factory()->create([
        'pet_id' => $pet->pet_id,
        'appointment_date' => $overrides['appointment_date'] ?? '2026-04-21',
        'appointment_time' => $overrides['appointment_time'] ?? '09:00',
        'consultation_mode' => $overrides['consultation_mode'] ?? 'In-clinic',
        'status' => $overrides['status'] ?? 'Pending',
    ]);

    if (isset($overrides['created_at']) || isset($overrides['updated_at'])) {
        $appointment->forceFill([
            'created_at' => $overrides['created_at'] ?? $appointment->created_at,
            'updated_at' => $overrides['updated_at'] ?? $appointment->updated_at,
        ])->saveQuietly();
    }

    return compact('owner', 'pet', 'appointment');
}

test('staff notifications page shows live appointment activity sections from real appointment data', function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 20, 4, 0, 0, 'UTC'));

    $staff = User::factory()->create([
        'role' => 'Staff',
    ]);

    $newEntry = makeStaffNotificationAppointmentFixture([
        'pet_name' => 'Nori',
        'created_at' => Carbon::create(2026, 4, 20, 3, 30, 0, 'UTC'),
        'updated_at' => Carbon::create(2026, 4, 20, 3, 30, 0, 'UTC'),
        'status' => 'Pending',
    ]);

    $cancelledToday = makeStaffNotificationAppointmentFixture([
        'pet_name' => 'Maple',
        'status' => 'Cancelled',
        'created_at' => Carbon::create(2026, 4, 17, 2, 0, 0, 'UTC'),
        'updated_at' => Carbon::create(2026, 4, 20, 1, 15, 0, 'UTC'),
    ]);

    $didNotArrive = makeStaffNotificationAppointmentFixture([
        'pet_name' => 'Atlas',
        'status' => 'Missed',
        'created_at' => Carbon::create(2026, 4, 17, 2, 0, 0, 'UTC'),
        'updated_at' => Carbon::create(2026, 4, 20, 2, 45, 0, 'UTC'),
    ]);

    $staleAppointment = makeStaffNotificationAppointmentFixture([
        'pet_name' => 'Oldie',
        'status' => 'Cancelled',
        'created_at' => Carbon::create(2026, 4, 15, 1, 0, 0, 'UTC'),
        'updated_at' => Carbon::create(2026, 4, 16, 1, 0, 0, 'UTC'),
    ]);

    $response = $this->actingAs($staff)->get(route('staff.notifications'));

    $response->assertOk()
        ->assertViewIs('staff.notifications')
        ->assertSee('Live Staff Notifications')
        ->assertSee('New Appointment Entries')
        ->assertSee('Cancelled Today')
        ->assertSee('Did Not Arrive Recently')
        ->assertSee($newEntry['pet']->name)
        ->assertSee($cancelledToday['pet']->name)
        ->assertSee($didNotArrive['pet']->name)
        ->assertDontSee($staleAppointment['pet']->name)
        ->assertViewHas('newAppointmentEntries', fn ($appointments) => $appointments->pluck('appointment_id')->contains($newEntry['appointment']->appointment_id))
        ->assertViewHas('cancelledTodayAppointments', fn ($appointments) => $appointments->pluck('appointment_id')->contains($cancelledToday['appointment']->appointment_id))
        ->assertViewHas('recentDidNotArriveAppointments', fn ($appointments) => $appointments->pluck('appointment_id')->contains($didNotArrive['appointment']->appointment_id));

    Carbon::setTestNow();
});

test('staff notifications feed endpoint returns refreshable html fragments', function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 20, 4, 0, 0, 'UTC'));

    $staff = User::factory()->create([
        'role' => 'Staff',
    ]);

    makeStaffNotificationAppointmentFixture([
        'pet_name' => 'Pebble',
        'created_at' => Carbon::create(2026, 4, 20, 3, 45, 0, 'UTC'),
        'updated_at' => Carbon::create(2026, 4, 20, 3, 45, 0, 'UTC'),
        'status' => 'Pending',
    ]);

    $response = $this->actingAs($staff)
        ->getJson(route('staff.notifications.feed'));

    $response->assertOk()
        ->assertJsonStructure([
            'summary_html',
            'feed_html',
            'synced_at',
        ]);

    expect($response->json('summary_html'))->toContain('New Entries');
    expect($response->json('feed_html'))->toContain('Pebble');
    expect($response->json('feed_html'))->toContain('Open Appointment');

    Carbon::setTestNow();
});
