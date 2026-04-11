<?php

namespace App\Listeners;

use App\Mail\AdminLoginAlert;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Mail;

class SendAdminLoginAlert
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user->is_admin) {
            return;
        }

        $request = request();

        Mail::to('hassan@almlaki.sa')
            ->queue(new AdminLoginAlert([
                'user_name' => $user->name,
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => now()->format('Y-m-d H:i:s'),
                'locale' => 'ar',
            ]));
    }
}
