<?php

namespace App\Observers;

use App\Mail\NewContentPublished;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class ContentObserver
{
    public function created(Model $model): void
    {
        $typeMap = [
            \App\Models\Article::class => 'article',
            \App\Models\Workshop::class => 'workshop',
            \App\Models\Portfolio::class => 'portfolio',
        ];

        $contentType = $typeMap[get_class($model)] ?? 'content';

        Mail::to('hassan@almalki.sa')
            ->queue(new NewContentPublished($model, $contentType));

        $this->flushContentCache();
    }

    public function updated(Model $model): void
    {
        $this->flushContentCache();
    }

    public function deleted(Model $model): void
    {
        $this->flushContentCache();
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
