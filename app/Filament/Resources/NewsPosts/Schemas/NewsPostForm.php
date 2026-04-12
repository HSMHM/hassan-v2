<?php

namespace App\Filament\Resources\NewsPosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class NewsPostForm
{
    public static function configure(Schema $schema): Schema
    {
        $isAr = fn () => app()->getLocale() === 'ar';

        return $schema->components([

            // Quick Overview — titles + social posts (most important for review)
            Section::make($isAr() ? '📋 نظرة سريعة' : '📋 Quick Overview')
                ->description($isAr()
                    ? 'راجع العناوين ومنشورات التواصل الاجتماعي قبل النشر'
                    : 'Review titles and social posts before publishing')
                ->schema([
                    TextInput::make('title_ar')
                        ->label($isAr() ? 'العنوان بالعربي' : 'Arabic Title')
                        ->required()
                        ->columnSpanFull()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug_ar', \Str::slug($state))),
                    TextInput::make('title_en')
                        ->label($isAr() ? 'العنوان بالإنجليزي' : 'English Title')
                        ->required()
                        ->columnSpanFull()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug_en', \Str::slug($state))),
                    Textarea::make('social_post_ar')
                        ->label($isAr() ? '🐦 منشور تويتر (عربي)' : '🐦 Tweet (Arabic)')
                        ->required()
                        ->rows(3)
                        ->maxLength(280)
                        ->helperText($isAr() ? 'الحد الأقصى 280 حرف' : 'Max 280 characters')
                        ->columnSpan(1),
                    Textarea::make('social_post_en')
                        ->label($isAr() ? '🐦 منشور تويتر (إنجليزي)' : '🐦 Tweet (English)')
                        ->required()
                        ->rows(3)
                        ->maxLength(280)
                        ->helperText($isAr() ? 'الحد الأقصى 280 حرف' : 'Max 280 characters')
                        ->columnSpan(1),
                ])
                ->columns(2),

            // Detailed Content (tabs)
            Tabs::make('DetailedContent')
                ->tabs([
                    Tab::make($isAr() ? '📝 المقالة بالعربي' : '📝 Arabic Article')
                        ->icon('heroicon-o-language')
                        ->schema([
                            Textarea::make('excerpt_ar')
                                ->label($isAr() ? 'المقتطف' : 'Excerpt')
                                ->required()
                                ->rows(2)
                                ->columnSpanFull(),
                            RichEditor::make('content_ar')
                                ->label($isAr() ? 'المحتوى الكامل' : 'Full Content')
                                ->required()
                                ->columnSpanFull(),
                        ]),

                    Tab::make($isAr() ? '📝 المقالة بالإنجليزي' : '📝 English Article')
                        ->icon('heroicon-o-language')
                        ->schema([
                            Textarea::make('excerpt_en')
                                ->label($isAr() ? 'المقتطف' : 'Excerpt')
                                ->required()
                                ->rows(2)
                                ->columnSpanFull(),
                            RichEditor::make('content_en')
                                ->label($isAr() ? 'المحتوى الكامل' : 'Full Content')
                                ->required()
                                ->columnSpanFull(),
                        ]),

                    Tab::make($isAr() ? '🔍 SEO' : '🔍 SEO')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            TextInput::make('slug_ar')->label('Slug (AR)')->required()->unique(ignoreRecord: true),
                            TextInput::make('slug_en')->label('Slug (EN)')->required()->unique(ignoreRecord: true),
                            TextInput::make('meta_title_ar')->label('Meta Title (AR)'),
                            TextInput::make('meta_title_en')->label('Meta Title (EN)'),
                            Textarea::make('meta_description_ar')->label('Meta Description (AR)')->rows(2)->columnSpanFull(),
                            Textarea::make('meta_description_en')->label('Meta Description (EN)')->rows(2)->columnSpanFull(),
                        ])->columns(2),

                    Tab::make($isAr() ? '📎 المصدر' : '📎 Source')
                        ->icon('heroicon-o-link')
                        ->schema([
                            TextInput::make('source_url')->label($isAr() ? 'رابط المصدر' : 'Source URL')->url(),
                            TextInput::make('source_title')->label($isAr() ? 'عنوان المصدر' : 'Source Title'),
                            Select::make('source_type')
                                ->label($isAr() ? 'نوع المصدر' : 'Source Type')
                                ->options([
                                    'blog' => 'Blog',
                                    'youtube' => 'YouTube',
                                    'twitter' => 'Twitter / X',
                                    'docs' => 'Docs',
                                    'news' => 'News',
                                ]),
                        ])->columns(3),
                ])
                ->columnSpanFull(),

            // Status section (collapsed by default)
            Section::make($isAr() ? '⚙️ الحالة' : '⚙️ Status')
                ->schema([
                    Select::make('status')
                        ->label($isAr() ? 'الحالة' : 'Status')
                        ->options([
                            'draft' => $isAr() ? '📝 مسودة' : '📝 Draft',
                            'pending' => $isAr() ? '⏳ بانتظار الموافقة' : '⏳ Pending',
                            'publishing' => $isAr() ? '🔄 قيد النشر' : '🔄 Publishing',
                            'published' => $isAr() ? '✅ منشور' : '✅ Published',
                            'partial' => $isAr() ? '⚠️ نشر جزئي' : '⚠️ Partial',
                            'failed' => $isAr() ? '❌ فشل' : '❌ Failed',
                            'skipped' => $isAr() ? '⏭️ متجاوز' : '⏭️ Skipped',
                        ])
                        ->default('draft')
                        ->required()
                        ->disabled(),
                    TextInput::make('cover_image')->label($isAr() ? 'صورة الغلاف' : 'Cover Image'),
                    TextInput::make('og_image')->label('OG Image'),
                    DateTimePicker::make('published_at')->label($isAr() ? 'تاريخ النشر' : 'Published At'),
                    DateTimePicker::make('approved_at')->label($isAr() ? 'تاريخ الموافقة' : 'Approved At'),
                ])
                ->columns(2)
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
