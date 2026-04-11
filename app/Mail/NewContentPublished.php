<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewContentPublished extends Mailable implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly Model $model,
        public readonly string $contentType,
    ) {}

    public function envelope(): Envelope
    {
        $title = $this->model->title_ar ?? $this->model->title ?? '';

        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('app.name')
            ),
            subject: "📝 New {$this->contentType}: {$title}",
        );
    }

    public function content(): Content
    {
        $model = $this->model;
        $locale = 'ar';

        $title = $model->title_ar ?? $model->title ?? '';
        $excerpt = $model->excerpt_ar ?? $model->description_ar ?? '';
        $cover = $model->cover_image ?? null;

        $resourceMap = [
            'article' => 'articles',
            'workshop' => 'workshops',
            'portfolio' => 'portfolios',
        ];
        $resourceSlug = $resourceMap[$this->contentType] ?? $this->contentType;
        $adminUrl = config('app.url') . "/admin/{$resourceSlug}/{$model->id}/edit";

        if ($cover && ! str_starts_with($cover, 'http')) {
            $cover = config('app.url') . $cover;
        }

        return new Content(
            view: 'emails.new-content',
            with: [
                'contentTitle' => $title,
                'contentExcerpt' => $excerpt,
                'contentType' => $this->contentType,
                'coverImage' => $cover,
                'createdAt' => $model->created_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
                'adminUrl' => $adminUrl,
                'locale' => $locale,
            ],
        );
    }
}
