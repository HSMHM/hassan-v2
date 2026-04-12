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
     * Horizontal 1200x630 — Twitter + LinkedIn.
     */
    public function generateOg(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->manager->createImage(1200, 630)->fill('121212');

        $this->placeLogo($image, 600, 80, 50);

        $wrapped = wordwrap($title, 35, "\n", true);
        $image->text($this->shapeArabic($wrapped), 600, 310, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(44);
            $font->color('ffffff');
            $font->align('center', 'center');
            $font->lineHeight(1.8);
        });

        if ($subtitle) {
            $image->text($this->shapeArabic($subtitle), 600, 510, function (FontFactory $font) {
                $font->filename($this->fontPath);
                $font->size(20);
                $font->color('a0a0a0');
                $font->align('center', 'center');
            });
        }

        $image->text('almalki.sa', 600, 580, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(22);
            $font->color('666666');
            $font->align('center', 'center');
        });

        return $this->saveImage($image, "{$id}-og.jpg");
    }

    /**
     * Horizontal 1200x630 English — LinkedIn.
     */
    public function generateOgEn(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->manager->createImage(1200, 630)->fill('121212');

        $this->placeLogo($image, 600, 80, 50);

        $wrapped = wordwrap($title, 40, "\n", true);
        $image->text($wrapped, 600, 310, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(42);
            $font->color('ffffff');
            $font->align('center', 'center');
            $font->lineHeight(1.8);
        });

        if ($subtitle) {
            $image->text($subtitle, 600, 510, function (FontFactory $font) {
                $font->filename($this->fontPath);
                $font->size(20);
                $font->color('a0a0a0');
                $font->align('center', 'center');
            });
        }

        $image->text('almalki.sa', 600, 580, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(22);
            $font->color('666666');
            $font->align('center', 'center');
        });

        return $this->saveImage($image, "{$id}-og-en.jpg");
    }

    /**
     * Tall 1080x1350 (4:5) — Instagram + WhatsApp + Website.
     */
    public function generateTall(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->manager->createImage(1080, 1350)->fill('121212');

        $this->placeLogo($image, 540, 150, 65);

        $wrapped = wordwrap($title, 24, "\n", true);
        $image->text($this->shapeArabic($wrapped), 540, 620, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(52);
            $font->color('ffffff');
            $font->align('center', 'center');
            $font->lineHeight(2.0);
        });

        if ($subtitle) {
            $image->text($this->shapeArabic($subtitle), 540, 1050, function (FontFactory $font) {
                $font->filename($this->fontPath);
                $font->size(22);
                $font->color('a0a0a0');
                $font->align('center', 'center');
            });
        }

        $image->text('almalki.sa', 540, 1280, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(26);
            $font->color('666666');
            $font->align('center', 'center');
        });

        return $this->saveImage($image, "{$id}-tall.jpg");
    }

    /**
     * Tall 1080x1350 English — Website EN.
     */
    public function generateTallEn(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->manager->createImage(1080, 1350)->fill('121212');

        $this->placeLogo($image, 540, 150, 65);

        $wrapped = wordwrap($title, 28, "\n", true);
        $image->text($wrapped, 540, 620, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(48);
            $font->color('ffffff');
            $font->align('center', 'center');
            $font->lineHeight(2.0);
        });

        if ($subtitle) {
            $image->text($subtitle, 540, 1050, function (FontFactory $font) {
                $font->filename($this->fontPath);
                $font->size(22);
                $font->color('a0a0a0');
                $font->align('center', 'center');
            });
        }

        $image->text('almalki.sa', 540, 1280, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(26);
            $font->color('666666');
            $font->align('center', 'center');
        });

        return $this->saveImage($image, "{$id}-tall-en.jpg");
    }

    /**
     * Vertical 1080x1920 (9:16) — Snapchat Story.
     */
    public function generateStory(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->manager->createImage(1080, 1920)->fill('121212');

        $this->placeLogo($image, 540, 200, 70);

        $image->text($this->shapeArabic('تم إضافة خبر بعنوان :'), 540, 750, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(28);
            $font->color('a0a0a0');
            $font->align('center', 'center');
        });

        $wrapped = wordwrap($title, 20, "\n", true);
        $image->text($this->shapeArabic($wrapped), 540, 960, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(54);
            $font->color('ffffff');
            $font->align('center', 'center');
            $font->lineHeight(2.0);
        });

        $image->text($this->shapeArabic('في موقعي'), 540, 1200, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(28);
            $font->color('a0a0a0');
            $font->align('center', 'center');
        });

        $image->text('almalki.sa', 540, 1780, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(28);
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

        $this->placeLogo($image, 600, 250, 80);

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
