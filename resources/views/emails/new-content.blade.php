@php
    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
    $align = $locale === 'ar' ? 'right' : 'left';
    $font = "'Segoe UI', Tahoma, Arial, sans-serif";

    $typeLabels = [
        'article'   => $locale === 'ar' ? 'مقال' : 'Article',
        'workshop'  => $locale === 'ar' ? 'ورشة عمل' : 'Workshop',
        'portfolio' => $locale === 'ar' ? 'عمل' : 'Portfolio',
    ];
    $typeLabel = $typeLabels[$contentType] ?? $contentType;
@endphp

<x-mail::layouts.base :locale="$locale" :subject="($locale === 'ar' ? 'محتوى جديد — ' : 'New Content — ') . $typeLabel"
    :badge="($locale === 'ar' ? 'محتوى جديد: ' : 'NEW: ') . $typeLabel"
    badge-color="#e3f2fd" badge-text-color="#1565c0"
    :preheader="($locale === 'ar' ? 'تمت إضافة محتوى جديد: ' : 'New content published: ') . $contentTitle">

    <h1 style="font-family: {{ $font }}; font-size: 20px; font-weight: 700; color: #111; margin: 0 0 8px; text-align: {{ $align }};">
        {{ $locale === 'ar' ? "📝 {$typeLabel} جديد" : "📝 New {$typeLabel}" }}
    </h1>

    <p style="margin: 0 0 20px; color: #444; text-align: {{ $align }};">
        {{ $locale === 'ar'
            ? "تمت إضافة {$typeLabel} جديد إلى الموقع."
            : "A new {$typeLabel} has been added to the website." }}
    </p>

    {{-- Content card --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 24px; background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden;">
        @if($coverImage)
        <tr>
            <td style="padding: 0;">
                <img src="{{ $coverImage }}" alt="{{ $contentTitle }}" width="600" style="width: 100%; max-width: 600px; height: auto; display: block; border-radius: 8px 8px 0 0;" />
            </td>
        </tr>
        @endif
        <tr>
            <td style="padding: 20px; direction: {{ $dir }}; text-align: {{ $align }};">
                <h2 style="font-family: {{ $font }}; font-size: 18px; font-weight: 700; color: #111; margin: 0 0 8px;">
                    {{ $contentTitle }}
                </h2>
                @if($contentExcerpt)
                <p style="font-family: {{ $font }}; font-size: 14px; color: #555; margin: 0 0 16px; line-height: 1.6;">
                    {{ Str::limit($contentExcerpt, 200) }}
                </p>
                @endif

                {{-- Details --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="font-family: {{ $font }}; font-size: 13px; color: #666; padding: 4px 0;">
                            📂 <strong>{{ $locale === 'ar' ? 'النوع:' : 'Type:' }}</strong> {{ $typeLabel }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-family: {{ $font }}; font-size: 13px; color: #666; padding: 4px 0;" dir="ltr">
                            🕐 <strong>{{ $locale === 'ar' ? 'التاريخ:' : 'Date:' }}</strong> {{ $createdAt }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- CTA --}}
    @if($adminUrl)
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
        <tr>
            <td align="center" style="border-radius: 8px; background-color: #0b0b10;">
                <a href="{{ $adminUrl }}" target="_blank" style="display: inline-block; font-family: {{ $font }}; font-size: 14px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 8px;">
                    {{ $locale === 'ar' ? '🔗 عرض في لوحة التحكم' : '🔗 View in Admin Panel' }}
                </a>
            </td>
        </tr>
    </table>
    @endif

</x-mail::layouts.base>
