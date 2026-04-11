<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>{{ $locale === 'ar' ? 'حسان المالكي — المقالات' : 'Hassan Almalki — Articles' }}</title>
    <link>{{ config('app.url') }}</link>
    <description>{{ $locale === 'ar' ? 'أحدث المقالات التقنية من حسان المالكي' : 'Latest technical articles from Hassan Almalki' }}</description>
    <language>{{ $locale }}</language>
    <atom:link href="{{ $selfUrl }}" rel="self" type="application/rss+xml"/>
    @foreach ($items as $item)
    <item>
        <title>{{ $item['title'] }}</title>
        <link>{{ $item['link'] }}</link>
        <description><![CDATA[{{ $item['description'] }}]]></description>
        @if ($item['pubDate'])
        <pubDate>{{ $item['pubDate'] }}</pubDate>
        @endif
        <guid isPermaLink="true">{{ $item['link'] }}</guid>
    </item>
    @endforeach
</channel>
</rss>
