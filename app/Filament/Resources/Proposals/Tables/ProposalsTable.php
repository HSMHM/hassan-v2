<?php

namespace App\Filament\Resources\Proposals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProposalsTable
{
    public static function configure(Table $table): Table
    {
        $isAr = fn () => app()->getLocale() === 'ar';

        return $table
            ->columns([
                TextColumn::make('proposal_id')
                    ->label($isAr() ? 'رقم العرض' : 'ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label($isAr() ? 'العميل' : 'Customer')
                    ->searchable(),
                TextColumn::make('locale')
                    ->label($isAr() ? 'اللغة' : 'Locale')
                    ->badge(),
                IconColumn::make('is_active')
                    ->label($isAr() ? 'نشط' : 'Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label($isAr() ? 'تاريخ الإنشاء' : 'Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('locale')
                    ->options(['ar' => 'العربية', 'en' => 'English']),
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
