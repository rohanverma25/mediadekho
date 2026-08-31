@extends('emails.layout')

@section('subject', 'Reset Your Password')

@section('content')
    <h2 style="margin:0 0 12px; font-size:18px; color:#0f172a;">Hi {{ $user->name }},</h2>
    <p style="margin:0 0 20px; font-size:13px; color:#334155; line-height:1.6;">
        We received a request to reset the password for your {{ config('app.name') }} account. Click the button below to choose a new one. This link expires in 60 minutes.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0">
        <tr>
            <td style="border-radius:10px; background-color:#d81a2b;">
                <a href="{{ $resetUrl }}" target="_blank" style="display:inline-block; padding:12px 28px; font-size:13px; font-weight:bold; color:#ffffff; text-decoration:none;">
                    Reset Password
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:20px 0 0; font-size:12px; color:#94a3b8; line-height:1.6;">
        If you didn't request this, you can safely ignore this email — your password won't be changed.
    </p>
@endsection
