<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.site-settings';

    public array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'ar' ? 'النظام' : 'System';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'ar' ? 'إعدادات الموقع' : 'Site Settings';
    }

    public function getTitle(): string
    {
        return app()->getLocale() === 'ar' ? 'إعدادات الموقع' : 'Site Settings';
    }

    public function mount(): void
    {
        // Hydrate form state: strip /uploads/ prefix on image keys so FileUpload finds them on the disk
        $data = [];
        foreach (SiteSetting::all() as $setting) {
            $value = $setting->value;
            if ($setting->type === 'image' && is_string($value) && str_starts_with($value, '/uploads/')) {
                $value = ltrim(substr($value, strlen('/uploads/')), '/');
            }
            $data[$setting->key] = $value;
        }
        $this->data = $data;
        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        $isAr = fn () => app()->getLocale() === 'ar';
        $groups = [
            'branding' => $isAr() ? 'الهوية البصرية' : 'Branding',
            'identity' => $isAr() ? 'المعلومات الشخصية' : 'Identity',
            'contact' => $isAr() ? 'التواصل' : 'Contact',
            'social' => $isAr() ? 'وسائل التواصل' : 'Social Media',
            'seo' => $isAr() ? 'SEO' : 'SEO',
            'footer' => $isAr() ? 'الفوتر' : 'Footer',
            'about' => $isAr() ? 'نبذة عنّي' : 'About',
        ];

        $tabs = [];
        foreach ($groups as $groupKey => $groupLabel) {
            $fields = [];
            $settings = SiteSetting::where('group', $groupKey)->orderBy('id')->get();
            foreach ($settings as $setting) {
                foreach ($this->buildField($setting, $isAr()) as $component) {
                    $fields[] = $component;
                }
            }
            $tabs[] = Tab::make($groupLabel)->schema($fields);
        }

        return $schema
            ->components([
                Tabs::make('settings_tabs')->tabs($tabs)->columnSpanFull(),
            ])
            ->statePath('data');
    }

    /**
     * @return Component[]
     */
    protected function buildField(SiteSetting $setting, bool $isAr): array
    {
        $label = $isAr ? ($setting->label_ar ?: $setting->key) : ($setting->label_en ?: $setting->key);
        $key = $setting->key;
        $currentValue = $setting->value;

        return match ($setting->type) {
            'textarea' => [
                Textarea::make($key)->label($label)->rows(3)->columnSpanFull(),
            ],
            'url' => [TextInput::make($key)->label($label)->url()],
            'email' => [TextInput::make($key)->label($label)->email()],
            'image' => [
                Placeholder::make($key.'_preview')
                    ->label($label.' — '.($isAr ? 'المعاينة الحالية' : 'Current preview'))
                    ->content(fn () => $currentValue
                        ? new HtmlString('<img src="'.e($currentValue).'" style="max-width:280px;max-height:160px;border-radius:8px;border:1px solid rgba(255,255,255,0.12);display:block;">')
                        : new HtmlString('<span style="opacity:0.5">— '.($isAr ? 'لا توجد صورة' : 'no image').' —</span>')
                    )
                    ->columnSpanFull(),
                FileUpload::make($key)
                    ->label($isAr ? 'رفع صورة جديدة' : 'Upload new image')
                    ->disk('public_uploads')
                    ->directory('settings')
                    ->image()
                    ->imageEditor()
                    ->imagePreviewHeight('140')
                    ->maxSize(5120)
                    ->visibility('public')
                    ->columnSpanFull(),
            ],
            'boolean' => [Toggle::make($key)->label($label)],
            default => [TextInput::make($key)->label($label)],
        };
    }

    public function save(): void
    {
        $values = $this->form->getState();

        foreach ($values as $key => $value) {
            $setting = SiteSetting::where('key', $key)->first();
            if (! $setting) {
                continue;
            }

            // For image fields: normalize disk-relative path to absolute /uploads/... for DB storage
            if ($setting->type === 'image' && is_string($value) && $value !== '' && ! str_starts_with($value, '/')) {
                $value = '/uploads/'.ltrim($value, '/');
            }

            $setting->update(['value' => $value]);
        }

        SiteSetting::flushCache();

        Notification::make()
            ->success()
            ->title(app()->getLocale() === 'ar' ? 'تم حفظ الإعدادات' : 'Settings saved')
            ->send();

        // Reload so the preview placeholders pick up the new paths
        $this->mount();
    }
}
