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
            Section::make($isAr() ? 'المصدر' : 'Source')
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
                ])
                ->columns(3)
                ->collapsible(),

            Tabs::make('Content')
                ->tabs([
                    Tab::make($isAr() ? 'العربية' : 'Arabic')
                        ->schema([
                            TextInput::make('title_ar')->label($isAr() ? 'العنوان (عربي)' : 'Title (Arabic)')->required()->columnSpanFull(),
                            TextInput::make('slug_ar')->label('Slug (AR)')->required()->unique(ignoreRecord: true),
                            Textarea::make('excerpt_ar')->label($isAr() ? 'المقتطف (عربي)' : 'Excerpt (Arabic)')->required()->rows(2)->columnSpanFull(),
                            RichEditor::make('content_ar')->label($isAr() ? 'المحتوى (عربي)' : 'Content (Arabic)')->required()->columnSpanFull(),
                            Textarea::make('social_post_ar')->label($isAr() ? 'منشور تواصل (عربي)' : 'Social Post (Arabic)')->required()->rows(3)->maxLength(280)->columnSpanFull()->helperText('max 280 chars'),
                            TextInput::make('meta_title_ar')->label('Meta Title (AR)'),
                            Textarea::make('meta_description_ar')->label('Meta Description (AR)')->rows(2)->columnSpanFull(),
                        ]),
                    Tab::make($isAr() ? 'الإنجليزية' : 'English')
                        ->schema([
                            TextInput::make('title_en')->label($isAr() ? 'العنوان (إنجليزي)' : 'Title (English)')->required()->columnSpanFull(),
                            TextInput::make('slug_en')->label('Slug (EN)')->required()->unique(ignoreRecord: true),
                            Textarea::make('excerpt_en')->label($isAr() ? 'المقتطف (إنجليزي)' : 'Excerpt (English)')->required()->rows(2)->columnSpanFull(),
                            RichEditor::make('content_en')->label($isAr() ? 'المحتوى (إنجليزي)' : 'Content (English)')->required()->columnSpanFull(),
                            Textarea::make('social_post_en')->label($isAr() ? 'منشور تواصل (إنجليزي)' : 'Social Post (English)')->required()->rows(3)->maxLength(280)->columnSpanFull()->helperText('max 280 chars'),
                            TextInput::make('meta_title_en')->label('Meta Title (EN)'),
                            Textarea::make('meta_description_en')->label('Meta Description (EN)')->rows(2)->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),

            Section::make($isAr() ? 'الصور والحالة' : 'Image & Status')
                ->schema([
                    TextInput::make('cover_image')->label($isAr() ? 'صورة الغلاف' : 'Cover Image URL'),
                    TextInput::make('og_image')->label('OG Image URL'),
                    Select::make('status')
                        ->label($isAr() ? 'الحالة' : 'Status')
                        ->options([
                            'draft' => $isAr() ? 'مسودة' : 'Draft',
                            'pending' => $isAr() ? 'بانتظار الموافقة' : 'Pending',
                            'approved' => $isAr() ? 'موافق عليه' : 'Approved',
                            'publishing' => $isAr() ? 'قيد النشر' : 'Publishing',
                            'published' => $isAr() ? 'منشور' : 'Published',
                            'partial' => $isAr() ? 'جزئي' : 'Partial',
                            'skipped' => $isAr() ? 'متجاوز' : 'Skipped',
                            'failed' => $isAr() ? 'فشل' : 'Failed',
                        ])
                        ->default('draft')
                        ->required(),
                    DateTimePicker::make('sent_to_whatsapp_at')->label($isAr() ? 'أُرسل لواتساب' : 'Sent to WhatsApp'),
                    DateTimePicker::make('approved_at')->label($isAr() ? 'الموافقة' : 'Approved'),
                    DateTimePicker::make('published_at')->label($isAr() ? 'النشر' : 'Published'),
                ])
                ->columns(2),
        ]);
    }
}
