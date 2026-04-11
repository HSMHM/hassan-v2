<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Component;
use Illuminate\Support\HtmlString;

/**
 * Reusable image-upload field that:
 *  - Uploads to the `public_uploads` disk (→ public/uploads/<directory>)
 *  - Stores an absolute public path like `/uploads/<directory>/<file>` in the DB
 *    so frontend `<img src>` works directly without any URL resolver
 *  - Shows a live preview of the current image (works for both new /uploads/... and legacy /img/... paths)
 *  - Shows the native Filament image thumbnail in the uploader for new uploads
 */
class ImageField
{
    public static function make(string $name, string $directory, ?string $label = null, bool $required = false): array
    {
        $upload = FileUpload::make($name)
            ->label($label ?? $name)
            ->disk('public_uploads')
            ->directory($directory)
            ->image()
            ->imageEditor()
            ->maxSize(5120)
            ->imagePreviewHeight('180')
            ->visibility('public')
            // DB ← Filament: prepend /uploads/ so the stored path is an absolute public URL
            ->dehydrateStateUsing(function ($state) {
                if (! $state) {
                    return null;
                }
                if (is_string($state) && str_starts_with($state, '/')) {
                    return $state; // already absolute
                }

                return '/uploads/'.ltrim((string) $state, '/');
            })
            // DB → Filament: strip the /uploads/ prefix so FileUpload finds the file on its disk.
            // Legacy /img/... paths return null here so FileUpload shows "no file" — the Placeholder
            // below still renders the current image as a visual preview.
            ->formatStateUsing(function ($state) {
                if (! $state) {
                    return null;
                }
                if (is_string($state) && str_starts_with($state, '/uploads/')) {
                    return ltrim(substr($state, strlen('/uploads/')), '/');
                }

                return null;
            });

        if ($required) {
            $upload->required();
        }

        $preview = Placeholder::make($name.'_preview')
            ->label(app()->getLocale() === 'ar' ? 'المعاينة الحالية' : 'Current preview')
            ->content(function ($record) use ($name) {
                $value = $record?->{$name};
                if (! $value) {
                    return new HtmlString('<span style="opacity:0.5">— '.(app()->getLocale() === 'ar' ? 'لا توجد صورة' : 'no image').' —</span>');
                }

                return new HtmlString(
                    '<img src="'.e($value).'" style="max-width:320px;max-height:200px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);display:block;">'
                );
            });

        return [$preview, $upload];
    }
}
