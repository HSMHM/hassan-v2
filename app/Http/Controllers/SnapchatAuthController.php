<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SnapchatAuthController extends Controller
{
    public const STATE_CACHE_KEY = 'snapchat:oauth:state';

    /**
     * Callback for Snap's OAuth flow. Snap redirects here with ?code=... & ?state=...
     * We exchange the code for access + refresh tokens, then show Hassan the values
     * to paste into .env. The page is gated by a one-time state that only
     * `php artisan snapchat:auth` can produce, so casual visitors see a 403.
     */
    public function callback(Request $request)
    {
        $code = (string) $request->query('code', '');
        $state = (string) $request->query('state', '');
        $error = (string) $request->query('error', '');

        if ($error) {
            abort(400, "Snap OAuth error: {$error}");
        }

        $expectedState = Cache::pull(self::STATE_CACHE_KEY);
        if (! $expectedState || ! hash_equals($expectedState, $state)) {
            abort(403, 'State mismatch — run `php artisan snapchat:auth` first and use the URL it prints.');
        }

        if (! $code) {
            abort(400, 'Missing authorization code.');
        }

        $response = Http::asForm()->post('https://accounts.snapchat.com/login/oauth2/access_token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.snapchat.client_id'),
            'client_secret' => config('services.snapchat.client_secret'),
            'code' => $code,
            'redirect_uri' => rtrim(config('app.url'), '/').'/snapchat/callback',
        ]);

        if (! $response->successful()) {
            Log::error('Snapchat token exchange failed', ['body' => $response->body()]);
            abort(500, 'Token exchange failed: '.$response->body());
        }

        $data = $response->json();
        $access = $data['access_token'] ?? '';
        $refresh = $data['refresh_token'] ?? '';
        $expiresIn = $data['expires_in'] ?? 3600;

        return response(view('snapchat.callback', [
            'access' => $access,
            'refresh' => $refresh,
            'expiresIn' => $expiresIn,
        ]))->header('X-Robots-Tag', 'noindex');
    }
}
