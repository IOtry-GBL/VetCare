<?php

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('appointment reminders queue email alongside in-app notifications without duplicates', function () {
    Carbon::setTestNow(Carbon::create(2026, 10, 19, 8, 0, 0, 'Asia/Manila'));
    Mail::fake();

    $owner = User::factory()->create([
        'role' => 'Pet Owner',
        'full_name' => 'Taylor Client',
    ]);
    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => 'Milo',
    ]);

    foreach ([
        ['date' => '2026-10-20', 'time' => '10:00:00', 'label' => 'one day'],
        ['date' => '2026-10-19', 'time' => '09:00:00', 'label' => 'one hour'],
        ['date' => '2026-10-19', 'time' => '08:00:00', 'label' => 'now'],
    ] as $appointmentData) {
        Appointment::create([
            'pet_id' => $pet->pet_id,
            'appointment_date' => $appointmentData['date'],
            'appointment_time' => $appointmentData['time'],
            'consultation_mode' => 'In-clinic',
            'reason_for_visit' => 'Annual checkup',
            'status' => 'Approved',
        ]);
    }

    $this->artisan('appointments:send-reminders')->assertExitCode(0);
    $this->artisan('appointments:send-reminders')->assertExitCode(0);

    expect(Notification::query()->count())->toBe(3);
    Mail::assertQueued(AppointmentReminderMail::class, 3);
    Mail::assertQueued(AppointmentReminderMail::class, function (AppointmentReminderMail $mail) use ($owner): bool {
        $rendered = $mail->render();

        return $mail->hasTo($owner->email)
            && $mail->recipientName === 'Taylor Client'
            && $mail->petName === 'Milo'
            && $mail->consultationMode === 'In-clinic'
            && $mail->reasonForVisit === 'Annual checkup'
            && str_contains($rendered, 'Milo')
            && str_contains($rendered, 'Annual checkup')
            && $mail->connection === 'database'
            && $mail->queue === 'emails';
    });
    foreach (['one day', 'one hour', 'now'] as $reminderLabel) {
        Mail::assertQueued(
            AppointmentReminderMail::class,
            fn (AppointmentReminderMail $mail): bool => $mail->reminderLabel === $reminderLabel
        );
    }

    Carbon::setTestNow();
});
