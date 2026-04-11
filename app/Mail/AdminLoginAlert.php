<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AdminLoginAlert extends Mailable implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly array $data
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('app.name')
            ),
            subject: '🔐 Security Alert — Admin Login',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-login',
            with: [
                'userName' => $this->data['user_name'],
                'userEmail' => $this->data['user_email'],
                'ipAddress' => $this->data['ip_address'],
                'userAgent' => $this->data['user_agent'],
                'loginAt' => $this->data['login_at'],
                'locale' => $this->data['locale'] ?? 'ar',
            ],
        );
    }
}
