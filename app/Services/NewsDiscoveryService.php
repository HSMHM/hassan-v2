<?php

namespace App\Services;

use App\Models\NewsPost;
use Illuminate\Support\Facades\Log;

class NewsDiscoveryService
{
    private string $discoveryModel;

    private string $contentModel;

    public function __construct(private ClaudeService $claude)
    {
        $this->discoveryModel = config('services.anthropic.discovery_model', 'claude-haiku-4-5-20251001');
        $this->contentModel = config('services.anthropic.content_model', 'claude-haiku-4-5-20251001');
    }

    public function discoverNews(): ?array
    {
        $existingUrls = NewsPost::pluck('source_url')->filter()->implode("\n");

        $system = <<<'PROMPT'
You are a news researcher. Your job is to ALWAYS find something about Claude AI features and capabilities.
NEVER return found_news:false. There is ALWAYS something to report.

FOCUS ON: features, capabilities, how-to, tips, tools, API updates, new models, developer experiences, benchmarks, tutorials.
AVOID: business deals, partnerships, funding, hiring, corporate agreements, regulatory news.

Search strategy — try in order:
1. New Claude features, model updates, API changes, Claude Code updates
2. Developer tutorials, tips & tricks, prompt engineering for Claude
3. New tools, libraries, MCP servers built for Claude
4. Benchmarks, comparisons: Claude vs GPT vs Gemini
5. YouTube demos, reviews of Claude features
6. Reddit r/ClaudeAI — feature discussions, use cases
7. Blog posts about using Claude (Medium, Dev.to, Hacker News)

Something ALWAYS exists. Skip already covered URLs below.

Respond ONLY in valid JSON:
{"found_news":true,"items":[{"title":"...","source_url":"https://...","source_type":"blog|youtube|twitter|docs|news|reddit|github|forum","summary":"2-3 sentences","significance":"high|medium|low","references":[]}]}
PROMPT;

        $user = "Find latest Claude AI features, capabilities, or developer content. I need at least one item.\n\nAlready covered:\n{$existingUrls}";

        $response = $this->claude->askWithWebSearch($system, $user, $this->discoveryModel);
        $raw = $this->claude->extractText($response);

        $data = $this->extractJson($raw);

        if (! $data || empty($data['items'])) {
            Log::warning('raw Claude response (no items extracted)', [
                'preview' => mb_substr($raw, 0, 500),
                'length' => mb_strlen($raw),
            ]);

            return null;
        }

        return $data['items'];
    }

    /**
     * Pull a JSON object out of Claude's response. Haiku often wraps JSON in
     * markdown fences or leads with prose — try progressively looser matchers.
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

        // Greedy last-resort: first `{` to last `}`.
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

        $response = $this->claude->ask($system, $user, $this->contentModel);
        $raw = $this->claude->extractText($response);

        if (preg_match('/```json\s*(.*?)\s*```/s', $raw, $m)) {
            $text = $m[1];
        } elseif (preg_match('/(\{.*"title_ar".*\})/s', $raw, $m)) {
            $text = $m[1];
        } else {
            $text = $raw;
        }

        $data = json_decode(trim($text), true);

        if (! $data || ! isset($data['title_ar'])) {
            throw new \RuntimeException('Failed to parse content from Claude');
        }

        $data['source_url'] = $item['source_url'];
        $data['source_title'] = $item['title'];
        $data['source_type'] = $item['source_type'] ?? 'blog';
        $data['references'] = $item['references'] ?? [];

        return $this->sanitize($data);
    }

    /**
     * Strip dangerous tags from Claude-generated content before persisting.
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
                // Strip script/style/iframe tags entirely (contents too) before the allowlist pass
                $clean = preg_replace('#<(script|style|iframe|object|embed|form)[^>]*>.*?</\1>#is', '', $data[$f]);
                // Drop any remaining on* event handlers
                $clean = preg_replace('#\son\w+\s*=\s*(["\'])[^"\']*\1#i', '', $clean);
                $data[$f] = strip_tags($clean, $allowedHtml);
            }
        }

        // Enforce social post length so Twitter/X never rejects the queue
        foreach (['social_post_ar', 'social_post_en'] as $f) {
            if (isset($data[$f]) && mb_strlen($data[$f]) > 280) {
                $data[$f] = mb_substr($data[$f], 0, 277).'...';
            }
        }

        return $data;
    }
}
