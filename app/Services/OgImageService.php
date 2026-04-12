<?php

namespace App\Services;

use ArPHP\I18N\Arabic;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;

class OgImageService
{
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
     * Build an image with: logo + title + almalki.sa
     */
    private function buildImage(int $w, int $h, string $title, bool $isArabic, array $positions)
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

        // Domain
        $image->text('almalki.sa', $positions['domainX'], $positions['domainY'], function (FontFactory $font) use ($positions) {
            $font->filename($this->fontPath);
            $font->size($positions['domainSize']);
            $font->color('666666');
            $font->align('center', 'center');
        });

        return $image;
    }

    /**
     * Horizontal 1200x630 Arabic — Twitter.
     */
    public function generateOg(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->buildImage(1200, 630, $title, true, [
            'logoX' => 600, 'logoY' => 85, 'logoH' => 60,
            'titleX' => 600, 'titleY' => 320, 'wordsPerLine' => 4, 'titleWrap' => 50, 'titleSize' => 38,
            'domainX' => 600, 'domainY' => 585, 'domainSize' => 30,
        ]);

        return $this->saveImage($image, "{$id}-og.jpg");
    }

    /**
     * Horizontal 1200x630 English — LinkedIn.
     */
    public function generateOgEn(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->buildImage(1200, 630, $title, false, [
            'logoX' => 600, 'logoY' => 85, 'logoH' => 60,
            'titleX' => 600, 'titleY' => 320, 'wordsPerLine' => 5, 'titleWrap' => 55, 'titleSize' => 36,
            'domainX' => 600, 'domainY' => 585, 'domainSize' => 28,
        ]);

        return $this->saveImage($image, "{$id}-og-en.jpg");
    }

    /**
     * Tall 1080x1350 (4:5) Arabic — Instagram + WhatsApp + Website AR.
     */
    public function generateTall(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->buildImage(1080, 1350, $title, true, [
            'logoX' => 540, 'logoY' => 170, 'logoH' => 72,
            'titleX' => 540, 'titleY' => 620, 'wordsPerLine' => 3, 'titleWrap' => 40, 'titleSize' => 44,
            'domainX' => 540, 'domainY' => 1280, 'domainSize' => 34,
        ]);

        return $this->saveImage($image, "{$id}-tall.jpg");
    }

    /**
     * Tall 1080x1350 English — Website EN.
     */
    public function generateTallEn(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->buildImage(1080, 1350, $title, false, [
            'logoX' => 540, 'logoY' => 170, 'logoH' => 72,
            'titleX' => 540, 'titleY' => 620, 'wordsPerLine' => 4, 'titleWrap' => 45, 'titleSize' => 40,
            'domainX' => 540, 'domainY' => 1280, 'domainSize' => 32,
        ]);

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
