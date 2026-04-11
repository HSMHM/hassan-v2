<?php

namespace App\Observers;

use App\Mail\NewContentPublished;
use Illuminate\Database\Eloquent\Model;
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

        Mail::to('hassan@almlaki.sa')
            ->queue(new NewContentPublished($model, $contentType));
    }
}
