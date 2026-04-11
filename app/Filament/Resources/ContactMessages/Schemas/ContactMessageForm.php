<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        $isAr = fn () => app()->getLocale() === 'ar';

        return $schema->components([
            Section::make($isAr() ? 'بيانات المرسل' : 'Sender Information')
                ->schema([
                    TextInput::make('name')
                        ->label($isAr() ? 'الاسم' : 'Name')
                        ->disabled(),
                    TextInput::make('email')
                        ->label($isAr() ? 'البريد الإلكتروني' : 'Email')
                        ->disabled(),
                    TextInput::make('mobile')
                        ->label($isAr() ? 'الجوال' : 'Mobile')
                        ->disabled(),
                    TextInput::make('locale')
                        ->label($isAr() ? 'اللغة' : 'Locale')
                        ->disabled(),
                ])
                ->columns(2),

            Section::make($isAr() ? 'الرسالة' : 'Message')
                ->schema([
                    Textarea::make('message')
                        ->label($isAr() ? 'الرسالة' : 'Message')
                        ->disabled()
                        ->rows(8)
                        ->columnSpanFull(),
                ]),

            Section::make($isAr() ? 'البيانات التقنية' : 'Technical Data')
                ->schema([
                    TextInput::make('ip_address')
                        ->label('IP')
                        ->disabled(),
                    TextInput::make('user_agent')
                        ->label('User Agent')
                        ->disabled(),
                    Toggle::make('is_read')
                        ->label($isAr() ? 'مقروءة' : 'Read'),
                    Toggle::make('is_spam')
                        ->label($isAr() ? 'سبام' : 'Spam'),
                ])
                ->columns(2)
                ->collapsed(),
        ]);
    }
}
