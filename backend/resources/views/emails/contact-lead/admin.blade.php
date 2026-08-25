@extends('emails.layout')

@section('subject', 'New Enquiry')

@section('content')
    <h2 style="margin:0 0 4px; font-size:18px; color:#0f172a;">New Enquiry Received</h2>
    <p style="margin:0 0 16px; font-size:13px; color:#64748b;">Submitted {{ $lead->created_at->format('M j, Y g:i A') }}</p>

    @include('emails.partials.facts-table', ['rows' => [
        'Name' => $lead->name,
        'Email' => $lead->email,
        'Phone' => $lead->phone,
        'Company' => $lead->company_name,
        'Location' => $lead->location,
        'Subject' => $lead->subject,
        'Account' => $lead->user?->name,
    ]])

    <p style="margin:16px 0 4px; font-size:12px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Message</p>
    <p style="margin:0; font-size:13px; color:#1e293b; white-space:pre-wrap;">{{ $lead->description }}</p>
@endsection
