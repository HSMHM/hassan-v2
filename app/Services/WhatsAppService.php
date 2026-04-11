<?php

namespace App\Services;

use App\Models\NewsPost;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    private string $token;

    private string $baseUrl;

    private string $ownerPhone;

    public function __construct()
    {
        $this->token = config('services.whapi.token');
        $this->baseUrl = config('services.whapi.base_url');
        $this->ownerPhone = config('services.whapi.owner_phone');
    }

    public function sendMessage(string $text, ?string $phone = null): array
    {
        return Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/messages/text", [
            'to' => ($phone ?? $this->ownerPhone).'@s.whatsapp.net',
            'body' => $text,
        ])->json() ?? [];
    }

    public function postTextStatus(string $text): array
    {
        return Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/statuses/text", [
            'body' => $text,
        ])->json() ?? [];
    }

    public function postImageStatus(string $imageUrl, string $caption = ''): array
    {
        return Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/statuses/image", [
            'media' => ['link' => $imageUrl],
            'caption' => $caption,
        ])->json() ?? [];
    }

    public function sendNewsForApproval(NewsPost $post): void
    {
        $msg = "🔔 *خبر جديد عن Claude AI*\n\n";
        $msg .= "📌 *{$post->title_ar}*\n{$post->excerpt_ar}\n\n";
        $msg .= "---\n📌 *{$post->title_en}*\n{$post->excerpt_en}\n\n";
        $msg .= "---\n🐦 *Twitter AR:*\n{$post->social_post_ar}\n\n";
        $msg .= "🐦 *Twitter EN:*\n{$post->social_post_en}\n\n";
        $msg .= "---\n📎 Source: {$post->source_url}\n\n";
        $msg .= "---\n";
        $msg .= "✅ *publish* → نشر على جميع المنصات\n";
        $msg .= "✏️ *edit: تعليمات* → تعديل المحتوى\n";
        $msg .= "⏭️ *skip* → تجاوز\n";
        $msg .= "🐦 *publish x* → تويتر فقط\n";
        $msg .= "🐦📸 *publish x ig* → تويتر + انستقرام\n";
        $msg .= "\n🆔 #{$post->id}";

        $this->sendMessage($msg);
        $post->update(['sent_to_whatsapp_at' => now(), 'status' => 'pending']);
    }
}
