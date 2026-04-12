@php
    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
    $align = $locale === 'ar' ? 'right' : 'left';
    $font = "'Segoe UI', Tahoma, Arial, sans-serif";
@endphp

<x-emails.layouts.base :locale="$locale" :subject="$locale === 'ar' ? 'رسالة تواصل جديدة' : 'New Contact Form Submission'"
    :badge="$locale === 'ar' ? 'رسالة جديدة' : 'NEW MESSAGE'"
    badge-color="#e8f5e9" badge-text-color="#2e7d32"
    :preheader="($locale === 'ar' ? 'رسالة جديدة من ' : 'New message from ') . $senderName">

    <h1 style="font-family: {{ $font }}; font-size: 20px; font-weight: 700; color: #111; margin: 0 0 8px; text-align: {{ $align }};">
        {{ $locale === 'ar' ? '📩 رسالة تواصل جديدة' : '📩 New Contact Message' }}
    </h1>

    <p style="margin: 0 0 20px; color: #444; text-align: {{ $align }};">
        {{ $locale === 'ar'
            ? "تم استلام رسالة جديدة من {$senderName} عبر نموذج التواصل."
            : "A new message was received from {$senderName} via the contact form." }}
    </p>

    {{-- Sender info --}}
    <table role="presentation" class="info-table" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 20px; background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
        <tr>
            <td style="padding: 14px 20px; border-bottom: 1px solid #e9ecef; direction: {{ $dir }};">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="info-label" style="font-family: {{ $font }}; font-size: 13px; font-weight: 700; color: #666; width: 100px; text-align: {{ $align }};">
                            {{ $locale === 'ar' ? '👤 الاسم' : '👤 Name' }}
                        </td>
                        <td style="font-family: {{ $font }}; font-size: 14px; color: #333; text-align: {{ $align }};">
                            {{ $senderName }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 14px 20px; border-bottom: 1px solid #e9ecef; direction: {{ $dir }};">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="info-label" style="font-family: {{ $font }}; font-size: 13px; font-weight: 700; color: #666; width: 100px; text-align: {{ $align }};">
                            {{ $locale === 'ar' ? '📧 البريد' : '📧 Email' }}
                        </td>
                        <td style="font-family: {{ $font }}; font-size: 14px; color: #333; text-align: {{ $align }};">
                            <a href="mailto:{{ $senderEmail }}" style="color: #1565c0; text-decoration: none;">{{ $senderEmail }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @if($senderMobile)
        <tr>
            <td style="padding: 14px 20px; border-bottom: 1px solid #e9ecef; direction: {{ $dir }};">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="info-label" style="font-family: {{ $font }}; font-size: 13px; font-weight: 700; color: #666; width: 100px; text-align: {{ $align }};">
                            {{ $locale === 'ar' ? '📱 الجوال' : '📱 Mobile' }}
                        </td>
                        <td style="font-family: {{ $font }}; font-size: 14px; color: #333; text-align: {{ $align }};" dir="ltr">
                            {{ $senderMobile }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endif
        <tr>
            <td style="padding: 14px 20px; border-bottom: 1px solid #e9ecef; direction: {{ $dir }};">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="info-label" style="font-family: {{ $font }}; font-size: 13px; font-weight: 700; color: #666; width: 100px; text-align: {{ $align }};">
                            {{ $locale === 'ar' ? '🌐 اللغة' : '🌐 Locale' }}
                        </td>
                        <td style="font-family: {{ $font }}; font-size: 14px; color: #333; text-align: {{ $align }};">
                            {{ $locale === 'ar' ? 'عربي' : 'English' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 14px 20px; direction: {{ $dir }};">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="info-label" style="font-family: {{ $font }}; font-size: 13px; font-weight: 700; color: #666; width: 100px; text-align: {{ $align }};">
                            🕐 {{ $locale === 'ar' ? 'الوقت' : 'Time' }}
                        </td>
                        <td style="font-family: {{ $font }}; font-size: 14px; color: #333; text-align: {{ $align }};" dir="ltr">
                            {{ $sentAt }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Message body --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 20px;">
        <tr>
            <td style="direction: {{ $dir }}; text-align: {{ $align }};">
                <div style="font-family: {{ $font }}; font-size: 13px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                    {{ $locale === 'ar' ? '💬 الرسالة' : '💬 Message' }}
                </div>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 16px 20px; direction: {{ $dir }}; text-align: {{ $align }};">
                <div style="font-family: {{ $font }}; font-size: 14px; color: #333; line-height: 1.7; white-space: pre-line;">{{ $body }}</div>
            </td>
        </tr>
    </table>

    {{-- Technical info --}}
    <p style="font-family: {{ $font }}; font-size: 12px; color: #999; margin: 0; text-align: {{ $align }};">
        IP: {{ $ip }}
    </p>

</x-emails.layouts.base>
