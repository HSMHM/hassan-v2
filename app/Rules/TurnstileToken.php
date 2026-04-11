<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileToken implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = (string) config('services.turnstile.secret_key');

        // If Turnstile isn't configured, accept silently (honeypot + throttle still guard).
        if ($secret === '') {
            return;
        }

        if (! is_string($value) || $value === '') {
            $fail(app()->getLocale() === 'ar'
                ? 'يرجى إكمال التحقق البشري.'
                : 'Please complete the human verification.');

            return;
        }

        try {
            $response = Http::asForm()->timeout(5)->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'secret' => $secret,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]
            );

            if (! $response->successful() || ! ($response->json('success') === true)) {
                Log::warning('Turnstile verification failed', [
                    'errors' => $response->json('error-codes') ?? [],
                    'ip' => request()->ip(),
                ]);
                $fail(app()->getLocale() === 'ar'
                    ? 'فشل التحقق البشري، حاول مرة أخرى.'
                    : 'Verification failed, please try again.');
            }
        } catch (\Throwable $e) {
            Log::warning('Turnstile request error: '.$e->getMessage());
            // Fail closed — if we can't reach Cloudflare, reject the submission.
            $fail(app()->getLocale() === 'ar'
                ? 'تعذّر التحقق، حاول بعد قليل.'
                : 'Verification temporarily unavailable, please try again.');
        }
    }
}
