@php
    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
    $align = $locale === 'ar' ? 'right' : 'left';
    $font = "'Segoe UI', Tahoma, Arial, sans-serif";
@endphp

<x-emails.layouts.base :locale="$locale" :subject="$locale === 'ar' ? 'شكراً لتواصلك' : 'Thank you for reaching out'"
    :badge="$locale === 'ar' ? 'تم الاستلام' : 'RECEIVED'"
    badge-color="#e8f5e9" badge-text-color="#2e7d32"
    :preheader="$locale === 'ar' ? 'شكراً لتواصلك — سنرد عليك قريباً' : 'Thank you for reaching out — we will reply soon'">

    {{-- Greeting --}}
    <h1 style="font-family: {{ $font }}; font-size: 20px; font-weight: 700; color: #111; margin: 0 0 8px; text-align: {{ $align }};">
        {{ $locale === 'ar' ? "مرحباً {$senderName}،" : "Hello {$senderName}," }}
    </h1>

    <p style="margin: 0 0 20px; color: #444; text-align: {{ $align }};">
        {{ $locale === 'ar'
            ? 'شكراً لتواصلك. لقد استلمت رسالتك وسأقوم بالرد عليك في أقرب وقت ممكن.'
            : 'Thank you for getting in touch. I have received your message and will get back to you as soon as possible.' }}
    </p>

    {{-- Message summary card --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 24px;">
        <tr>
            <td style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; direction: {{ $dir }}; text-align: {{ $align }};">
                <div style="font-family: {{ $font }}; font-size: 13px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                    {{ $locale === 'ar' ? '📋 ملخص رسالتك' : '📋 Your Message Summary' }}
                </div>
                <div style="font-family: {{ $font }}; font-size: 14px; color: #333; line-height: 1.7; white-space: pre-line;">{{ $body }}</div>
            </td>
        </tr>
    </table>

    {{-- Call to action --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="border-top: 1px solid #e9ecef; padding-top: 20px; text-align: {{ $align }};">
                <p style="font-family: {{ $font }}; font-size: 14px; color: #666; margin: 0 0 4px;">
                    {{ $locale === 'ar' ? 'يمكنك زيارة موقعي لمعرفة المزيد:' : 'Visit my website to learn more:' }}
                </p>
                <a href="{{ config('app.url') }}" style="font-family: {{ $font }}; font-size: 14px; color: #0b0b10; font-weight: 600; text-decoration: underline;">
                    {{ str_replace(['https://', 'http://'], '', config('app.url')) }}
                </a>
            </td>
        </tr>
    </table>

    <p style="margin: 24px 0 0; font-family: {{ $font }}; font-size: 15px; color: #333; text-align: {{ $align }};">
        {{ $locale === 'ar' ? 'مع أطيب التحيات،' : 'Best regards,' }}<br />
        <strong>{{ $locale === 'ar' ? 'حسان المالكي' : 'Hassan Almalki' }}</strong>
    </p>

</x-emails.layouts.base>
