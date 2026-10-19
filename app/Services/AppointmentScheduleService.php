<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class AppointmentScheduleService
{
    private const OPENING_MINUTE = 9 * 60;
    private const CLOSING_MINUTE = 17 * 60;
    private const SLOT_INTERVAL_MINUTES = 15;
    private const STANDARD_DURATION_MINUTES = 15;
    private const EMERGENCY_DURATION_MINUTES = 30;

    public static function timeSlots(): array
    {
        $slots = [];

        for ($minute = self::OPENING_MINUTE; $minute < self::CLOSING_MINUTE; $minute += self::SLOT_INTERVAL_MINUTES) {
            $slots[] = sprintf('%02d:%02d', intdiv($minute, 60), $minute % 60);
        }

        return $slots;
    }

    public static function durationMinutes(?string $reason): int
    {
        return self::isEmergency($reason)
            ? self::EMERGENCY_DURATION_MINUTES
            : self::STANDARD_DURATION_MINUTES;
    }

    public static function isEmergency(?string $reason): bool
    {
        return str_contains(strtolower($reason ?? ''), 'emergency');
    }

    public static function leadTimeHours(): int
    {
        $default = config('super-admin.settings.appointment_lead_time_hours', 2);

        return max(0, (int) SystemSetting::getValue('appointment_lead_time_hours', $default));
    }

    public static function calendarBookings(): array
    {
        return Appointment::query()
            ->activeEntries()
            ->with(['pet.owner'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    'appointment_id' => $appointment->appointment_id,
                    'date' => $appointment->appointment_date->format('Y-m-d'),
                    'time' => $appointment->appointment_time->format('H:i'),
                    'reason' => $appointment->reason_for_visit,
                    'duration_minutes' => self::durationMinutes($appointment->reason_for_visit),
                    'status' => $appointment->status,
                    'client_name' => $appointment->pet?->owner?->name ?? 'Unknown client',
                    'pet_name' => $appointment->pet?->name ?? 'Unknown pet',
                ];
            })
            ->values()
            ->all();
    }

    public static function bookedAppointments(?int $ignoreAppointmentId = null): array
    {
        return Appointment::query()
            ->activeEntries()
            ->when($ignoreAppointmentId, fn (Builder $query) => $query->where('appointment_id', '!=', $ignoreAppointmentId))
            ->get()
            ->groupBy(fn (Appointment $appointment) => $appointment->appointment_date->format('Y-m-d'))
            ->map(fn ($appointments) => $appointments
                ->map(fn (Appointment $appointment) => $appointment->appointment_time->format('H:i'))
                ->values()
                ->all()
            )
            ->all();
    }

    public static function normalizeDateTimeInput(Request $request): void
    {
        $appointmentDateTime = $request->input('appointment_date');

        if ($appointmentDateTime && str_contains($appointmentDateTime, 'T') && !$request->filled('appointment_time')) {
            [$date, $time] = explode('T', $appointmentDateTime, 2);
            $request->merge([
                'appointment_date' => $date,
                'appointment_time' => substr($time, 0, 5),
            ]);
        }
    }

    public static function availableTimesFor(string $date, ?string $reason = null, ?int $ignoreAppointmentId = null): array
    {
        $duration = self::durationMinutes($reason);
        $appointments = Appointment::query()
            ->activeEntries()
            ->whereDate('appointment_date', $date)
            ->when($ignoreAppointmentId, fn (Builder $query) => $query->where('appointment_id', '!=', $ignoreAppointmentId))
            ->get(['appointment_time', 'reason_for_visit']);

        return array_values(array_filter(self::timeSlots(), function (string $time) use ($date, $duration, $reason, $appointments) {
            $startMinute = self::minutesFromTime($time);

            if ($startMinute + $duration > self::CLOSING_MINUTE
                || self::hasOverlappingAppointment($startMinute, $duration, $appointments)) {
                return false;
            }

            if (!self::isEmergency($reason)) {
                try {
                    $scheduledAt = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}", 'Asia/Manila');
                } catch (\Throwable) {
                    return false;
                }

                if ($scheduledAt->lessThan(now('Asia/Manila')->addHours(self::leadTimeHours()))) {
                    return false;
                }
            }

            return true;
        }));
    }

    public static function validateSlot(Validator $validator, Request $request, ?int $ignoreAppointmentId = null): void
    {
        $date = $request->input('appointment_date');
        $time = $request->input('appointment_time');

        if (!$date || !$time) {
            return;
        }

        try {
            $scheduledAt = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}", 'Asia/Manila');
        } catch (\Throwable) {
            return;
        }

        if ($scheduledAt->lessThan(now('Asia/Manila'))) {
            $validator->errors()->add('appointment_date', 'Choose a future appointment date and time.');
            return;
        }

        $reason = $request->input('reason') ?? $request->input('reason_for_visit');
        if ($reason === null && $ignoreAppointmentId) {
            $reason = Appointment::query()->whereKey($ignoreAppointmentId)->value('reason_for_visit');
        }

        $leadTimeHours = self::leadTimeHours();
        if (!self::isEmergency($reason)
            && $scheduledAt->lessThan(now('Asia/Manila')->addHours($leadTimeHours))) {
            $validator->errors()->add(
                'appointment_time',
                "Appointments must be booked at least {$leadTimeHours} hours in advance."
            );
            return;
        }

        $duration = self::durationMinutes($reason);
        $startMinute = self::minutesFromTime($time);
        if ($startMinute < self::OPENING_MINUTE
            || ($startMinute - self::OPENING_MINUTE) % self::SLOT_INTERVAL_MINUTES !== 0
            || $startMinute + $duration > self::CLOSING_MINUTE) {
            $validator->errors()->add('appointment_time', 'Choose a time in 15-minute increments between 9:00 AM and 5:00 PM.');
            return;
        }

        $appointments = Appointment::query()
            ->activeEntries()
            ->whereDate('appointment_date', $date)
            ->when($ignoreAppointmentId, fn (Builder $query) => $query->where('appointment_id', '!=', $ignoreAppointmentId))
            ->get(['appointment_time', 'reason_for_visit']);

        if (self::hasOverlappingAppointment($startMinute, $duration, $appointments)) {
            $validator->errors()->add('appointment_time', 'That appointment slot is already taken.');
        }
    }

    private static function minutesFromTime(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', substr($time, 0, 5)));

        return ($hour * 60) + $minute;
    }

    private static function hasOverlappingAppointment(int $startMinute, int $duration, iterable $appointments): bool
    {
        foreach ($appointments as $appointment) {
            $existingStart = self::minutesFromTime($appointment->appointment_time->format('H:i'));
            $existingEnd = $existingStart + self::durationMinutes($appointment->reason_for_visit);

            if ($startMinute < $existingEnd && $existingStart < $startMinute + $duration) {
                return true;
            }
        }

        return false;
    }
}
