@extends('emails.layout')

@section('subject', "We've received your media listing request")

@section('content')
    <h2 style="margin:0 0 12px; font-size:18px; color:#0f172a;">Thanks, {{ $mediaListingRequest->contact_name }}!</h2>
    <p style="margin:0; font-size:13px; color:#334155; line-height:1.6;">
        We've received your details for <strong>{{ $mediaListingRequest->media_title }}</strong>. Our team will review it and reach out to get it listed on {{ config('app.name') }}.
    </p>
@endsection
