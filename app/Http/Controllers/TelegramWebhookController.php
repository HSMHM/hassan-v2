<?php

namespace App\Http\Controllers;

use App\Jobs\DiscoverNewsJob;
use App\Jobs\GeneratePostImagesJob;
use App\Jobs\PublishNewsJob;
use App\Jobs\RegenerateContentJob;
use App\Models\NewsPost;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $expectedSecret = (string) config('services.telegram.webhook_secret');
        if ($expectedSecret !== '' && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $expectedSecret) {
            abort(401);
        }

        $update = $request->all();

        if (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);

            return response()->json(['ok' => true]);
        }

        if (isset($update['message']['text'])) {
            $chatId = (string) ($update['message']['chat']['id'] ?? '');
            $text = trim($update['message']['text']);

            if ($chatId !== (string) config('services.telegram.chat_id')) {
                return response()->json(['ok' => true]);
            }

            $this->handleMessage($text);
        }

        return response()->json(['ok' => true]);
    }

    private function handleMessage(string $text): void
    {
        $lower = mb_strtolower(trim($text));
        $telegram = app(TelegramService::class);

        // === "خبر جديد" or "news" — Discover new Claude news ON DEMAND ===
        if (in_array($lower, ['خبر', 'خبر جديد', 'news', 'ابحث', 'search', '/news'], true)
            || str_starts_with($lower, '/news')) {

            $pending = NewsPost::where('status', 'pending')->first();
            if ($pending) {
                $telegram->sendNewsForApproval($pending);

                return;
            }

            $telegram->sendMessage('🔍 جاري البحث عن آخر أخبار Claude...');
            DiscoverNewsJob::dispatch();

            return;
        }

        // === "publish" as text (fallback) ===
        if (in_array($lower, ['publish', 'نشر'], true)) {
            $post = NewsPost::where('status', 'pending')->latest('sent_to_whatsapp_at')->first();
            if ($post) {
                $post->update(['status' => 'approved', 'approved_at' => now()]);
                $telegram->sendMessage('⏳ جاري النشر على جميع المنصات...');
                PublishNewsJob::dispatch($post->id);
            } else {
                $telegram->sendMessage('⚠️ لا يوجد خبر بانتظار الموافقة.');
            }

            return;
        }

        // === "edit: instructions" ===
        if (preg_match('/^(edit|تعديل):\s*(.+)/iu', $text, $m)) {
            $post = NewsPost::where('status', 'pending')->latest('sent_to_whatsapp_at')->first();
            if ($post) {
                $telegram->sendMessage('✏️ جاري التعديل...');
                RegenerateContentJob::dispatchSync($post->id, $m[2]);
            } else {
                $telegram->sendMessage('⚠️ لا يوجد خبر للتعديل.');
            }

            return;
        }

        // === "skip" ===
        if (in_array($lower, ['skip', 'تجاوز'], true)) {
            $post = NewsPost::where('status', 'pending')->latest('sent_to_whatsapp_at')->first();
            if ($post) {
                $post->update(['status' => 'skipped']);
                $telegram->sendMessage("⏭️ تم تجاوز: {$post->title_ar}");
            }

            return;
        }

        // === /start ===
        if ($lower === '/start') {
            $telegram->sendMessage(
                "👋 مرحباً حسان!\n\n".
                "أنا بوت أخبار Claude AI. هذي الأوامر المتاحة:\n\n".
                "🔍 <b>خبر جديد</b> أو <b>/news</b> — ابحث عن آخر أخبار Claude\n".
                "✅ <b>publish</b> أو <b>نشر</b> — انشر الخبر المعلّق\n".
                "✏️ <b>edit: تعليمات</b> — عدّل المحتوى\n".
                "⏭️ <b>skip</b> أو <b>تجاوز</b> — تجاوز الخبر\n\n".
                "أو ببساطة اكتب <b>خبر</b> وأنا أسوي الباقي 🚀"
            );

            return;
        }

        // === /status ===
        if ($lower === '/status') {
            $pending = NewsPost::where('status', 'pending')->count();
            $published = NewsPost::where('status', 'published')->count();
            $today = NewsPost::whereDate('published_at', today())->count();

            $telegram->sendMessage(
                "📊 <b>حالة النظام:</b>\n\n".
                "⏳ بانتظار الموافقة: {$pending}\n".
                "✅ منشورة: {$published}\n".
                "📅 منشورة اليوم: {$today}"
            );

            return;
        }

        // === scale commands — apply to the current pending post ===
        if (preg_match('/^(كبّ?ر|كبير|\+)(\s+الصورة)?$/u', trim($text))) {
            $this->adjustScale('up');

            return;
        }
        if (preg_match('/^(صغّ?ر|صغير|\-|\−)(\s+الصورة)?$/u', trim($text))) {
            $this->adjustScale('down');

            return;
        }

        // Unknown command
        $telegram->sendMessage(
            "🤔 ما فهمت الأمر.\n\n".
            "اكتب <b>خبر جديد</b> للبحث عن أخبار\n".
            "أو <b>/start</b> لعرض كل الأوامر"
        );
    }

    /**
     * Apply source-image scale change to the current pending post,
     * regenerate all 4 OG/tall images, and resend the preview.
     */
    private function adjustScale(string $direction): void
    {
        $telegram = app(TelegramService::class);
        $post = NewsPost::where('status', 'pending')->latest('sent_to_whatsapp_at')->first();

        if (! $post) {
            $telegram->sendMessage('⚠️ لا يوجد خبر بانتظار الموافقة.');

            return;
        }

        $current = (float) ($post->source_scale ?? 1.0);
        $new = $direction === 'up'
            ? min(2.0, round($current * 1.25, 2))
            : max(0.4, round($current / 1.25, 2));

        if (abs($new - $current) < 0.01) {
            $telegram->sendMessage(
                $direction === 'up'
                    ? '⚠️ الصورة وصلت الحد الأقصى (200%).'
                    : '⚠️ الصورة وصلت الحد الأدنى (40%).'
            );

            return;
        }

        $telegram->sendMessage("🔄 جاري إعادة التوليد بحجم ".(int) ($new * 100).'%...');

        $post->update(['source_scale' => $new]);
        GeneratePostImagesJob::dispatch($post->id, mode: 'regenerate', resendPreview: true);
    }

    private function handleCallback(array $callback): void
    {
        $telegram = app(TelegramService::class);
        $data = $callback['data'] ?? '';
        $callbackId = $callback['id'] ?? '';
        $messageId = $callback['message']['message_id'] ?? null;

        // === PUBLISH ALL ===
        if (preg_match('/^publish_all_(\d+)$/', $data, $m)) {
            $post = NewsPost::find($m[1]);
            if ($post && $post->status === 'pending') {
                $post->update(['status' => 'approved', 'approved_at' => now()]);
                $telegram->answerCallback($callbackId, '✅ جاري النشر...');

                if ($messageId) {
                    $telegram->editMessage($messageId,
                        "⏳ <b>جاري النشر...</b>\n\n📌 {$post->title_ar}\n\n🆔 #{$post->id}"
                    );
                }

                PublishNewsJob::dispatch($post->id);
            } else {
                $telegram->answerCallback($callbackId, '⚠️ الخبر غير متاح');
            }

            return;
        }

        // === PUBLISH WEBSITE ONLY ===
        if (preg_match('/^publish_website_(\d+)$/', $data, $m)) {
            $post = NewsPost::find($m[1]);
            if ($post && $post->status === 'pending') {
                $post->update([
                    'status' => 'published',
                    'published_at' => now(),
                ]);
                GeneratePostImagesJob::dispatch($post->id);
                $telegram->answerCallback($callbackId, '✅ منشور في الموقع');

                if ($messageId) {
                    $telegram->editMessage($messageId,
                        "✅ <b>منشور في الموقع</b>\n\n📌 {$post->title_ar}\n📖 <a href=\"{$post->getArticleUrl('ar')}\">عرض المقالة</a>"
                    );
                }
            }

            return;
        }

        // === SKIP ===
        if (preg_match('/^skip_(\d+)$/', $data, $m)) {
            $post = NewsPost::find($m[1]);
            if ($post && $post->status === 'pending') {
                $post->update(['status' => 'skipped']);
                $telegram->answerCallback($callbackId, '⏭️ تم التجاوز');

                if ($messageId) {
                    $telegram->editMessage($messageId,
                        "⏭️ <b>تم التجاوز</b>\n\n📌 <s>{$post->title_ar}</s>\n\n🆔 #{$post->id}"
                    );
                }
            }

            return;
        }

        // === PUBLISH TWITTER ONLY ===
        if (preg_match('/^publish_twitter_(\d+)$/', $data, $m)) {
            $post = NewsPost::find($m[1]);
            if ($post && $post->status === 'pending') {
                $post->update(['status' => 'approved', 'approved_at' => now()]);
                $telegram->answerCallback($callbackId, '🐦 جاري النشر على تويتر...');
                PublishNewsJob::dispatch($post->id, ['twitter']);
            }

            return;
        }

        // === PUBLISH TWITTER + INSTAGRAM ===
        if (preg_match('/^publish_twitter_ig_(\d+)$/', $data, $m)) {
            $post = NewsPost::find($m[1]);
            if ($post && $post->status === 'pending') {
                $post->update(['status' => 'approved', 'approved_at' => now()]);
                $telegram->answerCallback($callbackId, '🐦📸 جاري النشر...');
                PublishNewsJob::dispatch($post->id, ['twitter', 'instagram']);
            }

            return;
        }

        // === EDIT — prompt Hassan to type instructions ===
        if (preg_match('/^edit_(\d+)$/', $data, $m)) {
            $telegram->answerCallback($callbackId, '✏️');
            $telegram->sendMessage(
                "✏️ اكتب تعليمات التعديل:\n\n".
                "مثال:\n".
                "<code>edit: قصّر العنوان العربي</code>\n".
                "<code>edit: make the English tweet more engaging</code>"
            );

            return;
        }

        // === SCALE UP / DOWN / RESET ===
        if (preg_match('/^scale_(up|down|reset)_(\d+)$/', $data, $m)) {
            $action = $m[1];
            $post = NewsPost::find($m[2]);
            if (! $post || $post->status !== 'pending') {
                $telegram->answerCallback($callbackId, '⚠️ الخبر غير متاح');

                return;
            }

            $current = (float) ($post->source_scale ?? 1.0);
            $new = match ($action) {
                'up' => min(2.0, round($current * 1.25, 2)),
                'down' => max(0.4, round($current / 1.25, 2)),
                'reset' => 1.0,
            };

            if (abs($new - $current) < 0.01) {
                $telegram->answerCallback($callbackId, 'الصورة عند الحد');

                return;
            }

            $telegram->answerCallback($callbackId, '🔄 ' . (int) ($new * 100).'%');
            $post->update(['source_scale' => $new]);
            GeneratePostImagesJob::dispatch($post->id, mode: 'regenerate', resendPreview: true);

            return;
        }

        $telegram->answerCallback($callbackId, '❓');
    }
}
