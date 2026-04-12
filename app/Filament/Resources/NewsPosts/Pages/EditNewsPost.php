<?php

namespace App\Filament\Resources\NewsPosts\Pages;

use App\Filament\Resources\NewsPosts\NewsPostResource;
use App\Jobs\PublishNewsJob;
use App\Services\NewsDiscoveryService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditNewsPost extends EditRecord
{
    protected static string $resource = NewsPostResource::class;

    protected function getHeaderActions(): array
    {
        $isAr = app()->getLocale() === 'ar';
        $status = $this->record?->status;

        $actions = [];

        // Discover new news — available for drafts/skipped/failed
        if (in_array($status, ['draft', 'skipped', 'failed'])) {
            $actions[] = Action::make('discoverNews')
                ->label($isAr ? '🔍 بحث عن خبر جديد' : '🔍 Find New Story')
                ->color('info')
                ->icon('heroicon-o-sparkles')
                ->requiresConfirmation()
                ->modalDescription($isAr
                    ? '⚠️ سيتم استبدال المحتوى الحالي. متأكد؟'
                    : '⚠️ Current content will be replaced. Sure?')
                ->action(function () use ($isAr) {
                    try {
                        $discovery = app(NewsDiscoveryService::class);
                        $items = $discovery->discoverNews();

                        if (empty($items)) {
                            Notification::make()->title($isAr ? 'لا توجد أخبار جديدة' : 'No new news')->warning()->send();

                            return;
                        }

                        $top = collect($items)->sortByDesc(fn ($i) => match ($i['significance'] ?? 'low') {
                            'high' => 3, 'medium' => 2, default => 1,
                        })->first();

                        $content = $discovery->generateContent($top);

                        $this->form->fill(array_merge($this->record->toArray(), $content, ['status' => 'draft']));

                        Notification::make()->title($isAr ? '✅ تم تحميل خبر جديد' : '✅ New content loaded')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title($isAr ? '❌ فشل' : '❌ Failed')->body($e->getMessage())->danger()->send();
                    }
                });
        }

        // Publish everywhere — for unpublished posts
        if (in_array($status, ['draft', 'pending', 'skipped'])) {
            $actions[] = Action::make('publishEverywhere')
                ->label($isAr ? '🚀 نشر وشارك' : '🚀 Publish & Share')
                ->color('primary')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->modalDescription($isAr
                    ? 'سيتم حفظ التعديلات ونشر الخبر على الموقع + جميع منصات التواصل'
                    : 'Changes will be saved and published to website + all social platforms')
                ->action(function () use ($isAr) {
                    $this->save();
                    $this->record->update([
                        'status' => 'publishing',
                        'approved_at' => now(),
                        'published_at' => now(),
                    ]);
                    PublishNewsJob::dispatch($this->record->id);

                    Notification::make()
                        ->title($isAr ? '🚀 جاري النشر...' : '🚀 Publishing...')
                        ->success()->send();
                });
        }

        // Share to social — published on website but not shared yet
        if ($status === 'published' && empty($this->record->platform_status)) {
            $actions[] = Action::make('shareToSocial')
                ->label($isAr ? '🚀 شارك على السوشال' : '🚀 Share on Social Media')
                ->color('primary')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'publishing']);
                    PublishNewsJob::dispatch($this->record->id);
                    Notification::make()->title('🚀 Sharing...')->success()->send();
                });
        }

        // Retry failed — for partial/failed
        if (in_array($status, ['failed', 'partial'])) {
            $failedPlatforms = collect($this->record->platform_status ?? [])
                ->filter(fn ($p) => ($p['status'] ?? '') !== 'published')
                ->keys()
                ->toArray();

            $actions[] = Action::make('retryFailed')
                ->label($isAr ? '🔄 أعد المحاولة' : '🔄 Retry Failed')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->action(function () use ($failedPlatforms) {
                    $this->record->update(['status' => 'publishing']);
                    PublishNewsJob::dispatch($this->record->id, $failedPlatforms);
                    Notification::make()->title('🔄 Retrying...')->success()->send();
                });
        }

        $actions[] = DeleteAction::make();

        return $actions;
    }

    protected function getFormActions(): array
    {
        $isAr = app()->getLocale() === 'ar';
        $status = $this->record?->status;

        $actions = [];

        // Draft states — 3 options
        if (in_array($status, ['draft', 'pending', 'skipped'])) {
            $actions[] = Action::make('saveAsDraft')
                ->label($isAr ? 'حفظ كمسودة' : 'Save Draft')
                ->color('gray')
                ->outlined()
                ->action(function () use ($isAr) {
                    $this->save();
                    $this->record->update(['status' => 'draft', 'published_at' => null]);
                    Notification::make()->title($isAr ? '📝 محفوظ' : '📝 Saved')->success()->send();
                });

            $actions[] = Action::make('publishToWebsite')
                ->label($isAr ? 'نشر في الموقع' : 'Publish to Website')
                ->color('success')
                ->action(function () use ($isAr) {
                    $this->save();
                    $this->record->update([
                        'status' => 'published',
                        'published_at' => $this->record->published_at ?? now(),
                    ]);
                    Notification::make()->title($isAr ? '✅ منشور في الموقع' : '✅ Published')->success()->send();
                });
        }

        // Published/shared states — save changes only
        if (in_array($status, ['published', 'partial', 'failed', 'publishing'])) {
            $actions[] = Action::make('saveChanges')
                ->label($isAr ? '💾 حفظ التعديلات' : '💾 Save Changes')
                ->color('success')
                ->action(function () use ($isAr) {
                    $this->save();
                    Notification::make()->title($isAr ? '💾 تم الحفظ' : '💾 Saved')->success()->send();
                });
        }

        return $actions;
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()->hidden();
    }
}
