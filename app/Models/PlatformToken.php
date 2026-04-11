<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PlatformToken extends Model
{
    protected $fillable = [
        'platform',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'expires_at' => 'datetime',
    ];

    public static function tokenFor(string $platform): ?string
    {
        $token = static::where('platform', $platform)->first();
        if (! $token) {
            return null;
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            Log::warning('Platform token expired', ['platform' => $platform]);

            return null;
        }

        return $token->access_token;
    }

    public static function saveToken(
        string $platform,
        string $accessToken,
        ?string $refreshToken = null,
        ?Carbon $expiresAt = null
    ): static {
        return static::updateOrCreate(
            ['platform' => $platform],
            [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_at' => $expiresAt,
            ]
        );
    }
}
