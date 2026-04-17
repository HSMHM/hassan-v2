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
        $this->discoveryModel = config('services.gemini.discovery_model', 'gemini-2.5-pro');
        $this->contentModel = config('services.gemini.content_model', 'gemini-2.5-pro');
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
You are Hassan Almalki writing on his personal site (almalki.sa). Hassan is a Saudi product/tech professional who follows this space closely.
Voice: Hassan speaks as a PEER in the field, not as a spectator. He's already deep in this world — so he reacts like someone who has context, not someone who's seeing it for the first time. First person, measured, analytical.

{$topicLine}You produce bilingual article content (AR + EN) PLUS per-platform social captions with distinct tones.

CRITICAL voice rules — NO hype, NO fanboy tone:
- NEVER use: "يا جماعة", "وش ذا السرعة", "صدق", "بطل", "ما صدقت", "مجنون", "🤯", "🔥" as emphasis
- NEVER use: "changes the game", "game-changer", "just when you think...", "mind-blowing", "the future is here", "not just an incremental update"
- NEVER open with awe. Open with an observation, a technical detail, or a pragmatic take.
- Limit emojis: 0-2 max per caption, functional not decorative. Prefer none.

Per-platform tone rules (distinct):
- twitter_ar: Saudi Najdi colloquial (لهجة نجدية خفيفة), but calm and thoughtful. Talk like a domain peer sharing a useful note. Allowed words: "الصراحة", "لاحظت", "شفت", "جربت", "اللي لفت نظري", "من ناحية", "عملياً". Short observation + why it matters in 1 line.
- instagram_ar: Same Najdi peer tone, a little more room for context. Still analytical, not excited. No hashtag spam (max 3 relevant ones if any).
- linkedin_en: Reflective professional English, first person. Lead with a specific observation or detail — NOT a hook like "Just when you think..." or "X changes the game". Think: a senior PM sharing a note with peers on the timeline. 2-4 short paragraphs, concrete takeaway, skip marketing clichés.

Example calibration:
- BAD (twitter_ar): "يا جماعة وش ذا السرعة! 🤯 Claude 3.5 Sonnet نزل وهو بطل..."
- GOOD (twitter_ar): "أنثروبيك نزّلت Claude 3.5 Sonnet. أذكى من Opus وأسرع وأرخص — الأهم ميزة Artifacts اللي تخلّي الموديل يرتب مخرجاته في لوحة جانبية. التأثير العملي: أدوات أقل، واجهة أنظف."
- BAD (linkedin_en): "Just when you think the AI race might be settling, a new release completely changes the game."
- GOOD (linkedin_en): "Anthropic quietly shipped Claude 3.5 Sonnet this week. Two things stood out: it beats Opus on most benchmarks at a fraction of the cost, and Artifacts finally gives long-form LLM output a usable surface."

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
    "twitter_ar":"max 260 chars, calm Najdi peer tone, analytical not fanboy, end with: [ARTICLE_URL_AR]",
    "instagram_ar":"max 800 chars, calm Najdi peer tone, analytical not fanboy, end with: [ARTICLE_URL_AR]",
    "linkedin_en":"max 1500 chars, reflective professional English, no marketing clichés, paragraphs separated by blank lines, end with: [ARTICLE_URL_EN]"
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
     * Regenerate only the platform_captions for the given platforms, preserving
     * any existing captions for platforms not in the list. Returns the full
     * merged captions array ready to persist.
     */
    public function regeneratePlatformCaptions(NewsPost $post, array $platforms): array
    {
        $allowed = ['twitter_ar', 'instagram_ar', 'linkedin_en'];
        $keys = array_values(array_intersect($platforms, $allowed));
        if (empty($keys)) {
            throw new \InvalidArgumentException('No valid platforms specified');
        }

        $rules = [
            'twitter_ar' => 'max 260 chars, calm Najdi peer tone, analytical not fanboy, end with: [ARTICLE_URL_AR]',
            'instagram_ar' => 'max 800 chars, calm Najdi peer tone, analytical not fanboy, end with: [ARTICLE_URL_AR]',
            'linkedin_en' => 'max 1500 chars, reflective professional English, no marketing clichés, end with: [ARTICLE_URL_EN]',
        ];

        $requested = [];
        foreach ($keys as $k) {
            $requested[] = "  \"{$k}\":\"{$rules[$k]}\"";
        }
        $shape = "{\n".implode(",\n", $requested)."\n}";

        $system = <<<PROMPT
You are Hassan Almalki — a Saudi product/tech professional speaking as a PEER in this field, not a spectator.
NO hype, NO fanboy tone.
NEVER use: "يا جماعة", "وش ذا السرعة", "صدق", "بطل", "🤯", "🔥", "changes the game", "mind-blowing", "just when you think...", "not just an incremental update".
Open with an observation or technical detail, not awe. 0-2 emojis max — functional, not decorative.

Regenerate ONLY these social captions FROM SCRATCH — try a different angle/wording than a typical first attempt:
{$shape}

Return ONLY valid JSON (no markdown) containing exactly the requested keys and nothing else.
Keep [ARTICLE_URL_AR] and [ARTICLE_URL_EN] placeholders literal.
PROMPT;

        $user = "Title AR: {$post->title_ar}\n"
            ."Title EN: {$post->title_en}\n"
            ."Excerpt AR: {$post->excerpt_ar}\n"
            ."Excerpt EN: {$post->excerpt_en}\n"
            ."Source: {$post->source_url}";

        $raw = $this->gemini->ask($system, $user, $this->contentModel);
        $data = $this->extractJson($raw);

        if (! $data) {
            Log::warning('regeneratePlatformCaptions parse failed', [
                'post_id' => $post->id,
                'preview' => mb_substr($raw, 0, 500),
            ]);
            throw new \RuntimeException('Failed to parse regenerated captions');
        }

        $limits = ['twitter_ar' => 280, 'instagram_ar' => 2200, 'linkedin_en' => 3000];
        $existing = is_array($post->platform_captions) ? $post->platform_captions : [];

        foreach ($keys as $key) {
            if (! isset($data[$key]) || ! is_string($data[$key])) {
                continue;
            }
            $val = trim(strip_tags($data[$key]));
            if (mb_strlen($val) > $limits[$key]) {
                $val = mb_substr($val, 0, $limits[$key] - 3).'...';
            }
            $existing[$key] = $val;
        }

        return $existing;
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
