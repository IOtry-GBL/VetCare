<?php

namespace App\Console\Commands;

use App\Events\AppointmentMissed;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateMissedAppointments extends Command
{
    protected $signature = 'appointments:update-missed';
    protected $description = 'Update appointments to Missed if current time has passed and attendance is false';

    public function handle()
    {
        $now = Carbon::now();

        // Find appointments that should be marked as missed
        $missedAppointments = Appointment::where('status', 'Approved')
            ->get()
            ->filter(function ($appointment) use ($now) {
                $date = substr((string) $appointment->getRawOriginal('appointment_date'), 0, 10);
                $time = substr((string) $appointment->getRawOriginal('appointment_time'), 0, 5);
                $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
                
                // If current time is past appointment time and attendance is false/null
                return $now->greaterThan($appointmentDateTime) && 
                       (!isset($appointment->attendance) || $appointment->attendance === false);
            });

        foreach ($missedAppointments as $appointment) {
            $appointment->update(['status' => 'Missed']);
            
            // Dispatch event to send notification
            event(new AppointmentMissed($appointment));
        }

        $this->info("Updated {$missedAppointments->count()} appointments to Missed status.");
    }
}
