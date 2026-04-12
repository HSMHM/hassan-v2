@php
    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
    $align = $locale === 'ar' ? 'right' : 'left';
    $font = "'Segoe UI', Tahoma, Arial, sans-serif";
@endphp

<x-emails.layouts.base :locale="$locale" :subject="$locale === 'ar' ? 'تنبيه أمني — تسجيل دخول' : 'Security Alert — Admin Login'"
    :badge="$locale === 'ar' ? 'تنبيه أمني' : 'SECURITY ALERT'"
    badge-color="#fff3e0" badge-text-color="#e65100"
    :preheader="$locale === 'ar' ? 'تم تسجيل دخول جديد إلى لوحة التحكم' : 'A new admin login was detected'">

    <h1 style="font-family: {{ $font }}; font-size: 20px; font-weight: 700; color: #111; margin: 0 0 8px; text-align: {{ $align }};">
        {{ $locale === 'ar' ? '🔐 تسجيل دخول جديد' : '🔐 New Admin Login' }}
    </h1>

    <p style="margin: 0 0 20px; color: #444; text-align: {{ $align }};">
        {{ $locale === 'ar'
            ? 'تم تسجيل دخول إلى لوحة التحكم. إذا لم تكن أنت، قم بتغيير كلمة المرور فوراً.'
            : 'A login to the admin panel was detected. If this was not you, change your password immediately.' }}
    </p>

    {{-- Login details --}}
    <table role="presentation" class="info-table" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 24px; background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
        <tr>
            <td style="padding: 16px 20px; border-bottom: 1px solid #e9ecef; direction: {{ $dir }};">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="info-label" style="font-family: {{ $font }}; font-size: 13px; font-weight: 700; color: #666; width: 120px; text-align: {{ $align }}; vertical-align: top;">
                            {{ $locale === 'ar' ? '👤 المستخدم' : '👤 User' }}
                        </td>
                        <td style="font-family: {{ $font }}; font-size: 14px; color: #333; text-align: {{ $align }};">
                            {{ $userName }} ({{ $userEmail }})
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 20px; border-bottom: 1px solid #e9ecef; direction: {{ $dir }};">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="info-label" style="font-family: {{ $font }}; font-size: 13px; font-weight: 700; color: #666; width: 120px; text-align: {{ $align }}; vertical-align: top;">
                            {{ $locale === 'ar' ? '🌐 العنوان IP' : '🌐 IP Address' }}
                        </td>
                        <td style="font-family: {{ $font }}; font-size: 14px; color: #333; text-align: {{ $align }};" dir="ltr">
                            {{ $ipAddress }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 20px; border-bottom: 1px solid #e9ecef; direction: {{ $dir }};">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="info-label" style="font-family: {{ $font }}; font-size: 13px; font-weight: 700; color: #666; width: 120px; text-align: {{ $align }}; vertical-align: top;">
                            {{ $locale === 'ar' ? '🖥️ المتصفح' : '🖥️ Browser' }}
                        </td>
                        <td style="font-family: {{ $font }}; font-size: 14px; color: #333; text-align: {{ $align }};">
                            {{ $userAgent }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 20px; direction: {{ $dir }};">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="info-label" style="font-family: {{ $font }}; font-size: 13px; font-weight: 700; color: #666; width: 120px; text-align: {{ $align }}; vertical-align: top;">
                            {{ $locale === 'ar' ? '🕐 الوقت' : '🕐 Time' }}
                        </td>
                        <td style="font-family: {{ $font }}; font-size: 14px; color: #333; text-align: {{ $align }};" dir="ltr">
                            {{ $loginAt }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Warning --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="background-color: #fff8e1; border: 1px solid #ffe082; border-radius: 8px; padding: 16px 20px; direction: {{ $dir }}; text-align: {{ $align }};">
                <p style="font-family: {{ $font }}; font-size: 13px; color: #6d4c00; margin: 0; line-height: 1.6;">
                    ⚠️ {{ $locale === 'ar'
                        ? 'إذا لم تكن أنت من قام بتسجيل الدخول، قم بتغيير كلمة المرور فوراً وتحقق من نشاط الحساب.'
                        : 'If you did not perform this login, change your password immediately and review account activity.' }}
                </p>
            </td>
        </tr>
    </table>

</x-emails.layouts.base>
