<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        $isAr = fn () => app()->getLocale() === 'ar';

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label($isAr() ? 'الاسم' : 'Name')
                    ->searchable()
                    ->weight(fn ($record) => $record->is_read ? null : 'bold'),
                TextColumn::make('email')
                    ->label($isAr() ? 'البريد' : 'Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('mobile')
                    ->label($isAr() ? 'الجوال' : 'Mobile')
                    ->toggleable(),
                TextColumn::make('locale')
                    ->label($isAr() ? 'اللغة' : 'Locale')
                    ->badge(),
                IconColumn::make('is_read')
                    ->label($isAr() ? 'مقروءة' : 'Read')
                    ->boolean(),
                IconColumn::make('is_spam')
                    ->label($isAr() ? 'سبام' : 'Spam')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label($isAr() ? 'التاريخ' : 'Received')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_read')
                    ->label($isAr() ? 'مقروءة' : 'Read'),
                TernaryFilter::make('is_spam')
                    ->label($isAr() ? 'سبام' : 'Spam'),
            ])
            ->recordActions([
                EditAction::make()->label($isAr() ? 'عرض' : 'View'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
