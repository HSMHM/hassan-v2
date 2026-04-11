<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactFormConfirmation extends Mailable implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly array $data
    ) {}

    public function envelope(): Envelope
    {
        $locale = $this->data['locale'] ?? 'ar';

        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                $locale === 'ar' ? 'حسان المالكي' : 'Hassan Almalki'
            ),
            subject: $locale === 'ar'
                ? 'شكراً لتواصلك — حسان المالكي'
                : 'Thank you for reaching out — Hassan Almalki',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-confirmation',
            with: [
                'senderName' => $this->data['name'],
                'body' => $this->data['message'],
                'locale' => $this->data['locale'] ?? 'ar',
            ],
        );
    }
}
