<?php

namespace App\Filament\Resources\NewsPosts\Pages;

use App\Filament\Resources\NewsPosts\NewsPostResource;
use App\Jobs\PublishNewsJob;
use App\Services\NewsDiscoveryService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsPost extends CreateRecord
{
    protected static string $resource = NewsPostResource::class;

    protected function getHeaderActions(): array
    {
        $isAr = app()->getLocale() === 'ar';

        return [
            Action::make('discoverNews')
                ->label($isAr ? '🔍 ابحث عن خبر جديد' : '🔍 Find Latest News')
                ->color('info')
                ->icon('heroicon-o-sparkles')
                ->size('lg')
                ->extraAttributes(['class' => 'w-full sm:w-auto'])
                ->requiresConfirmation()
                ->modalHeading($isAr ? 'البحث عن أخبار Claude' : 'Search for Claude News')
                ->modalDescription($isAr
                    ? 'Claude سيبحث في الإنترنت عن آخر أخبار Claude AI ويعبّئ النموذج تلقائياً'
                    : 'Claude will search the web for the latest Claude AI news and auto-fill the form')
                ->modalSubmitActionLabel($isAr ? '🔍 ابحث' : '🔍 Search')
                ->action(function () use ($isAr) {
                    try {
                        Notification::make()
                            ->title($isAr ? '🔍 جاري البحث...' : '🔍 Searching...')
                            ->info()
                            ->send();

                        $discovery = app(NewsDiscoveryService::class);
                        $items = $discovery->discoverNews();

                        if (empty($items)) {
                            Notification::make()
                                ->title($isAr ? 'لا توجد أخبار جديدة' : 'No new news found')
                                ->body($isAr ? 'حاول مرة أخرى لاحقاً' : 'Try again later')
                                ->warning()
                                ->duration(5000)
                                ->send();

                            return;
                        }

                        $top = collect($items)->sortByDesc(fn ($i) => match ($i['significance'] ?? 'low') {
                            'high' => 3, 'medium' => 2, default => 1,
                        })->first();

                        $content = $discovery->generateContent($top);

                        $this->form->fill([
                            'source_url' => $content['source_url'] ?? '',
                            'source_title' => $content['source_title'] ?? '',
                            'source_type' => $content['source_type'] ?? 'blog',
                            'title_ar' => $content['title_ar'] ?? '',
                            'title_en' => $content['title_en'] ?? '',
                            'slug_ar' => $content['slug_ar'] ?? '',
                            'slug_en' => $content['slug_en'] ?? '',
                            'excerpt_ar' => $content['excerpt_ar'] ?? '',
                            'excerpt_en' => $content['excerpt_en'] ?? '',
                            'content_ar' => $content['content_ar'] ?? '',
                            'content_en' => $content['content_en'] ?? '',
                            'social_post_ar' => $content['social_post_ar'] ?? '',
                            'social_post_en' => $content['social_post_en'] ?? '',
                            'meta_title_ar' => $content['meta_title_ar'] ?? '',
                            'meta_title_en' => $content['meta_title_en'] ?? '',
                            'meta_description_ar' => $content['meta_description_ar'] ?? '',
                            'meta_description_en' => $content['meta_description_en'] ?? '',
                            'references' => $content['references'] ?? [],
                            'status' => 'draft',
                        ]);

                        Notification::make()
                            ->title($isAr ? '✅ تم العثور على خبر!' : '✅ News found!')
                            ->body($isAr ? 'راجع المحتوى ثم اختر الإجراء المناسب' : 'Review content then choose an action')
                            ->success()
                            ->duration(5000)
                            ->send();

                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title($isAr ? '❌ فشل البحث' : '❌ Search failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function getFormActions(): array
    {
        $isAr = app()->getLocale() === 'ar';

        return [
            Action::make('saveAsDraft')
                ->label($isAr ? 'حفظ كمسودة' : 'Save Draft')
                ->color('gray')
                ->outlined()
                ->action(function () use ($isAr) {
                    $data = $this->form->getState();
                    $data['status'] = 'draft';
                    $data['published_at'] = null;

                    $record = static::getModel()::create($data);

                    Notification::make()
                        ->title($isAr ? '📝 محفوظ كمسودة' : '📝 Saved as draft')
                        ->success()->send();

                    $this->redirect(NewsPostResource::getUrl('edit', ['record' => $record]));
                }),

            Action::make('publishToWebsite')
                ->label($isAr ? 'نشر في الموقع' : 'Publish to Website')
                ->color('success')
                ->action(function () use ($isAr) {
                    $data = $this->form->getState();
                    $data['status'] = 'published';
                    $data['published_at'] = now();

                    $record = static::getModel()::create($data);

                    Notification::make()
                        ->title($isAr ? '✅ منشور في الموقع' : '✅ Published to website')
                        ->body($isAr ? 'يظهر الآن في صفحة المقالات' : 'Now visible on the articles page')
                        ->success()->send();

                    $this->redirect(NewsPostResource::getUrl('edit', ['record' => $record]));
                }),

            Action::make('publishEverywhere')
                ->label($isAr ? '🚀 نشر وشارك' : '🚀 Publish & Share')
                ->color('primary')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->modalHeading($isAr ? 'نشر على جميع المنصات' : 'Publish everywhere')
                ->modalDescription($isAr
                    ? "سيتم نشر الخبر في الموقع ومشاركته على:\nTwitter · Instagram · LinkedIn · Snapchat · WhatsApp Status"
                    : "Will publish to your website and share on:\nTwitter · Instagram · LinkedIn · Snapchat · WhatsApp Status")
                ->modalSubmitActionLabel($isAr ? '🚀 نشر الآن' : '🚀 Publish Now')
                ->action(function () use ($isAr) {
                    $data = $this->form->getState();
                    $data['status'] = 'publishing';
                    $data['approved_at'] = now();
                    $data['published_at'] = now();

                    $record = static::getModel()::create($data);

                    PublishNewsJob::dispatch($record->id);

                    Notification::make()
                        ->title($isAr ? '🚀 جاري النشر...' : '🚀 Publishing...')
                        ->body($isAr ? 'ستصلك إشعار على Telegram عند الانتهاء' : 'You\'ll be notified on Telegram when done')
                        ->success()->send();

                    $this->redirect(NewsPostResource::getUrl('edit', ['record' => $record]));
                }),
        ];
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()->hidden();
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()->hidden();
    }
}
