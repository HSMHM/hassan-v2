<?php

namespace App\Console\Commands;

use App\Services\InstagramService;
use Illuminate\Console\Command;

class InstagramStoreToken extends Command
{
    protected $signature = 'instagram:store-token
                            {token : Long-lived token from Meta App Dashboard}
                            {--expires-in=5184000 : Expiry in seconds, default 60 days}';

    protected $description = 'Store the Instagram access token in the database for publishing and refresh jobs';

    public function handle(InstagramService $instagram): int
    {
        $token = trim((string) $this->argument('token'));
        $expiresIn = max(0, (int) $this->option('expires-in'));

        if ($token === '') {
            $this->error('Token is required.');

            return self::FAILURE;
        }

        $instagram->storeAccessToken($token, $expiresIn);

        $this->info('Instagram token stored successfully.');
        $this->line('Source: database');
        $this->line('Expires at: '.now()->addSeconds($expiresIn)->toDateTimeString());

        return self::SUCCESS;
    }
}