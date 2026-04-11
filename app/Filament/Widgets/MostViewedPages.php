<?php

namespace App\Filament\Widgets;

use App\Models\PageVisit;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MostViewedPages extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = null;

    public function getHeading(): ?string
    {
        return app()->getLocale() === 'ar' ? 'الصفحات الأكثر زيارة (آخر 30 يوم)' : 'Most Viewed Pages (Last 30 Days)';
    }

    public function table(Table $table): Table
    {
        $isAr = app()->getLocale() === 'ar';

        return $table
            ->query(
                PageVisit::query()
                    ->fromSub(
                        PageVisit::query()
                            ->select('path', 'page_title', DB::raw('COUNT(*) as visit_count'), DB::raw('COUNT(DISTINCT ip_address) as unique_visitors'), DB::raw('MAX(id) as id'))
                            ->where('created_at', '>=', now()->subDays(30))
                            ->where('device', '!=', 'Bot')
                            ->groupBy('path', 'page_title'),
                        'page_visits'
                    )
            )
            ->columns([
                Tables\Columns\TextColumn::make('page_title')
                    ->label($isAr ? 'الصفحة' : 'Page')
                    ->icon('heroicon-o-document')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('path')
                    ->label($isAr ? 'الرابط' : 'Path')
                    ->color('gray')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('visit_count')
                    ->label($isAr ? 'الزيارات' : 'Views')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('unique_visitors')
                    ->label($isAr ? 'زوار فريدون' : 'Unique')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
            ])
            ->defaultSort('visit_count', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->striped();
    }
}
