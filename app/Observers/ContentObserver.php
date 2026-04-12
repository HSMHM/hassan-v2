<?php

namespace App\Observers;

use App\Mail\NewContentPublished;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ContentObserver
{
    public function created(Model $model): void
    {
        $typeMap = [
            \App\Models\Article::class => 'article',
            \App\Models\NewsPost::class => 'news',
            \App\Models\Workshop::class => 'workshop',
            \App\Models\Portfolio::class => 'portfolio',
        ];

        $contentType = $typeMap[get_class($model)] ?? 'content';

        Mail::to('hassan@almalki.sa')
            ->queue(new NewContentPublished($model, $contentType));

        $this->optimizeCovers($model);
        $this->flushContentCache();
    }

    public function updated(Model $model): void
    {
        $this->optimizeCovers($model);
        $this->flushContentCache();
    }

    public function deleted(Model $model): void
    {
        $this->flushContentCache();
    }

    private function optimizeCovers(Model $model): void
    {
        foreach (['cover_image', 'cover_image_en'] as $attr) {
            if (! array_key_exists($attr, $model->getAttributes())) {
                continue;
            }
            if ($model->wasChanged($attr) || $model->wasRecentlyCreated) {
                $this->optimizeImage($model->{$attr});
            }
        }
    }

    private function optimizeImage(?string $path): void
    {
        if (! $path || ! str_starts_with($path, '/uploads/')) {
            return; // skip legacy /img/... paths and externally-hosted URLs
        }

        $fullPath = public_path(ltrim($path, '/'));
        if (! file_exists($fullPath) || filesize($fullPath) === 0) {
            return;
        }

        try {
            $manager = new ImageManager(new Driver);
            $image = $manager->read($fullPath);

            if ($image->width() > 1200) {
                $image->scaleDown(width: 1200);
            }

            // Re-save as JPEG at 85% quality. For PNG/WebP we overwrite the
            // original bytes but keep the extension so stored DB paths stay valid.
            $image->toJpeg(85)->save($fullPath);
        } catch (\Throwable $e) {
            Log::warning('Image optimization failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function flushContentCache(): void
    {
        foreach (['ar', 'en'] as $locale) {
            Cache::forget("home_articles_{$locale}_v2");
            Cache::forget("home_portfolios_{$locale}_v2");
            Cache::forget("home_workshops_{$locale}_v2");
        }
    }
}
