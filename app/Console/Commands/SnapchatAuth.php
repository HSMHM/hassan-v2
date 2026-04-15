<?php

namespace App\Console\Commands;

use App\Http\Controllers\SnapchatAuthController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SnapchatAuth extends Command
{
    protected $signature = 'snapchat:auth';

    protected $description = 'Print the Snapchat OAuth authorize URL. Visit it, approve, and the callback will show the tokens.';

    public function handle(): int
    {
        $clientId = config('services.snapchat.client_id');
        if (! $clientId) {
            $this->error('SNAPCHAT_CLIENT_ID is not set in .env');

            return self::FAILURE;
        }

        $state = Str::random(40);
        Cache::put(SnapchatAuthController::STATE_CACHE_KEY, $state, now()->addMinutes(10));

        $redirect = rtrim(config('app.url'), '/').'/snapchat/callback';

        $url = 'https://accounts.snapchat.com/login/oauth2/authorize?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirect,
            'scope' => 'snapchat-profile-api',
            'state' => $state,
        ]);

        $this->info('Open this URL in your browser, sign in to Snap, and approve:');
        $this->newLine();
        $this->line($url);
        $this->newLine();
        $this->comment('State expires in 10 minutes.');
        $this->comment("Redirect URI registered in Snap must match exactly: {$redirect}");

        return self::SUCCESS;
    }
}
