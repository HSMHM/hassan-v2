<?php

namespace App\Filament\Resources\Portfolios\Schemas;

use App\Filament\Support\ImageField;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PortfolioForm
{
    public static function configure(Schema $schema): Schema
    {
        $isAr = fn () => app()->getLocale() === 'ar';

        return $schema->components([
            Tabs::make('Portfolio')
                ->tabs([
                    Tab::make($isAr() ? 'العربية' : 'Arabic')
                        ->schema([
                            TextInput::make('title_ar')
                                ->label($isAr() ? 'العنوان (عربي)' : 'Title (Arabic)')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set) => $set('slug_ar', Str::slug($state))),
                            TextInput::make('slug_ar')
                                ->label('Slug (AR)')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            Textarea::make('description_ar')
                                ->label($isAr() ? 'الوصف (عربي)' : 'Description (Arabic)')
                                ->rows(3)
                                ->columnSpanFull(),
                            RichEditor::make('content_ar')
                                ->label($isAr() ? 'المحتوى (عربي)' : 'Content (Arabic)')
                                ->columnSpanFull(),
                            TextInput::make('meta_title_ar')->label('Meta Title (AR)')->maxLength(255),
                            Textarea::make('meta_description_ar')->label('Meta Description (AR)')->rows(2)->columnSpanFull(),
                        ]),
                    Tab::make($isAr() ? 'الإنجليزية' : 'English')
                        ->schema([
                            TextInput::make('title_en')
                                ->label($isAr() ? 'العنوان (إنجليزي)' : 'Title (English)')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set) => $set('slug_en', Str::slug($state))),
                            TextInput::make('slug_en')
                                ->label('Slug (EN)')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            Textarea::make('description_en')
                                ->label($isAr() ? 'الوصف (إنجليزي)' : 'Description (English)')
                                ->rows(3)
                                ->columnSpanFull(),
                            RichEditor::make('content_en')
                                ->label($isAr() ? 'المحتوى (إنجليزي)' : 'Content (English)')
                                ->columnSpanFull(),
                            TextInput::make('meta_title_en')->label('Meta Title (EN)')->maxLength(255),
                            Textarea::make('meta_description_en')->label('Meta Description (EN)')->rows(2)->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),

            Section::make($isAr() ? 'صورة الغلاف' : 'Cover Image')
                ->schema(ImageField::make(
                    'cover_image',
                    'portfolio',
                    $isAr() ? 'صورة الغلاف' : 'Cover Image'
                )),

            Section::make($isAr() ? 'الإعدادات' : 'Settings')
                ->schema([
                    TextInput::make('category')
                        ->label($isAr() ? 'التصنيف (عربي)' : 'Category (Arabic)'),
                    TextInput::make('category_en')
                        ->label($isAr() ? 'التصنيف (إنجليزي)' : 'Category (English)'),
                    TextInput::make('project_url')
                        ->label($isAr() ? 'رابط المشروع' : 'Project URL')
                        ->url()
                        ->columnSpanFull(),
                    TextInput::make('sort_order')
                        ->label($isAr() ? 'الترتيب' : 'Sort Order')
                        ->numeric()
                        ->default(0),
                    Toggle::make('is_published')
                        ->label($isAr() ? 'منشور' : 'Published')
                        ->default(false),
                ])
                ->columns(2),
        ]);
    }
}
