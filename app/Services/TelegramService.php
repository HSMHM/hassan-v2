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

    public function sendNewsForApproval(NewsPost $post): void
    {
        $previewUrl = rtrim(config('app.url'), '/').'/cpanel/news-posts/'.$post->id.'/edit';

        $msg = "🔔 <b>خبر جديد</b>\n\n";
        $msg .= "📌 {$post->title_ar}\n\n";
        $msg .= mb_substr($post->excerpt_ar, 0, 150);
        if (mb_strlen($post->excerpt_ar) > 150) {
            $msg .= '...';
        }
        $msg .= "\n\n";
        $msg .= "📎 <a href=\"{$post->source_url}\">المصدر</a>";
        $msg .= '  ·  ';
        $msg .= "<a href=\"{$previewUrl}\">👁 عرض وتعديل</a>";

        $buttons = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ نشر وشارك', 'callback_data' => "publish_all_{$post->id}"],
                    ['text' => '💾 الموقع فقط', 'callback_data' => "publish_website_{$post->id}"],
                    ['text' => '⏭️ تجاوز', 'callback_data' => "skip_{$post->id}"],
                ],
            ],
        ];

        $this->sendMessage($msg, $buttons);

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
