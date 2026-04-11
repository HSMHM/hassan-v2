<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $fillable = [
        'title_ar', 'title_en',
        'slug_ar', 'slug_en',
        'description_ar', 'description_en',
        'content_ar', 'content_en',
        'features',
        'cover_image',
        'project_url',
        'category', 'category_en',
        'meta_title_ar', 'meta_title_en',
        'meta_description_ar', 'meta_description_en',
        'sort_order',
        'is_published',
    ];

    public function categoryFor(string $locale): ?string
    {
        return $locale === 'en' ? ($this->category_en ?: $this->category) : $this->category;
    }

    protected $casts = [
        'features' => 'array',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
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
