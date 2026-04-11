<?php

namespace App\Filament\Resources\Proposals;

use App\Filament\Resources\Proposals\Pages\CreateProposal;
use App\Filament\Resources\Proposals\Pages\EditProposal;
use App\Filament\Resources\Proposals\Pages\ListProposals;
use App\Filament\Resources\Proposals\Schemas\ProposalForm;
use App\Filament\Resources\Proposals\Tables\ProposalsTable;
use App\Models\Proposal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProposalResource extends Resource
{
    protected static ?string $model = Proposal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'ar' ? 'النظام' : 'System';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'ar' ? 'العروض' : 'Proposals';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'ar' ? 'عرض' : 'Proposal';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'ar' ? 'العروض' : 'Proposals';
    }

    public static function form(Schema $schema): Schema
    {
        return ProposalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProposalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProposals::route('/'),
            'create' => CreateProposal::route('/create'),
            'edit' => EditProposal::route('/{record}/edit'),
        ];
    }
}
