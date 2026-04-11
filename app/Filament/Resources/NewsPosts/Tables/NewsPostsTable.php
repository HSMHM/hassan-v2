<?php

namespace App\Filament\Resources\NewsPosts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NewsPostsTable
{
    public static function configure(Table $table): Table
    {
        $isAr = fn () => app()->getLocale() === 'ar';
        $headlineColumn = $isAr() ? 'title_ar' : 'title_en';

        return $table
            ->columns([
                TextColumn::make($headlineColumn)
                    ->label($isAr() ? 'العنوان' : 'Headline')
                    ->searchable()
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('source_title')
                    ->label($isAr() ? 'المصدر' : 'Source')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('source_type')
                    ->label($isAr() ? 'النوع' : 'Type')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label($isAr() ? 'الحالة' : 'Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'info',
                        'rejected' => 'danger',
                        'published' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('sent_to_whatsapp_at')
                    ->label($isAr() ? 'أُرسل' : 'Sent')
                    ->since()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label($isAr() ? 'النشر' : 'Published')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label($isAr() ? 'الحالة' : 'Status')
                    ->options([
                        'draft' => $isAr() ? 'مسودة' : 'Draft',
                        'pending' => $isAr() ? 'بانتظار الموافقة' : 'Pending',
                        'approved' => $isAr() ? 'موافق عليه' : 'Approved',
                        'rejected' => $isAr() ? 'مرفوض' : 'Rejected',
                        'published' => $isAr() ? 'منشور' : 'Published',
                        'failed' => $isAr() ? 'فشل' : 'Failed',
                    ]),
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
