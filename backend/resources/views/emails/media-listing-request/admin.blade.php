@extends('emails.layout')

@section('subject', 'New Media Listing Request')

@section('content')
    <h2 style="margin:0 0 4px; font-size:18px; color:#0f172a;">New Media Listing Request</h2>
    <p style="margin:0 0 16px; font-size:13px; color:#64748b;">Submitted {{ $mediaListingRequest->created_at->format('M j, Y g:i A') }}</p>

    @include('emails.partials.facts-table', ['rows' => [
        'Company' => $mediaListingRequest->company_name,
        'Contact Name' => $mediaListingRequest->contact_name,
        'Email' => $mediaListingRequest->email,
        'Phone' => $mediaListingRequest->phone,
        'Media / Property' => $mediaListingRequest->media_title,
        'Media Type' => $mediaListingRequest->media_type,
        'Location' => $mediaListingRequest->location,
        'Approximate Rate' => $mediaListingRequest->approximate_rate,
        'Media Kit' => $mediaListingRequest->media_kit ? 'Attached' : 'Not provided',
    ]])

    @if ($mediaListingRequest->description)
        <p style="margin:16px 0 4px; font-size:12px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Additional Details</p>
        <p style="margin:0; font-size:13px; color:#1e293b; white-space:pre-wrap;">{{ $mediaListingRequest->description }}</p>
    @endif

    @if ($mediaListingRequest->image_url)
        <p style="margin:16px 0 4px; font-size:12px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Photo</p>
        <img src="{{ $mediaListingRequest->image_url }}" alt="Media photo" width="240" style="border-radius:8px; border:1px solid #e2e8f0;">
    @endif
@endsection
