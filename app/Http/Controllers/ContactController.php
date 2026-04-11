<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmission;
use App\Models\ContactMessage;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        $locale = app()->getLocale();

        return Inertia::render('Contact/Index', [
            'meta' => SeoService::forPage(
                $locale,
                'contact',
                'تواصل معي | حسان المالكي',
                'Contact | Hassan Almalki',
                'تواصل مع حسان المالكي عبر البريد الإلكتروني أو وسائل التواصل الاجتماعي.',
                'Get in touch with Hassan Almalki via email or social media.'
            ),
            'breadcrumbs' => [
                ['label' => $locale === 'ar' ? 'الرئيسية' : 'Home', 'url' => $locale === 'ar' ? '/' : '/en'],
                ['label' => $locale === 'ar' ? 'تواصل معي' : 'Contact'],
            ],
        ]);
    }

    public function send(ContactRequest $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return back()->with('success', __('messages.contact.success'));
        }

        $validated = $request->validated();
        unset($validated['website'], $validated['cf_turnstile_response']);

        $locale = app()->getLocale();

        ContactMessage::create([
            ...$validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'locale' => $locale,
        ]);

        $adminEmail = site_setting('email', 'hassan@almalki.sa');

        try {
            Mail::to($adminEmail)
                ->sendNow(new ContactFormSubmission([
                    ...$validated,
                    'ip_address' => $request->ip(),
                    'locale' => $locale,
                ]));
        } catch (\Throwable $e) {
            Log::error('Admin contact notification failed: '.$e->getMessage());
        }

        try {
            Mail::to($validated['email'])
                ->sendNow(new ContactFormConfirmation([
                    ...$validated,
                    'locale' => $locale,
                ]));
        } catch (\Throwable $e) {
            Log::error('Sender confirmation failed: '.$e->getMessage());
        }

        $this->logToGoogleSheets($validated);

        return back()->with('success', __('messages.contact.success'));
    }

    private function logToGoogleSheets(array $data): void
    {
        $webhookUrl = config('services.google_sheets.webhook_url');
        $secret = config('services.google_sheets.secret');

        if (! $webhookUrl || ! $secret) {
            return;
        }

        try {
            Http::asForm()->timeout(5)->post($webhookUrl, [
                'name' => $data['name'],
                'email' => $data['email'],
                'mobile' => $data['mobile'] ?? '',
                'message' => $data['message'],
                'secret' => $secret,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Google Sheets log failed: '.$e->getMessage());
        }
    }
}
