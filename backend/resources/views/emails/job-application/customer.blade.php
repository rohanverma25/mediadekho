@extends('emails.layout')

@section('subject', "We've received your application")

@section('content')
    <h2 style="margin:0 0 12px; font-size:18px; color:#0f172a;">Thanks, {{ $application->name }}!</h2>
    <p style="margin:0; font-size:13px; color:#334155; line-height:1.6;">
        We've received your application for <strong>{{ $application->job?->title ?? 'this role' }}</strong>. Our team will review it and get back to you if it's a good fit.
    </p>
@endsection
