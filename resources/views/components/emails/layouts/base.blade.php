@php
    $locale = $locale ?? 'ar';
    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
    $align = $locale === 'ar' ? 'right' : 'left';
    $font = "'Segoe UI', Tahoma, Arial, sans-serif";
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="color-scheme" content="light dark" />
    <meta name="supported-color-schemes" content="light dark" />
    <title>{{ $subject ?? config('app.name') }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; padding: 12px !important; }
            .email-body { padding: 24px 20px !important; }
            .email-header { padding: 20px !important; }
            .info-table td { display: block !important; width: 100% !important; padding: 6px 0 !important; }
            .info-label { min-width: auto !important; }
        }
        @media (prefers-color-scheme: dark) {
            .email-body { background-color: #1a1a2e !important; }
            .email-outer { background-color: #0b0b10 !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f0f2f5; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: 100%;">

{{-- Preheader --}}
<div style="display: none; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #f0f2f5;">
    {{ $preheader ?? '' }}
    &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847; &#847;
</div>

<table role="presentation" class="email-outer" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f0f2f5;">
    <tr>
        <td align="center" style="padding: 32px 16px;">
            <table role="presentation" class="email-container" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08);">

                {{-- Header --}}
                <tr>
                    <td class="email-header" align="center" style="background: linear-gradient(135deg, #0b0b10 0%, #1a1a2e 100%); padding: 28px 32px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center">
                                    <div style="font-family: {{ $font }}; font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: 0.5px;">
                                        {{ $locale === 'ar' ? 'حسان المالكي' : 'Hassan Almalki' }}
                                    </div>
                                    <div style="font-family: {{ $font }}; font-size: 12px; color: #a0a0a0; margin-top: 4px; text-transform: uppercase; letter-spacing: 2px;">
                                        {{ $locale === 'ar' ? 'إشعار تلقائي' : 'AUTOMATED NOTIFICATION' }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Badge / Type --}}
                @isset($badge)
                <tr>
                    <td align="center" style="background-color: #ffffff; padding: 20px 32px 0;">
                        <div style="display: inline-block; background-color: {{ $badgeColor ?? '#e8e8e8' }}; color: {{ $badgeTextColor ?? '#333333' }}; font-family: {{ $font }}; font-size: 12px; font-weight: 700; padding: 5px 14px; border-radius: 20px; text-transform: uppercase; letter-spacing: 1px;">
                            {{ $badge }}
                        </div>
                    </td>
                </tr>
                @endisset

                {{-- Body --}}
                <tr>
                    <td class="email-body" style="background-color: #ffffff; padding: 32px; font-family: {{ $font }}; font-size: 15px; line-height: 1.7; color: #333333; direction: {{ $dir }}; text-align: {{ $align }};">
                        {{ $slot }}
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td align="center" style="background-color: #0b0b10; padding: 24px 32px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center" style="font-family: {{ $font }}; font-size: 12px; color: #666666; line-height: 1.6;">
                                    <span style="color: #a0a0a0;">{{ config('app.name') }}</span>
                                    <br />
                                    <a href="{{ config('app.url') }}" style="color: #a0a0a0; text-decoration: none;">{{ str_replace(['https://', 'http://'], '', config('app.url')) }}</a>
                                    <br />
                                    <span style="color: #555555; font-size: 11px;">
                                        {{ $locale === 'ar' ? 'هذا بريد تلقائي — لا تقم بالرد عليه.' : 'This is an automated message — please do not reply.' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
