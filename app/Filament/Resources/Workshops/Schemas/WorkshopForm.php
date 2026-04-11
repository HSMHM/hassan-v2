<?php

namespace App\Filament\Resources\Workshops\Schemas;

use App\Filament\Support\ImageField;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class WorkshopForm
{
    public static function configure(Schema $schema): Schema
    {
        $isAr = fn () => app()->getLocale() === 'ar';

        return $schema->components([
            Tabs::make('Workshop')
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
                            TextInput::make('location_ar')
                                ->label($isAr() ? 'الموقع (عربي)' : 'Location (Arabic)'),

                            TextInput::make('extras.duration_ar')
                                ->label($isAr() ? 'المدة (عربي)' : 'Duration (Arabic)')
                                ->placeholder($isAr() ? 'مثال: ساعتان' : 'e.g. 2 hours'),
                            TagsInput::make('extras.objectives_ar')
                                ->label($isAr() ? 'الأهداف التعليمية (عربي)' : 'Learning Objectives (Arabic)')
                                ->placeholder($isAr() ? 'أضف هدفاً واضغط Enter' : 'Add an objective and press Enter')
                                ->columnSpanFull(),
                            TagsInput::make('extras.audience_ar')
                                ->label($isAr() ? 'الفئة المستهدفة (عربي)' : 'Target Audience (Arabic)')
                                ->columnSpanFull(),
                            TagsInput::make('extras.topics_ar')
                                ->label($isAr() ? 'المحاور (عربي)' : 'Topics (Arabic)')
                                ->columnSpanFull(),
                            TagsInput::make('extras.outcomes_ar')
                                ->label($isAr() ? 'المخرجات المتوقعة (عربي)' : 'Expected Outcomes (Arabic)')
                                ->columnSpanFull(),

                            TextInput::make('meta_title_ar')->label('Meta Title (AR)'),
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
                            TextInput::make('location_en')
                                ->label($isAr() ? 'الموقع (إنجليزي)' : 'Location (English)'),

                            TextInput::make('extras.duration_en')
                                ->label($isAr() ? 'المدة (إنجليزي)' : 'Duration (English)')
                                ->placeholder('e.g. 2 hours'),
                            TagsInput::make('extras.objectives_en')
                                ->label($isAr() ? 'الأهداف التعليمية (إنجليزي)' : 'Learning Objectives (English)')
                                ->placeholder('Add an objective and press Enter')
                                ->columnSpanFull(),
                            TagsInput::make('extras.audience_en')
                                ->label($isAr() ? 'الفئة المستهدفة (إنجليزي)' : 'Target Audience (English)')
                                ->columnSpanFull(),
                            TagsInput::make('extras.topics_en')
                                ->label($isAr() ? 'المحاور (إنجليزي)' : 'Topics (English)')
                                ->columnSpanFull(),
                            TagsInput::make('extras.outcomes_en')
                                ->label($isAr() ? 'المخرجات المتوقعة (إنجليزي)' : 'Expected Outcomes (English)')
                                ->columnSpanFull(),

                            TextInput::make('meta_title_en')->label('Meta Title (EN)'),
                            Textarea::make('meta_description_en')->label('Meta Description (EN)')->rows(2)->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),

            Section::make($isAr() ? 'الإعدادات' : 'Settings')
                ->schema([
                    DatePicker::make('event_date')
                        ->label($isAr() ? 'تاريخ الورشة' : 'Event Date'),
                    TextInput::make('platform')
                        ->label($isAr() ? 'المنصة (عربي)' : 'Platform (Arabic)'),
                    TextInput::make('platform_en')
                        ->label($isAr() ? 'المنصة (إنجليزي)' : 'Platform (English)'),
                    TextInput::make('video_url')
                        ->label($isAr() ? 'رابط الفيديو' : 'Video URL')
                        ->url()
                        ->columnSpanFull(),
                    Toggle::make('is_published')
                        ->label($isAr() ? 'منشور' : 'Published')
                        ->default(false),
                ])
                ->columns(3),

            Section::make($isAr() ? 'صورة الغلاف (عربي)' : 'Cover Image (Arabic)')
                ->schema(ImageField::make(
                    'cover_image',
                    'workshops',
                    $isAr() ? 'صورة الغلاف (عربي)' : 'Cover Image (Arabic)'
                )),

            Section::make($isAr() ? 'صورة الغلاف (إنجليزي)' : 'Cover Image (English)')
                ->schema(ImageField::make(
                    'cover_image_en',
                    'workshops',
                    $isAr() ? 'صورة الغلاف (إنجليزي)' : 'Cover Image (English)'
                )),
        ]);
    }
}
