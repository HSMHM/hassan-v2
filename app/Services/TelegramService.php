<?php

namespace App\Services;

use App\Models\NewsPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $token;

    private string $chatId;

    private string $baseUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";
    }

    public function sendMessage(string $text, ?array $replyMarkup = null): array
    {
        $payload = [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => false,
        ];

        if ($replyMarkup) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        $response = Http::post("{$this->baseUrl}/sendMessage", $payload);

        if (! $response->successful()) {
            Log::error('Telegram send failed', ['body' => $response->body()]);
        }

        return $response->json() ?? [];
    }

    public function editMessage(int $messageId, string $text, ?array $replyMarkup = null): array
    {
        $payload = [
            'chat_id' => $this->chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        $response = Http::post("{$this->baseUrl}/editMessageText", $payload);

        return $response->json() ?? [];
    }

    public function answerCallback(string $callbackQueryId, string $text = ''): void
    {
        Http::post("{$this->baseUrl}/answerCallbackQuery", [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
        ]);
    }

    /**
     * Send a single photo (by public URL) with optional caption + inline buttons.
     * Caption is limited to 1024 chars by Telegram.
     */
    public function sendPhoto(string $photoUrl, string $caption = '', ?array $replyMarkup = null): array
    {
        $payload = [
            'chat_id' => $this->chatId,
            'photo' => $photoUrl,
            'caption' => mb_substr($caption, 0, 1024),
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        $response = Http::post("{$this->baseUrl}/sendPhoto", $payload);

        if (! $response->successful()) {
            Log::error('Telegram sendPhoto failed', ['body' => $response->body()]);
        }

        return $response->json() ?? [];
    }

    /**
     * Send up to 10 photos as a single album. Does NOT support inline buttons
     * (Telegram limitation) — follow up with a separate sendMessage if needed.
     */
    public function sendMediaGroup(array $photoUrls, string $caption = ''): array
    {
        $media = [];
        foreach (array_values($photoUrls) as $i => $url) {
            $media[] = array_filter([
                'type' => 'photo',
                'media' => $url,
                'caption' => $i === 0 ? mb_substr($caption, 0, 1024) : null,
                'parse_mode' => $i === 0 ? 'HTML' : null,
            ]);
        }

        $response = Http::post("{$this->baseUrl}/sendMediaGroup", [
            'chat_id' => $this->chatId,
            'media' => json_encode($media),
        ]);

        if (! $response->successful()) {
            Log::error('Telegram sendMediaGroup failed', ['body' => $response->body()]);
        }

        return $response->json() ?? [];
    }

    public function sendNewsForApproval(NewsPost $post): void
    {
        $previewUrl = rtrim(config('app.url'), '/').'/cpanel/news-posts/'.$post->id.'/edit';
        $base = rtrim(config('app.url'), '/');

        $caption = "🔔 <b>خبر جديد</b>";
        if ($post->topic) {
            $caption .= " · <i>".htmlspecialchars($post->topic).'</i>';
        }
        $caption .= "\n\n📌 {$post->title_ar}\n\n";
        $caption .= mb_substr($post->excerpt_ar, 0, 150);
        if (mb_strlen($post->excerpt_ar) > 150) {
            $caption .= '...';
        }
        $caption .= "\n\n";

        $platforms = is_array($post->platform_captions) ? $post->platform_captions : [];
        if (! empty($platforms['twitter_ar'])) {
            $caption .= "🐦 <b>تويتر</b>\n";
            $caption .= htmlspecialchars(mb_substr($platforms['twitter_ar'], 0, 200));
            $caption .= "\n\n";
        }
        if (! empty($platforms['linkedin_en'])) {
            $caption .= "💼 <b>LinkedIn</b>\n";
            $caption .= htmlspecialchars(mb_substr($platforms['linkedin_en'], 0, 200));
            if (mb_strlen($platforms['linkedin_en']) > 200) {
                $caption .= '...';
            }
            $caption .= "\n\n";
        }

        // Image links — clickable to preview in browser
        if ($post->tall_image) {
            $caption .= "🖼 <a href=\"{$base}{$post->tall_image}\">صورة طويلة</a>";
        }
        if ($post->og_image) {
            $caption .= "  ·  <a href=\"{$base}{$post->og_image}\">صورة OG</a>";
        }
        $caption .= "\n";
        $caption .= "📎 <a href=\"{$post->source_url}\">المصدر</a>";
        $caption .= "  ·  ";
        $caption .= "<a href=\"{$previewUrl}\">👁 عرض وتعديل</a>";

        $scalePct = (int) round(((float) ($post->source_scale ?? 1.0)) * 100);
        $buttons = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ نشر وشارك', 'callback_data' => "publish_all_{$post->id}"],
                    ['text' => '💾 الموقع فقط', 'callback_data' => "publish_website_{$post->id}"],
                    ['text' => '⏭️ تجاوز', 'callback_data' => "skip_{$post->id}"],
                ],
                [
                    ['text' => '➖ صغّر الصورة', 'callback_data' => "scale_down_{$post->id}"],
                    ['text' => "🖼 {$scalePct}%", 'callback_data' => "scale_reset_{$post->id}"],
                    ['text' => '➕ كبّر الصورة', 'callback_data' => "scale_up_{$post->id}"],
                ],
            ],
        ];

        // Show image previews first (tall + OG), then send caption+buttons.
        // Media group can't carry buttons — so we do: album preview → text w/ buttons.
        $previewImages = array_values(array_filter([
            $post->tall_image ? $base.$post->tall_image : null,
            $post->og_image ? $base.$post->og_image : null,
        ]));

        if (count($previewImages) >= 2) {
            $this->sendMediaGroup($previewImages);
            $this->sendMessage($caption, $buttons);
        } elseif (count($previewImages) === 1) {
            // Only one image — send it with caption + buttons in a single photo message.
            $this->sendPhoto($previewImages[0], $caption, $buttons);
        } else {
            // Fallback: no images yet, send text only.
            $this->sendMessage($caption, $buttons);
        }

        $post->update([
            'sent_to_whatsapp_at' => now(),
            'status' => 'pending',
        ]);
    }

    public function notify(string $text): void
    {
        $this->sendMessage($text);
    }

    public function setWebhook(string $url, string $secret): array
    {
        $response = Http::post("{$this->baseUrl}/setWebhook", [
            'url' => $url,
            'secret_token' => $secret,
            'allowed_updates' => ['message', 'callback_query'],
        ]);

        return $response->json() ?? [];
    }

    public function deleteWebhook(): array
    {
        $response = Http::post("{$this->baseUrl}/deleteWebhook");

        return $response->json() ?? [];
    }
}
