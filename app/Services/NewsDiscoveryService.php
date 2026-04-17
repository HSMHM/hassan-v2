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

    public function discoverNews(?string $topic = null): ?array
    {
        $existingUrls = NewsPost::whereNotNull('source_url')
            ->latest('id')
            ->take(10)
            ->pluck('source_url')
            ->implode("\n");

        $topic = $topic ? trim($topic) : null;

        if ($topic) {
            $system = <<<PROMPT
Find ONE recent, high-quality item about: "{$topic}" (last 30 days preferred, older acceptable if significant).
Search broadly: official blogs, news sites, YouTube, X/Twitter, Reddit, Hacker News, research papers, industry reports.
ANY type counts: announcement, launch, tutorial, analysis, interview, benchmark, case study, opinion.
NEVER return found_news:false. Skip URLs in "already covered".

Respond ONLY in valid JSON (no markdown, no prose):
{"found_news":true,"items":[{"title":"...","source_url":"https://...","source_type":"blog|youtube|twitter|reddit|news|github|docs|paper","summary":"2-3 sentences","significance":"high|medium|low","references":[]}]}
PROMPT;

            $user = "Find one item about: {$topic}\n\nAlready covered:\n{$existingUrls}";
        } else {
            $system = <<<'PROMPT'
Find ONE new Claude AI / Anthropic item from the last 7 days.
Sources: anthropic.com, X #ClaudeAI, Reddit r/ClaudeAI, YouTube, Hacker News, TechCrunch, dev blogs.
ANY type counts: product, feature, tutorial, benchmark, tool, social post, deal, research.
NEVER return found_news:false. Skip URLs in "already covered".

Respond ONLY in valid JSON (no markdown, no prose):
{"found_news":true,"items":[{"title":"...","source_url":"https://...","source_type":"blog|youtube|twitter|reddit|news|github|docs","summary":"2-3 sentences","significance":"high|medium|low","references":[]}]}
PROMPT;

            $user = "Find one Claude AI item.\n\nAlready covered:\n{$existingUrls}";
        }

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
        $decoded = json_decode(trim($raw), true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $raw, $m)) {
            $decoded = json_decode(trim($m[1]), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Balanced bracket matching — find the first complete JSON object.
        $start = strpos($raw, '{');
        if ($start !== false) {
            $depth = 0;
            $inStr = false;
            $escape = false;
            $len = strlen($raw);
            for ($i = $start; $i < $len; $i++) {
                $c = $raw[$i];
                if ($escape) {
                    $escape = false;
                    continue;
                }
                if ($c === '\\') {
                    $escape = true;
                    continue;
                }
                if ($c === '"') {
                    $inStr = ! $inStr;
                    continue;
                }
                if ($inStr) {
                    continue;
                }
                if ($c === '{') {
                    $depth++;
                } elseif ($c === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $decoded = json_decode(substr($raw, $start, $i - $start + 1), true);
                        if (is_array($decoded)) {
                            return $decoded;
                        }
                        break;
                    }
                }
            }
        }

        return null;
    }

    public function generateContent(array $item, ?string $topic = null): array
    {
        $topicLine = $topic ? "Topic context: {$topic}\n\n" : '';

        $system = <<<PROMPT
You are Hassan Almalki writing news on his personal site (almalki.sa). Hassan is a Saudi product/tech professional.
Voice: Hassan speaks in first person — as if he personally wrote each caption.

{$topicLine}You produce bilingual article content (AR + EN) PLUS per-platform social captions with distinct tones.

Per-platform tone rules (CRITICAL — these are NOT interchangeable):
- twitter_ar: Saudi Najdi colloquial dialect (لهجة نجدية عامية), personal first-person, like Hassan is chatting with friends. Use words like: "الصراحة", "والله", "شفت", "جربت", "ياخي", "طاف علي خبر", "وش رايكم". NO formal MSA. Emoji allowed.
- instagram_ar: Same Najdi colloquial style as twitter_ar but can be slightly longer. Same personal voice.
- linkedin_en: English, inspiring and reflective tone, first-person, suited for a thoughtful professional. Think: personal insight + takeaway. NOT a press-release summary. Start with a hook. 2-4 short paragraphs.

Return ONLY valid JSON (no markdown):
{
  "title_ar":"...","title_en":"...",
  "slug_ar":"lowercase-english-chars-hyphens","slug_en":"lowercase-hyphens",
  "excerpt_ar":"2-3 جمل","excerpt_en":"2-3 sentences",
  "content_ar":"HTML, 300-500 words, references section at end",
  "content_en":"HTML, 300-500 words, references section at end",
  "social_post_ar":"max 250 chars Saudi Arabic general, emoji, end with: 📖 [ARTICLE_URL_AR]",
  "social_post_en":"max 250 chars clear English, emoji, end with: 📖 [ARTICLE_URL_EN]",
  "platform_captions":{
    "twitter_ar":"max 260 chars, NAJDI colloquial, first-person Hassan voice, end with: [ARTICLE_URL_AR]",
    "instagram_ar":"max 800 chars, NAJDI colloquial, first-person Hassan voice, hashtags at end OK, end with link: [ARTICLE_URL_AR]",
    "linkedin_en":"max 1500 chars, inspiring English first-person, paragraphs separated by blank lines, end with: [ARTICLE_URL_EN]"
  },
  "meta_title_ar":"SEO title | حسان المالكي",
  "meta_title_en":"SEO title | Hassan Almalki",
  "meta_description_ar":"max 160 chars",
  "meta_description_en":"max 160 chars"
}

Placeholders [ARTICLE_URL_AR] and [ARTICLE_URL_EN] will be replaced with the real URLs at publish time — keep them literal.
PROMPT;

        $user = "Title: {$item['title']}\nSource: {$item['source_url']}\nSummary: {$item['summary']}\nRefs: ".json_encode($item['references'] ?? []);

        $raw = $this->gemini->ask($system, $user, $this->contentModel);

        $data = $this->extractJson($raw);

        if (! $data || ! isset($data['title_ar'])) {
            Log::warning('Gemini content response (parse failed)', [
                'preview' => mb_substr($raw, 0, 500),
                'length' => mb_strlen($raw),
            ]);
            throw new \RuntimeException('Failed to parse content from Gemini');
        }

        $data['source_url'] = $item['source_url'];
        $data['source_title'] = $item['title'];
        $data['source_type'] = $item['source_type'] ?? 'blog';
        $data['topic'] = $topic;
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

        if (isset($data['platform_captions']) && is_array($data['platform_captions'])) {
            $limits = ['twitter_ar' => 280, 'instagram_ar' => 2200, 'linkedin_en' => 3000];
            $clean = [];
            foreach ($limits as $key => $max) {
                $val = $data['platform_captions'][$key] ?? null;
                if (! is_string($val)) {
                    continue;
                }
                $val = trim(strip_tags($val));
                if (mb_strlen($val) > $max) {
                    $val = mb_substr($val, 0, $max - 3).'...';
                }
                $clean[$key] = $val;
            }
            $data['platform_captions'] = $clean;
        }

        return $data;
    }
}
