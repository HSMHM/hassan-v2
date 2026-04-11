<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactFormSubmission extends Mailable implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly array $data
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: "New Contact: {$this->data['name']}",
            replyTo: [
                new Address($this->data['email'], $this->data['name']),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
            with: [
                'senderName' => $this->data['name'],
                'senderEmail' => $this->data['email'],
                'senderMobile' => $this->data['mobile'] ?? null,
                'body' => $this->data['message'],
                'locale' => $this->data['locale'] ?? 'ar',
                'sentAt' => now()->format('Y-m-d H:i'),
                'ip' => $this->data['ip_address'] ?? 'N/A',
            ],
        );
    }
}
