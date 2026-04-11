<!DOCTYPE html>
<html lang="{{ $locale ?? 'ar' }}" dir="{{ ($locale ?? 'ar') === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>New Contact Form Submission</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; color: #111;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 30px; border: 1px solid #d9d9d9;">
        <h2 style="margin-top: 0;">📩 New Contact Form Submission</h2>
        <hr style="border: none; border-top: 1px solid #d9d9d9;">
        <table cellpadding="8" cellspacing="0" width="100%" style="border-collapse: collapse;">
            <tr>
                <td><strong>Name:</strong></td>
                <td>{{ $senderName }}</td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td><a href="mailto:{{ $senderEmail }}" style="color: #111;">{{ $senderEmail }}</a></td>
            </tr>
            @if($senderMobile)
                <tr>
                    <td><strong>Mobile:</strong></td>
                    <td dir="ltr">{{ $senderMobile }}</td>
                </tr>
            @endif
            <tr>
                <td><strong>Locale:</strong></td>
                <td>{{ $locale }}</td>
            </tr>
            <tr>
                <td><strong>Sent at:</strong></td>
                <td>{{ $sentAt }}</td>
            </tr>
            <tr>
                <td><strong>IP:</strong></td>
                <td>{{ $ip }}</td>
            </tr>
        </table>
        <hr style="border: none; border-top: 1px solid #d9d9d9;">
        <h3>Message:</h3>
        <div style="background: #f4f4f4; padding: 15px; border-radius: 6px; line-height: 1.6;">
            {!! nl2br(e($body)) !!}
        </div>
    </div>
</body>
</html>
