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

        $logo = $this->manager->read($this->logoPath);

        // Scale logo to desired height, keep aspect ratio
        $ratio = $height / $logo->height();
        $logo->resize((int) ($logo->width() * $ratio), $height);

        // Center the logo
        $x = $centerX - (int) ($logo->width() / 2);
        $y = $centerY - (int) ($logo->height() / 2);

        $image->place($logo, 'top-left', $x, $y);
    }

    /**
     * Generate the horizontal 1200x630 Open Graph / Instagram card.
     */
    public function generateOg(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->manager->createImage(1200, 630)->fill('121212');

        // Logo at top center
        $this->placeLogo($image, 600, 75, 50);

        $wrapped = wordwrap($title, 35, "\n", true);
        $image->text($this->shapeArabic($wrapped), 600, 315, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(44);
            $font->color('ffffff');
            $font->align('center', 'center');
            $font->lineHeight(1.5);
        });

        if ($subtitle) {
            $image->text($this->shapeArabic($subtitle), 600, 520, function (FontFactory $font) {
                $font->filename($this->fontPath);
                $font->size(20);
                $font->color('a0a0a0');
                $font->align('center', 'center');
            });
        }

        $image->text('almalki.sa', 600, 590, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(16);
            $font->color('555555');
            $font->align('center', 'center');
        });

        return $this->saveImage($image, "{$id}-og.jpg");
    }

    /**
     * Generate the English version of the OG image (1200x630).
     */
    public function generateOgEn(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->manager->createImage(1200, 630)->fill('121212');

        // Logo at top center
        $this->placeLogo($image, 600, 75, 50);

        $wrapped = wordwrap($title, 40, "\n", true);
        $image->text($wrapped, 600, 315, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(42);
            $font->color('ffffff');
            $font->align('center', 'center');
            $font->lineHeight(1.5);
        });

        if ($subtitle) {
            $image->text($subtitle, 600, 520, function (FontFactory $font) {
                $font->filename($this->fontPath);
                $font->size(20);
                $font->color('a0a0a0');
                $font->align('center', 'center');
            });
        }

        $image->text('almalki.sa', 600, 590, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(16);
            $font->color('555555');
            $font->align('center', 'center');
        });

        return $this->saveImage($image, "{$id}-og-en.jpg");
    }

    /**
     * Generate a vertical 1080x1920 story image (Snapchat / WhatsApp Status).
     */
    public function generateStory(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->manager->createImage(1080, 1920)->fill('121212');

        // Logo at top center
        $this->placeLogo($image, 540, 170, 60);

        // "تم اضافة خبر بعنوان :" above the title
        $image->text($this->shapeArabic('تم إضافة خبر بعنوان :'), 540, 750, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(28);
            $font->color('a0a0a0');
            $font->align('center', 'center');
        });

        $wrapped = wordwrap($title, 22, "\n", true);
        $image->text($this->shapeArabic($wrapped), 540, 920, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(56);
            $font->color('ffffff');
            $font->align('center', 'center');
            $font->lineHeight(1.5);
        });

        // "في موقعي" below the title
        $image->text($this->shapeArabic('في موقعي'), 540, 1100, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(28);
            $font->color('a0a0a0');
            $font->align('center', 'center');
        });

        $image->text('almalki.sa', 540, 1780, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(24);
            $font->color('555555');
            $font->align('center', 'center');
        });

        return $this->saveImage($image, "{$id}-story.jpg");
    }

    /**
     * Generate the site-wide default OG image.
     */
    public function generateDefault(): string
    {
        $image = $this->manager->createImage(1200, 630)->fill('121212');

        // Logo centered
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
