<?php

namespace App\Filament\Resources\Workshops\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class WorkshopsTable
{
    public static function configure(Table $table): Table
    {
        $isAr = fn () => app()->getLocale() === 'ar';
        $titleColumn = $isAr() ? 'title_ar' : 'title_en';

        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('')
                    ->getStateUsing(fn ($record) => $record->cover_image ? asset(ltrim($record->cover_image, '/')) : null)
                    ->square()
                    ->size(60),
                TextColumn::make($titleColumn)
                    ->label($isAr() ? 'العنوان' : 'Title')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('event_date')
                    ->label($isAr() ? 'التاريخ' : 'Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('platform')
                    ->label($isAr() ? 'المنصة' : 'Platform')
                    ->badge(),
                IconColumn::make('is_published')
                    ->label($isAr() ? 'منشور' : 'Published')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label($isAr() ? 'آخر تحديث' : 'Updated')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('event_date', 'desc')
            ->filters([
                TernaryFilter::make('is_published')
                    ->label($isAr() ? 'حالة النشر' : 'Published'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
