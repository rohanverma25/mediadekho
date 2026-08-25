@extends('emails.layout')

@section('subject', "We've received your enquiry")

@section('content')
    <h2 style="margin:0 0 12px; font-size:18px; color:#0f172a;">Thanks, {{ $lead->name }}!</h2>
    <p style="margin:0 0 16px; font-size:13px; color:#334155; line-height:1.6;">
        We've received your enquiry and a member of our team will get back to you shortly.
    </p>

    @if ($lead->subject)
        <p style="margin:0 0 4px; font-size:12px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Subject</p>
        <p style="margin:0 0 12px; font-size:13px; color:#1e293b;">{{ $lead->subject }}</p>
    @endif

    <p style="margin:0 0 4px; font-size:12px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Your Message</p>
    <p style="margin:0; font-size:13px; color:#1e293b; white-space:pre-wrap;">{{ $lead->description }}</p>
@endsection
