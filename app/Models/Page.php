<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title_ar', 'title_en',
        'slug',
        'content_ar', 'content_en',
        'meta_title_ar', 'meta_title_en',
        'meta_description_ar', 'meta_description_en',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function title(string $locale): string
    {
        return $locale === 'en' ? ($this->title_en ?: $this->title_ar) : $this->title_ar;
    }

    public function content(string $locale): ?string
    {
        return $locale === 'en' ? ($this->content_en ?: $this->content_ar) : $this->content_ar;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
