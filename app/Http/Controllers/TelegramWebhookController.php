<?php

namespace App\Http\Controllers;

use App\Jobs\DiscoverNewsJob;
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

            try {
                DiscoverNewsJob::dispatchSync();

                $last = \Illuminate\Support\Facades\Cache::get(DiscoverNewsJob::REASON_CACHE_KEY);
                $reason = $last['reason'] ?? null;

                if ($reason === 'created') {
                    // sendNewsForApproval was already called inside the job.
                    return;
                }

                $message = match ($reason) {
                    'pending_exists' => '⏳ فيه خبر بانتظار موافقتك — ارجع للرسالة السابقة.',
                    'no_items' => "🔍 Claude ما رجّع أخبار هالمرة. جرّب مرة ثانية بعد دقيقة.\n\nلو تكرر الخطأ، شيك اللوق:\n<code>grep 'raw Claude response' storage/logs/laravel.log | tail -5</code>",
                    'duplicate_url' => '🔁 الخبر اللي لقاه منشور عندك من قبل — جرّب مرة ثانية لتغطية مصدر آخر.',
                    'generate_failed' => "❌ فشل توليد المحتوى:\n<code>".($last['error'] ?? 'unknown')."</code>",
                    default => 'ℹ️ لا توجد أخبار جديدة حالياً. حاول لاحقاً.',
                };

                $telegram->sendMessage($message);
            } catch (\Throwable $e) {
                $telegram->sendMessage("❌ حدث خطأ أثناء البحث:\n<code>{$e->getMessage()}</code>");
            }

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

        // Unknown command
        $telegram->sendMessage(
            "🤔 ما فهمت الأمر.\n\n".
            "اكتب <b>خبر جديد</b> للبحث عن أخبار\n".
            "أو <b>/start</b> لعرض كل الأوامر"
        );
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

        $telegram->answerCallback($callbackId, '❓');
    }
}
