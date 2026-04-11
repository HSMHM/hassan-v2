<?php

namespace App\Filament\Resources\Proposals\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProposalForm
{
    public static function configure(Schema $schema): Schema
    {
        $isAr = fn () => app()->getLocale() === 'ar';

        return $schema->components([
            Section::make($isAr() ? 'بيانات العرض' : 'Proposal Details')
                ->schema([
                    TextInput::make('proposal_id')
                        ->label($isAr() ? 'رقم العرض' : 'Proposal ID')
                        ->required()
                        ->maxLength(64),
                    TextInput::make('customer_name')
                        ->label($isAr() ? 'اسم العميل' : 'Customer Name')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label($isAr() ? 'الوصف' : 'Description')
                        ->rows(3)
                        ->columnSpanFull(),
                    Select::make('locale')
                        ->label($isAr() ? 'اللغة' : 'Locale')
                        ->options(['ar' => 'العربية', 'en' => 'English'])
                        ->default('ar')
                        ->required(),
                    TextInput::make('password')
                        ->label($isAr() ? 'كلمة المرور' : 'Password')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                        ->helperText($isAr() ? 'اتركه فارغاً للإبقاء على الحالي' : 'Leave blank to keep current'),
                    Toggle::make('is_active')
                        ->label($isAr() ? 'نشط' : 'Active')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }
}
