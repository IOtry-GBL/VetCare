<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdherenceNotificationTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $medicationName,
        public string $dosage,
        public string $confirmationUrl,
        public CarbonInterface $expiresAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Adherence Notification Test');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.adherence-test',
            with: [
                'recipientName' => $this->recipientName,
                'medicationName' => $this->medicationName,
                'dosage' => $this->dosage,
                'confirmationUrl' => $this->confirmationUrl,
                'expiresAt' => $this->expiresAt,
            ],
        );
    }
}
