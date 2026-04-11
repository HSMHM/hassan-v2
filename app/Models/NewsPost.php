<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NewsPost extends Model
{
    protected $fillable = [
        'source_url', 'source_title', 'source_type',
        'title_ar', 'title_en', 'slug_ar', 'slug_en',
        'excerpt_ar', 'excerpt_en', 'content_ar', 'content_en',
        'social_post_ar', 'social_post_en',
        'meta_title_ar', 'meta_title_en',
        'meta_description_ar', 'meta_description_en',
        'cover_image', 'og_image', 'references',
        'status', 'platform_status',
        'sent_to_whatsapp_at', 'approved_at', 'published_at',
    ];

    protected $casts = [
        'references' => 'array',
        'platform_status' => 'array',
        'sent_to_whatsapp_at' => 'datetime',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function getArticleUrl(string $locale = 'ar'): string
    {
        $slug = $locale === 'en' ? $this->slug_en : $this->slug_ar;
        $prefix = $locale === 'en' ? '/en' : '';

        return rtrim(config('app.url'), '/').$prefix.'/articles/'.$slug;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
