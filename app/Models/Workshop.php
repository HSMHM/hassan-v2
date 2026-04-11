<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    protected $fillable = [
        'title_ar', 'title_en',
        'slug_ar', 'slug_en',
        'description_ar', 'description_en',
        'content_ar', 'content_en',
        'cover_image', 'cover_image_en',
        'event_date',
        'location_ar', 'location_en',
        'platform', 'platform_en',
        'extras',
        'video_url',
        'meta_title_ar', 'meta_title_en',
        'meta_description_ar', 'meta_description_en',
        'is_published',
    ];

    public function coverImage(string $locale): ?string
    {
        return $locale === 'en' ? ($this->cover_image_en ?: $this->cover_image) : $this->cover_image;
    }

    public function platformFor(string $locale): ?string
    {
        return $locale === 'en' ? ($this->platform_en ?: $this->platform) : $this->platform;
    }

    protected $casts = [
        'event_date' => 'date',
        'is_published' => 'boolean',
        'extras' => 'array',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function title(string $locale): string
    {
        return $locale === 'en' ? $this->title_en : $this->title_ar;
    }

    public function slug(string $locale): string
    {
        return $locale === 'en' ? $this->slug_en : $this->slug_ar;
    }
}
