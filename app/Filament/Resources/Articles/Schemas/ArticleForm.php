<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Filament\Support\ImageField;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        $isAr = fn () => app()->getLocale() === 'ar';

        return $schema->components([
            Tabs::make('Article')
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
                            Textarea::make('excerpt_ar')
                                ->label($isAr() ? 'المقتطف (عربي)' : 'Excerpt (Arabic)')
                                ->rows(3)
                                ->columnSpanFull(),
                            RichEditor::make('content_ar')
                                ->label($isAr() ? 'المحتوى (عربي)' : 'Content (Arabic)')
                                ->required()
                                ->columnSpanFull(),

                            TextInput::make('extras.reading_time_ar')
                                ->label($isAr() ? 'وقت القراءة (عربي)' : 'Reading Time (Arabic)')
                                ->placeholder($isAr() ? 'مثال: 6 دقائق قراءة' : 'e.g. 6 دقائق قراءة'),
                            TagsInput::make('extras.takeaways_ar')
                                ->label($isAr() ? 'أهم النقاط (عربي)' : 'Key Takeaways (Arabic)')
                                ->placeholder($isAr() ? 'أضف نقطة واضغط Enter' : 'Add a takeaway and press Enter')
                                ->columnSpanFull(),
                            TagsInput::make('extras.tags_ar')
                                ->label($isAr() ? 'الوسوم (عربي)' : 'Tags (Arabic)')
                                ->columnSpanFull(),

                            TextInput::make('meta_title_ar')
                                ->label('Meta Title (AR)')
                                ->maxLength(255),
                            Textarea::make('meta_description_ar')
                                ->label('Meta Description (AR)')
                                ->rows(2)
                                ->columnSpanFull(),
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
                            Textarea::make('excerpt_en')
                                ->label($isAr() ? 'المقتطف (إنجليزي)' : 'Excerpt (English)')
                                ->rows(3)
                                ->columnSpanFull(),
                            RichEditor::make('content_en')
                                ->label($isAr() ? 'المحتوى (إنجليزي)' : 'Content (English)')
                                ->required()
                                ->columnSpanFull(),

                            TextInput::make('extras.reading_time_en')
                                ->label($isAr() ? 'وقت القراءة (إنجليزي)' : 'Reading Time (English)')
                                ->placeholder('e.g. 6 min read'),
                            TagsInput::make('extras.takeaways_en')
                                ->label($isAr() ? 'أهم النقاط (إنجليزي)' : 'Key Takeaways (English)')
                                ->placeholder('Add a takeaway and press Enter')
                                ->columnSpanFull(),
                            TagsInput::make('extras.tags_en')
                                ->label($isAr() ? 'الوسوم (إنجليزي)' : 'Tags (English)')
                                ->columnSpanFull(),

                            TextInput::make('meta_title_en')
                                ->label('Meta Title (EN)')
                                ->maxLength(255),
                            Textarea::make('meta_description_en')
                                ->label('Meta Description (EN)')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),

            Section::make($isAr() ? 'صورة الغلاف (عربي)' : 'Cover Image (Arabic)')
                ->schema(ImageField::make(
                    'cover_image',
                    'articles',
                    $isAr() ? 'صورة الغلاف (عربي)' : 'Cover Image (Arabic)'
                )),

            Section::make($isAr() ? 'صورة الغلاف (إنجليزي)' : 'Cover Image (English)')
                ->schema(ImageField::make(
                    'cover_image_en',
                    'articles',
                    $isAr() ? 'صورة الغلاف (إنجليزي)' : 'Cover Image (English)'
                )),

            Section::make($isAr() ? 'المراجع' : 'References')
                ->description($isAr() ? 'روابط خارجية تظهر في نهاية المقال' : 'External links displayed at the end of the article')
                ->schema([
                    Repeater::make('extras.references')
                        ->label('')
                        ->schema([
                            TextInput::make('title')
                                ->label($isAr() ? 'العنوان' : 'Title')
                                ->required(),
                            TextInput::make('url')
                                ->label($isAr() ? 'الرابط' : 'URL')
                                ->url()
                                ->required(),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel($isAr() ? 'إضافة مرجع' : 'Add reference')
                        ->reorderable()
                        ->collapsible(),
                ])
                ->collapsed(),

            Section::make($isAr() ? 'الإعدادات' : 'Settings')
                ->schema([
                    Toggle::make('is_published')
                        ->label($isAr() ? 'منشور' : 'Published')
                        ->default(false),
                    DateTimePicker::make('published_at')
                        ->label($isAr() ? 'تاريخ النشر' : 'Publish Date'),
                ])
                ->columns(2),
        ]);
    }
}
