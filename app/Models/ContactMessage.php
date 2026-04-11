<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'message',
        'ip_address',
        'user_agent',
        'locale',
        'is_read',
        'is_spam',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_spam' => 'boolean',
    ];

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeNotSpam(Builder $query): Builder
    {
        return $query->where('is_spam', false);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }
}
