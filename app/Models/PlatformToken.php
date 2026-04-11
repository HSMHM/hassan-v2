<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformToken extends Model
{
    protected $fillable = [
        'platform',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public static function tokenFor(string $platform): ?string
    {
        return static::where('platform', $platform)->value('access_token');
    }
}
