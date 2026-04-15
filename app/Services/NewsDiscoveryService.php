<?php

namespace App\Services;

use App\Models\NewsPost;
use Illuminate\Support\Facades\Log;

class NewsDiscoveryService
{
    private string $discoveryModel;

    private string $contentModel;

    public function __construct(private GeminiService $gemini)
    {
        $this->discoveryModel = config('services.gemini.discovery_model', 'gemini-2.5-flash-lite');
        $this->contentModel = config('services.gemini.content_model', 'gemini-2.5-flash-lite');
    }

    public function discoverNews(): ?array
    {
        $existingUrls = NewsPost::whereNotNull('source_url')
            ->latest('id')
            ->take(10)
            ->pluck('source_url')
            ->implode("\n");

        $system = <<<'PROMPT'
Find ONE new Claude AI / Anthropic item from the last 7 days.
Sources: anthropic.com, X #ClaudeAI, Reddit r/ClaudeAI, YouTube, Hacker News, TechCrunch, dev blogs.
ANY type counts: product, feature, tutorial, benchmark, tool, social post, deal, research.
NEVER return found_news:false. Skip URLs in "already covered".

Respond ONLY in valid JSON (no markdown, no prose):
{"found_news":true,"items":[{"title":"...","source_url":"https://...","source_type":"blog|youtube|twitter|reddit|news|github|docs","summary":"2-3 sentences","significance":"high|medium|low","references":[]}]}
PROMPT;

        $user = "Find one Claude AI item.\n\nAlready covered:\n{$existingUrls}";

        $raw = $this->gemini->askWithWebSearch($system, $user, $this->discoveryModel);

        $data = $this->extractJson($raw);

        if (! $data || empty($data['items'])) {
            Log::warning('raw Gemini response (no items extracted)', [
                'preview' => mb_substr($raw, 0, 500),
                'length' => mb_strlen($raw),
            ]);

            return null;
        }

        return $data['items'];
    }

    /**
     * Pull a JSON object out of Gemini's response. Models sometimes wrap JSON in
     * markdown fences or lead with prose — try progressively looser matchers.
     */
    private function extractJson(string $raw): ?array
    {
        $candidates = [];

        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $raw, $m)) {
            $candidates[] = $m[1];
        }

        if (preg_match('/(\{[^{}]*"items"[\s\S]*\})/s', $raw, $m)) {
            $candidates[] = $m[1];
        }

        $first = strpos($raw, '{');
        $last = strrpos($raw, '}');
        if ($first !== false && $last !== false && $last > $first) {
            $candidates[] = substr($raw, $first, $last - $first + 1);
        }

        $candidates[] = $raw;

        foreach ($candidates as $candidate) {
            $decoded = json_decode(trim($candidate), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    public function generateContent(array $item): array
    {
        $system = <<<'PROMPT'
You are Hassan Almalki's bilingual content writer (AR + EN) about Claude AI news.

Style: Professional, enthusiastic about AI, Saudi professional Arabic (not formal MSA), clear English.

Return ONLY valid JSON (no markdown):
{
  "title_ar":"...","title_en":"...",
  "slug_ar":"lowercase-english-chars-hyphens","slug_en":"lowercase-hyphens",
  "excerpt_ar":"2-3 جمل","excerpt_en":"2-3 sentences",
  "content_ar":"HTML, 300-500 words, references section at end",
  "content_en":"HTML, 300-500 words, references section at end",
  "social_post_ar":"max 250 chars, emoji, end with: 📖 اقرأ المزيد: [ARTICLE_URL_AR]",
  "social_post_en":"max 250 chars, emoji, end with: 📖 Read more: [ARTICLE_URL_EN]",
  "meta_title_ar":"SEO title | حسان المالكي",
  "meta_title_en":"SEO title | Hassan Almalki",
  "meta_description_ar":"max 160 chars",
  "meta_description_en":"max 160 chars"
}

CRITICAL: social posts MUST be under 250 chars. Use [ARTICLE_URL_AR] and [ARTICLE_URL_EN] as URL placeholders.
PROMPT;

        $user = "Title: {$item['title']}\nSource: {$item['source_url']}\nSummary: {$item['summary']}\nRefs: ".json_encode($item['references'] ?? []);

        $raw = $this->gemini->ask($system, $user, $this->contentModel);

        if (preg_match('/```json\s*(.*?)\s*```/s', $raw, $m)) {
            $text = $m[1];
        } elseif (preg_match('/(\{.*"title_ar".*\})/s', $raw, $m)) {
            $text = $m[1];
        } else {
            $text = $raw;
        }

        $data = json_decode(trim($text), true);

        if (! $data || ! isset($data['title_ar'])) {
            Log::warning('Gemini content response (parse failed)', ['preview' => mb_substr($raw, 0, 500)]);
            throw new \RuntimeException('Failed to parse content from Gemini');
        }

        $data['source_url'] = $item['source_url'];
        $data['source_title'] = $item['title'];
        $data['source_type'] = $item['source_type'] ?? 'blog';
        $data['references'] = $item['references'] ?? [];

        return $this->sanitize($data);
    }

    /**
     * Strip dangerous tags from AI-generated content before persisting.
     * Plain-text fields get strip_tags; HTML content fields keep safe formatting
     * tags only — no scripts, iframes, style, or event handlers.
     */
    private function sanitize(array $data): array
    {
        $plainFields = [
            'title_ar', 'title_en', 'slug_ar', 'slug_en',
            'excerpt_ar', 'excerpt_en',
            'social_post_ar', 'social_post_en',
            'meta_title_ar', 'meta_title_en',
            'meta_description_ar', 'meta_description_en',
        ];

        foreach ($plainFields as $f) {
            if (isset($data[$f]) && is_string($data[$f])) {
                $data[$f] = trim(strip_tags($data[$f]));
            }
        }

        $allowedHtml = '<p><br><h2><h3><h4><ul><ol><li><strong><em><b><i><a><blockquote><code><pre>';
        foreach (['content_ar', 'content_en'] as $f) {
            if (isset($data[$f]) && is_string($data[$f])) {
                $clean = preg_replace('#<(script|style|iframe|object|embed|form)[^>]*>.*?</\1>#is', '', $data[$f]);
                $clean = preg_replace('#\son\w+\s*=\s*(["\'])[^"\']*\1#i', '', $clean);
                $data[$f] = strip_tags($clean, $allowedHtml);
            }
        }

        foreach (['social_post_ar', 'social_post_en'] as $f) {
            if (isset($data[$f]) && mb_strlen($data[$f]) > 280) {
                $data[$f] = mb_substr($data[$f], 0, 277).'...';
            }
        }

        return $data;
    }
}
