@php
    $setting = \App\Models\Setting::current();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('subject', config('app.name'))</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:Arial, Helvetica, sans-serif; color:#1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:560px;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="background-color:#d81a2b; padding:20px 28px; border-radius:12px 12px 0 0;">
                            <span style="font-size:20px; font-weight:900; color:#ffffff; letter-spacing:0.5px;">{{ config('app.name') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff; padding:28px; border:1px solid #e2e8f0; border-top:none;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff; padding:16px 28px 24px; border:1px solid #e2e8f0; border-top:none; border-radius:0 0 12px 12px;">
                            <hr style="border:none; border-top:1px solid #e2e8f0; margin:0 0 16px;">
                            <p style="margin:0; font-size:11px; color:#94a3b8; line-height:1.6;">
                                {{ config('app.name') }}
                                @if ($setting->contact_phone) &middot; {{ $setting->contact_phone }} @endif
                                @if ($setting->contact_email) &middot; {{ $setting->contact_email }} @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
