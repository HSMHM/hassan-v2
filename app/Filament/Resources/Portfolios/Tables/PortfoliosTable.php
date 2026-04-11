<?php

namespace App\Filament\Resources\Portfolios\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PortfoliosTable
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
                TextColumn::make('category')
                    ->label($isAr() ? 'التصنيف' : 'Category')
                    ->badge(),
                TextColumn::make('sort_order')
                    ->label($isAr() ? 'الترتيب' : 'Order')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label($isAr() ? 'منشور' : 'Published')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label($isAr() ? 'آخر تحديث' : 'Updated')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
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
