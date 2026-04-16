<?php

namespace App\Services;

use App\Models\NewsPost;
use ArPHP\I18N\Arabic;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;

class OgImageService
{
    /**
     * Generate any missing images for a post and persist their paths.
     * Safe to call multiple times — skips fields that are already filled.
     */
    public function ensureAll(NewsPost $post): void
    {
        $subtitle = $post->source_title ?: 'almalki.sa';

        // Pull the source-article thumbnail first (once) so all 4 layouts share it.
        if (! $post->source_image && $post->source_url) {
            try {
                $path = app(SourceImageExtractor::class)->extract($post->source_url, $post->id);
                if ($path) {
                    $post->update(['source_image' => $path]);
                } else {
                    Log::warning('Source image extraction returned null', [
                        'post_id' => $post->id,
                        'source_url' => $post->source_url,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Source image extract failed', ['post_id' => $post->id, 'error' => $e->getMessage()]);
            }
        }

        $src = $post->source_image;
        $scale = (float) ($post->source_scale ?? 1.0);

        $jobs = [
            'og_image' => fn () => $this->generateOg($post->title_ar, $subtitle, $post->id, $src, $scale),
            'og_image_en' => fn () => $this->generateOgEn($post->title_en, $subtitle, $post->id, $src, $scale),
            'tall_image' => fn () => $this->generateTall($post->title_ar, $subtitle, $post->id, $src, $scale),
            'tall_image_en' => fn () => $this->generateTallEn($post->title_en, $subtitle, $post->id, $src, $scale),
        ];

        foreach ($jobs as $field => $generator) {
            if ($post->{$field}) {
                continue;
            }
            try {
                $post->update([$field => $generator()]);
            } catch (\Throwable $e) {
                Log::warning("OG image ({$field}) failed", ['post_id' => $post->id, 'error' => $e->getMessage()]);
            }
        }

        $post->refresh();
    }

    /**
     * Force regenerate all 4 layout images (keeps the already-downloaded source).
     * Used after the user changes source_scale.
     */
    public function regenerateAll(NewsPost $post): void
    {
        $post->update([
            'og_image' => null,
            'og_image_en' => null,
            'tall_image' => null,
            'tall_image_en' => null,
        ]);

        $this->ensureAll($post);
    }


    private ImageManager $manager;

    private string $fontPath;

    private string $logoPath;

    private Arabic $arabic;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
        $this->fontPath = resource_path('fonts/Cairo.ttf');
        $this->logoPath = public_path('img/logo/logo.png');
        $this->arabic = new Arabic;
    }

    private function shapeArabic(string $text): string
    {
        return $this->arabic->utf8Glyphs($text);
    }

    private function wrapByWords(string $text, int $wordsPerLine): string
    {
        $words = preg_split('/\s+/u', trim($text));
        $lines = [];

        for ($i = 0; $i < count($words); $i += $wordsPerLine) {
            $lines[] = implode(' ', array_slice($words, $i, $wordsPerLine));
        }

        return implode("\n", $lines);
    }

    private function placeLogo($image, int $centerX, int $centerY, int $height): void
    {
        if (! file_exists($this->logoPath)) {
            return;
        }

        $logo = $this->manager->decodePath($this->logoPath);
        $logo->scale(height: $height);

        $x = $centerX - (int) ($logo->width() / 2);
        $y = $centerY - (int) ($logo->height() / 2);

        $image->insert($logo, $x, $y);
    }

    /**
     * Build an image with: logo + title + optional source thumbnail + almalki.sa
     */
    private function buildImage(int $w, int $h, string $title, bool $isArabic, array $positions, ?string $sourceImagePath = null)
    {
        $image = $this->manager->createImage($w, $h)->fill('121212');

        // Logo
        $this->placeLogo($image, $positions['logoX'], $positions['logoY'], $positions['logoH']);

        // Title — render each line separately for proper line spacing
        $wrapped = isset($positions['wordsPerLine'])
            ? $this->wrapByWords($title, $positions['wordsPerLine'])
            : wordwrap($title, $positions['titleWrap'], "\n", true);
        $lines = explode("\n", $wrapped);
        $lineSpacing = (int) ($positions['titleSize'] * 1.6);
        $totalHeight = count($lines) * $lineSpacing;
        $startY = $positions['titleY'] - (int) ($totalHeight / 2) + (int) ($lineSpacing / 2);

        foreach ($lines as $i => $line) {
            $displayLine = $isArabic ? $this->shapeArabic(trim($line)) : trim($line);
            $y = $startY + ($i * $lineSpacing);
            $image->text($displayLine, $positions['titleX'], $y, function (FontFactory $font) use ($positions) {
                $font->filename($this->fontPath);
                $font->size($positions['titleSize']);
                $font->color('ffffff');
                $font->align('center', 'center');
            });
        }

        // Source thumbnail (below title, if provided and if layout defines a slot)
        if ($sourceImagePath && isset($positions['sourceY'], $positions['sourceW'], $positions['sourceH'])) {
            $scale = max(0.4, min(2.0, (float) ($positions['sourceScale'] ?? 1.0)));
            $scaledW = (int) ($positions['sourceW'] * $scale);
            $scaledH = (int) ($positions['sourceH'] * $scale);
            $this->placeSourceImage($image, $sourceImagePath, $positions['sourceX'] ?? ($w / 2), $positions['sourceY'], $scaledW, $scaledH);
        }

        // Domain
        $image->text('almalki.sa', $positions['domainX'], $positions['domainY'], function (FontFactory $font) use ($positions) {
            $font->filename($this->fontPath);
            $font->size($positions['domainSize']);
            $font->color('666666');
            $font->align('center', 'center');
        });

        return $image;
    }

    private function placeSourceImage($canvas, string $relativePath, int $centerX, int $topY, int $maxW, int $maxH): void
    {
        $full = public_path(ltrim($relativePath, '/'));
        if (! is_file($full)) {
            return;
        }

        try {
            $thumb = $this->manager->decodePath($full);

            // Fit within maxW × maxH preserving aspect ratio.
            $ratio = min($maxW / $thumb->width(), $maxH / $thumb->height());
            $newW = (int) ($thumb->width() * $ratio);
            $newH = (int) ($thumb->height() * $ratio);
            $thumb->resize($newW, $newH);

            $x = $centerX - (int) ($newW / 2);
            $canvas->insert($thumb, $x, $topY);
        } catch (\Throwable $e) {
            Log::info('Source image insert failed', ['path' => $relativePath, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Horizontal 1200x630 Arabic — Twitter.
     */
    public function generateOg(string $title, ?string $subtitle, int|string $id, ?string $sourceImage = null, float $sourceScale = 1.0): string
    {
        $image = $this->buildImage(1200, 630, $title, true, [
            'logoX' => 600, 'logoY' => 70, 'logoH' => 52,
            'titleX' => 600, 'titleY' => 240, 'wordsPerLine' => 4, 'titleWrap' => 50, 'titleSize' => 34,
            'sourceX' => 600, 'sourceY' => 360, 'sourceW' => 280, 'sourceH' => 160, 'sourceScale' => $sourceScale,
            'domainX' => 600, 'domainY' => 590, 'domainSize' => 28,
        ], $sourceImage);

        return $this->saveImage($image, "{$id}-og.jpg");
    }

    public function generateOgEn(string $title, ?string $subtitle, int|string $id, ?string $sourceImage = null, float $sourceScale = 1.0): string
    {
        $image = $this->buildImage(1200, 630, $title, false, [
            'logoX' => 600, 'logoY' => 70, 'logoH' => 52,
            'titleX' => 600, 'titleY' => 240, 'wordsPerLine' => 5, 'titleWrap' => 55, 'titleSize' => 32,
            'sourceX' => 600, 'sourceY' => 360, 'sourceW' => 280, 'sourceH' => 160, 'sourceScale' => $sourceScale,
            'domainX' => 600, 'domainY' => 590, 'domainSize' => 26,
        ], $sourceImage);

        return $this->saveImage($image, "{$id}-og-en.jpg");
    }

    public function generateTall(string $title, ?string $subtitle, int|string $id, ?string $sourceImage = null, float $sourceScale = 1.0): string
    {
        $image = $this->buildImage(1080, 1350, $title, true, [
            'logoX' => 540, 'logoY' => 150, 'logoH' => 64,
            'titleX' => 540, 'titleY' => 500, 'wordsPerLine' => 3, 'titleWrap' => 40, 'titleSize' => 40,
            'sourceX' => 540, 'sourceY' => 770, 'sourceW' => 500, 'sourceH' => 280, 'sourceScale' => $sourceScale,
            'domainX' => 540, 'domainY' => 1290, 'domainSize' => 32,
        ], $sourceImage);

        return $this->saveImage($image, "{$id}-tall.jpg");
    }

    public function generateTallEn(string $title, ?string $subtitle, int|string $id, ?string $sourceImage = null, float $sourceScale = 1.0): string
    {
        $image = $this->buildImage(1080, 1350, $title, false, [
            'logoX' => 540, 'logoY' => 150, 'logoH' => 64,
            'titleX' => 540, 'titleY' => 500, 'wordsPerLine' => 4, 'titleWrap' => 45, 'titleSize' => 36,
            'sourceX' => 540, 'sourceY' => 770, 'sourceW' => 500, 'sourceH' => 280, 'sourceScale' => $sourceScale,
            'domainX' => 540, 'domainY' => 1290, 'domainSize' => 30,
        ], $sourceImage);

        return $this->saveImage($image, "{$id}-tall-en.jpg");
    }

    /**
     * Vertical 1080x1920 (9:16) — Snapchat Story.
     */
    public function generateStory(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->manager->createImage(1080, 1920)->fill('121212');

        $this->placeLogo($image, 540, 230, 90);

        $image->text($this->shapeArabic('تم إضافة خبر بعنوان :'), 540, 750, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(28);
            $font->color('a0a0a0');
            $font->align('center', 'center');
        });

        $wrapped = $this->wrapByWords($title, 3);
        $lines = explode("\n", $wrapped);
        $lineSpacing = (int) (44 * 1.6);
        $totalHeight = count($lines) * $lineSpacing;
        $startY = 960 - (int) ($totalHeight / 2) + (int) ($lineSpacing / 2);

        foreach ($lines as $i => $line) {
            $y = $startY + ($i * $lineSpacing);
            $image->text($this->shapeArabic(trim($line)), 540, $y, function (FontFactory $font) {
                $font->filename($this->fontPath);
                $font->size(44);
                $font->color('ffffff');
                $font->align('center', 'center');
            });
        }

        $image->text($this->shapeArabic('في موقعي'), 540, 1200, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(28);
            $font->color('a0a0a0');
            $font->align('center', 'center');
        });

        $image->text('almalki.sa', 540, 1780, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(34);
            $font->color('666666');
            $font->align('center', 'center');
        });

        return $this->saveImage($image, "{$id}-story.jpg");
    }

    /**
     * Site-wide default OG image.
     */
    public function generateDefault(): string
    {
        $image = $this->manager->createImage(1200, 630)->fill('121212');

        $this->placeLogo($image, 600, 250, 104);

        $image->text($this->shapeArabic('مطور تطبيقات ويب ومدير منتجات تقنية'), 600, 430, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(24);
            $font->color('757575');
            $font->align('center', 'center');
        });

        $path = public_path('img/og-image.jpg');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $image->save($path, quality: 85);

        return '/img/og-image.jpg';
    }

    private function saveImage($image, string $filename): string
    {
        $dir = public_path('uploads/og');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $image->save("$dir/$filename", quality: 85);

        return "/uploads/og/$filename";
    }
}
