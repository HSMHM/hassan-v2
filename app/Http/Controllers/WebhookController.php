<?php

namespace App\Http\Controllers;

use App\Jobs\PublishNewsJob;
use App\Jobs\RegenerateContentJob;
use App\Models\NewsPost;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function whatsapp(Request $request): JsonResponse
    {
        // Verify webhook secret if configured; skip check if not set yet.
        $expectedSecret = (string) config('services.whapi.webhook_secret');
        if ($expectedSecret !== '' && ! hash_equals($expectedSecret, (string) $request->header('X-Webhook-Secret'))) {
            abort(401);
        }

        $messages = $request->input('messages', []);

        foreach ($messages as $message) {
            // Skip messages sent BY the system (from_me = true) to prevent loops
            if (! empty($message['from_me'])) {
                continue;
            }

            $from = $message['from'] ?? '';
            $text = trim($message['text']['body'] ?? '');

            if (! str_contains($from, (string) config('services.whapi.owner_phone'))) {
                continue;
            }

            $this->handleCommand($text);
        }

        return response()->json(['ok' => true]);
    }

    private function handleCommand(string $text): void
    {
        $lower = mb_strtolower(trim($text));
        $wa = app(WhatsAppService::class);

        $post = NewsPost::where('status', 'pending')->latest('sent_to_whatsapp_at')->first();

        if (! $post && preg_match('/#(\d+)/', $text, $m)) {
            $post = NewsPost::find($m[1]);
        }

        if (! $post) {
            // Silently ignore — don't waste Whapi tokens replying to every random message
            return;
        }

        if (in_array($lower, ['publish', 'نشر'], true)) {
            $post->update(['status' => 'approved', 'approved_at' => now()]);
            $wa->sendMessage('⏳ جاري النشر على جميع المنصات...');
            PublishNewsJob::dispatch($post->id);

            return;
        }

        if (str_starts_with($lower, 'publish ')) {
            $platformText = str_replace('publish ', '', $lower);
            $platforms = $this->parsePlatforms($platformText);
            if ($platforms) {
                $post->update(['status' => 'approved', 'approved_at' => now()]);
                $wa->sendMessage('⏳ جاري النشر على: '.implode(', ', $platforms));
                PublishNewsJob::dispatch($post->id, $platforms);

                return;
            }
        }

        if (in_array($lower, ['skip', 'تجاوز'], true)) {
            $post->update(['status' => 'skipped']);
            $wa->sendMessage("⏭️ تم تجاوز: {$post->title_ar}");

            return;
        }

        if (preg_match('/^(edit|تعديل):\s*(.+)/iu', $text, $m)) {
            $wa->sendMessage('✏️ جاري التعديل...');
            RegenerateContentJob::dispatch($post->id, $m[2]);
        }
    }

    private function parsePlatforms(string $text): array
    {
        $map = [
            'x' => 'twitter', 'twitter' => 'twitter', 'تويتر' => 'twitter',
            'ig' => 'instagram', 'instagram' => 'instagram', 'انستقرام' => 'instagram',
            'li' => 'linkedin', 'linkedin' => 'linkedin', 'لينكدان' => 'linkedin',
            'snap' => 'snapchat', 'snapchat' => 'snapchat', 'سناب' => 'snapchat',
            'wa' => 'whatsapp', 'whatsapp' => 'whatsapp', 'واتساب' => 'whatsapp',
        ];

        $result = [];
        foreach ($map as $kw => $platform) {
            if (str_contains($text, $kw)) {
                $result[] = $platform;
            }
        }

        return array_values(array_unique($result));
    }
}
