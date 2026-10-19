<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdherenceReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $medicationName,
        public string $dosage,
        public CarbonInterface $scheduledAt,
        public CarbonInterface $deadline,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Medication Reminder - '.$this->medicationName
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.adherence-reminder',
            with: [
                'recipientName' => $this->recipientName,
                'medication_name' => $this->medicationName,
                'dosage' => $this->dosage,
                'scheduled_at' => $this->scheduledAt,
                'deadline' => $this->deadline,
            ],
        );
    }
}
