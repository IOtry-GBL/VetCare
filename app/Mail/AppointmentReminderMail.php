<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $petName,
        public CarbonInterface $appointmentAt,
        public string $reminderLabel,
        public string $consultationMode,
        public string $reasonForVisit,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Appointment Reminder - '.$this->petName
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointment-reminder',
            with: [
                'recipientName' => $this->recipientName,
                'petName' => $this->petName,
                'appointmentAt' => $this->appointmentAt,
                'reminderLabel' => $this->reminderLabel,
                'consultationMode' => $this->consultationMode,
                'reasonForVisit' => $this->reasonForVisit,
            ],
        );
    }
}
