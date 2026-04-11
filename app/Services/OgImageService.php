<?php

namespace App\Services;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;

class OgImageService
{
    private ImageManager $manager;

    private string $fontPath;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
        $this->fontPath = storage_path('app/fonts/Cairo.ttf');
    }

    /**
     * Generate the horizontal 1200x630 Open Graph / Instagram card.
     * Returns a public-relative path like /uploads/og/123-og.jpg.
     */
    public function generateOg(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->manager->createImage(1200, 630)->fill('121212');

        $image->text('Hassan Almalki  |  حسان المالكي', 600, 85, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(22);
            $font->color('757575');
            $font->align('center', 'center');
        });

        $wrapped = wordwrap($title, 35, "\n", true);
        $image->text($wrapped, 600, 315, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(44);
            $font->color('ffffff');
            $font->align('center', 'center');
            $font->lineHeight(1.5);
        });

        if ($subtitle) {
            $image->text($subtitle, 600, 540, function (FontFactory $font) {
                $font->filename($this->fontPath);
                $font->size(20);
                $font->color('a0a0a0');
                $font->align('center', 'center');
            });
        }

        return $this->saveImage($image, "{$id}-og.jpg");
    }

    /**
     * Generate a vertical 1080x1920 story image (Snapchat / WhatsApp Status).
     */
    public function generateStory(string $title, ?string $subtitle, int|string $id): string
    {
        $image = $this->manager->createImage(1080, 1920)->fill('121212');

        $image->text('Hassan Almalki', 540, 180, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(34);
            $font->color('757575');
            $font->align('center', 'center');
        });

        $wrapped = wordwrap($title, 22, "\n", true);
        $image->text($wrapped, 540, 900, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(56);
            $font->color('ffffff');
            $font->align('center', 'center');
            $font->lineHeight(1.5);
        });

        $image->text($subtitle ?: 'almalki.sa', 540, 1780, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(30);
            $font->color('a0a0a0');
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

        $image->text('حسان المالكي', 600, 240, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(72);
            $font->color('ffffff');
            $font->align('center', 'center');
        });

        $image->text('Hassan Almalki', 600, 335, function (FontFactory $font) {
            $font->filename($this->fontPath);
            $font->size(38);
            $font->color('a0a0a0');
            $font->align('center', 'center');
        });

        $image->text('مطور تطبيقات ويب ومدير منتجات تقنية', 600, 430, function (FontFactory $font) {
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
