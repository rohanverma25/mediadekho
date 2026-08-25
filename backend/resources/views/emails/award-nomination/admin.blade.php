@extends('emails.layout')

@section('subject', 'New Award Nomination')

@section('content')
    <h2 style="margin:0 0 4px; font-size:18px; color:#0f172a;">New Award Nomination</h2>
    <p style="margin:0 0 16px; font-size:13px; color:#64748b;">Submitted {{ $nomination->created_at->format('M j, Y g:i A') }}</p>

    @include('emails.partials.facts-table', ['rows' => [
        'Award' => $nomination->award?->title,
        'Name' => $nomination->name,
        'Email' => $nomination->email,
        'Phone' => $nomination->phone,
        'Company' => $nomination->company_name,
        'Account' => $nomination->user?->name,
    ]])

    <p style="margin:16px 0 4px; font-size:12px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Nomination Details</p>
    <p style="margin:0; font-size:13px; color:#1e293b; white-space:pre-wrap;">{{ $nomination->description }}</p>
@endsection
