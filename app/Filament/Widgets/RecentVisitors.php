<?php

namespace App\Filament\Widgets;

use App\Models\PageVisit;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentVisitors extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = null;

    public function getHeading(): ?string
    {
        return app()->getLocale() === 'ar' ? 'آخر الزوار' : 'Recent Visitors';
    }

    public function table(Table $table): Table
    {
        $isAr = app()->getLocale() === 'ar';

        return $table
            ->query(
                PageVisit::query()
                    ->orderByDesc('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label($isAr ? 'التاريخ والوقت' : 'Date & Time')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->size('sm')
                    ->icon('heroicon-o-clock'),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label($isAr ? 'عنوان IP' : 'IP Address')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->copyMessage($isAr ? 'تم النسخ' : 'Copied'),

                Tables\Columns\TextColumn::make('path')
                    ->label($isAr ? 'الصفحة' : 'Page')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->path),

                Tables\Columns\TextColumn::make('browser')
                    ->label($isAr ? 'المتصفح' : 'Browser')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Chrome' => 'success',
                        'Safari' => 'info',
                        'Firefox' => 'warning',
                        'Edge' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('platform')
                    ->label($isAr ? 'النظام' : 'OS')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Windows' => 'info',
                        'macOS' => 'gray',
                        'iOS', 'iPadOS' => 'primary',
                        'Android' => 'success',
                        'Linux' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('device')
                    ->label($isAr ? 'الجهاز' : 'Device')
                    ->badge()
                    ->icon(fn (?string $state): string => match ($state) {
                        'Mobile' => 'heroicon-o-device-phone-mobile',
                        'Tablet' => 'heroicon-o-device-tablet',
                        'Bot' => 'heroicon-o-cpu-chip',
                        default => 'heroicon-o-computer-desktop',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'Mobile' => 'warning',
                        'Tablet' => 'info',
                        'Bot' => 'danger',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('locale')
                    ->label($isAr ? 'اللغة' : 'Lang')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('referer')
                    ->label($isAr ? 'المصدر' : 'Referer')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->referer)
                    ->placeholder($isAr ? 'مباشر' : 'Direct')
                    ->color('gray')
                    ->size('sm'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->striped()
            ->filters([
                Tables\Filters\SelectFilter::make('device')
                    ->label($isAr ? 'الجهاز' : 'Device')
                    ->options([
                        'Desktop' => 'Desktop',
                        'Mobile' => 'Mobile',
                        'Tablet' => 'Tablet',
                        'Bot' => 'Bot',
                    ]),
                Tables\Filters\SelectFilter::make('browser')
                    ->label($isAr ? 'المتصفح' : 'Browser')
                    ->options(
                        PageVisit::query()
                            ->whereNotNull('browser')
                            ->distinct()
                            ->pluck('browser', 'browser')
                            ->toArray()
                    ),
                Tables\Filters\SelectFilter::make('platform')
                    ->label($isAr ? 'النظام' : 'Platform')
                    ->options(
                        PageVisit::query()
                            ->whereNotNull('platform')
                            ->distinct()
                            ->pluck('platform', 'platform')
                            ->toArray()
                    ),
            ]);
    }
}
