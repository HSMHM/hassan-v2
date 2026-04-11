<?php

namespace App\Services;

use App\Models\NewsPost;

class NewsDiscoveryService
{
    public function __construct(private ClaudeService $claude) {}

    public function discoverNews(): ?array
    {
        $existingUrls = NewsPost::pluck('source_url')->filter()->implode("\n");

        $system = <<<'PROMPT'
You are a news researcher for Claude AI and Anthropic updates.
Find GENUINELY NEW items from the last 48 hours only.
Skip anything already covered (see list below).
Focus on: model releases, product updates, API changes, pricing, features, partnerships, research papers.

Respond ONLY in valid JSON (no markdown):
{"found_news":true,"items":[{"title":"...","source_url":"https://...","source_type":"blog|youtube|twitter|docs|news","summary":"2-3 sentences","significance":"high|medium|low","references":[{"type":"...","url":"...","title":"..."}]}]}

If nothing new: {"found_news":false,"items":[]}
PROMPT;

        $user = "Search for latest Claude AI and Anthropic news. Check anthropic.com/news, @AnthropicAI on X, YouTube, tech news.\n\nAlready covered:\n{$existingUrls}";

        $response = $this->claude->askWithWebSearch($system, $user);
        $text = preg_replace('/```json\s*|\s*```/', '', $this->claude->extractText($response));
        $data = json_decode(trim($text), true);

        return ($data && ($data['found_news'] ?? false)) ? ($data['items'] ?? []) : null;
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

        $response = $this->claude->ask($system, $user);
        $text = preg_replace('/```json\s*|\s*```/', '', $this->claude->extractText($response));
        $data = json_decode(trim($text), true);

        if (! $data || ! isset($data['title_ar'])) {
            throw new \RuntimeException('Failed to parse content from Claude');
        }

        $data['source_url'] = $item['source_url'];
        $data['source_title'] = $item['title'];
        $data['source_type'] = $item['source_type'] ?? 'blog';
        $data['references'] = $item['references'] ?? [];

        return $data;
    }
}
