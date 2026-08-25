@extends('emails.layout')

@section('subject', "We've received your award nomination")

@section('content')
    <h2 style="margin:0 0 12px; font-size:18px; color:#0f172a;">Thanks, {{ $nomination->name }}!</h2>
    <p style="margin:0 0 16px; font-size:13px; color:#334155; line-height:1.6;">
        We've received your nomination for <strong>{{ $nomination->award?->title ?? 'this award' }}</strong>. Our team will review it and reach out if we need anything further.
    </p>

    <p style="margin:0 0 4px; font-size:12px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Your Submission</p>
    <p style="margin:0; font-size:13px; color:#1e293b; white-space:pre-wrap;">{{ $nomination->description }}</p>
@endsection
