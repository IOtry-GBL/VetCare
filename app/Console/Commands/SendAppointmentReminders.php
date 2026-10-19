<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Send appointment reminders at scheduled times (1 day before, 1 hour before, exact time)';

    public function handle()
    {
        $now = Carbon::now('Asia/Manila');

        // 1 DAY BEFORE REMINDERS
        $oneDayBefore = $now->copy()->addDay()->startOfDay();

        $upcomingAppointments = Appointment::whereDate('appointment_date', $oneDayBefore->toDateString())
            ->where('status', 'Approved')
            ->with('pet.owner')
            ->get();

        foreach ($upcomingAppointments as $appointment) {
            $this->createReminder($appointment, 'appointment_reminder_1day', 'one day');
        }

        // 1 HOUR BEFORE REMINDERS
        $oneHourBefore = $now->copy()->addHour();
        $appointmentsOneHourBefore = Appointment::whereDate('appointment_date', $now->toDateString())
            ->where('status', 'Approved')
            ->with('pet.owner')
            ->get()
            ->filter(function ($appt) use ($oneHourBefore) {
                $appointmentTime = $appt->appointment_date;

                return $appointmentTime->diffInMinutes($oneHourBefore) <= 5 &&
                       $appointmentTime->diffInMinutes($oneHourBefore) >= -5;
            });

        foreach ($appointmentsOneHourBefore as $appointment) {
            $this->createReminder($appointment, 'appointment_reminder_1hour', 'one hour');
        }

        // EXACT TIME REMINDERS
        $appointmentsNow = Appointment::whereDate('appointment_date', $now->toDateString())
            ->where('status', 'Approved')
            ->with('pet.owner')
            ->get()
            ->filter(function ($appt) use ($now) {
                $appointmentTime = $appt->appointment_date;

                return $appointmentTime->diffInMinutes($now) <= 2 &&
                       $appointmentTime->diffInMinutes($now) >= -2;
            });

        foreach ($appointmentsNow as $appointment) {
            $this->createReminder($appointment, 'appointment_time_reminder', 'now');
        }

        $this->info('Appointment reminders sent successfully.');
    }

    private function createReminder(Appointment $appointment, string $reminderType, string $reminderLabel): void
    {
        $owner = $appointment->pet?->owner;
        if (! $owner) {
            return;
        }

        DB::transaction(function () use ($appointment, $owner, $reminderType, $reminderLabel): void {
            $existing = Notification::query()
                ->where('user_id', $owner->user_id)
                ->where('reminder_type', $reminderType)
                ->where('related_reference', $appointment->appointment_id)
                ->where('sent_status', 'sent')
                ->exists();

            if ($existing) {
                return;
            }

            $appointmentAt = $appointment->appointment_date;

            Notification::create([
                'user_id' => $owner->user_id,
                'reminder_type' => $reminderType,
                'reminder_channel' => 'in-app',
                'scheduled_at' => now(),
                'sent_status' => 'sent',
                'related_reference' => $appointment->appointment_id,
            ]);

            if ($owner->email) {
                Mail::to($owner->email)->queue(
                    (new AppointmentReminderMail(
                        recipientName: $owner->full_name,
                        petName: $appointment->pet->pet_name,
                        appointmentAt: $appointmentAt,
                        reminderLabel: $reminderLabel,
                        consultationMode: $appointment->consultation_mode,
                        reasonForVisit: $appointment->reason_for_visit,
                    ))
                        ->onConnection('database')
                        ->onQueue('emails')
                );
            }
        });
    }
}
