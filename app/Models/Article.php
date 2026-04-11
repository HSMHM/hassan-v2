<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'title_ar', 'title_en',
        'slug_ar', 'slug_en',
        'excerpt_ar', 'excerpt_en',
        'content_ar', 'content_en',
        'cover_image', 'cover_image_en',
        'extras',
        'meta_title_ar', 'meta_title_en',
        'meta_description_ar', 'meta_description_en',
        'is_published',
        'published_at',
    ];

    public function coverImage(string $locale): ?string
    {
        return $locale === 'en' ? ($this->cover_image_en ?: $this->cover_image) : $this->cover_image;
    }

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'extras' => 'array',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function title(string $locale): string
    {
        return $locale === 'en' ? $this->title_en : $this->title_ar;
    }

    public function slug(string $locale): string
    {
        return $locale === 'en' ? $this->slug_en : $this->slug_ar;
    }

    public function safeContent(string $locale): string
    {
        return clean_html($locale === 'en' ? $this->content_en : $this->content_ar);
    }
}
