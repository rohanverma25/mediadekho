@extends('emails.layout')

@section('subject', 'New Job Application')

@section('content')
    <h2 style="margin:0 0 4px; font-size:18px; color:#0f172a;">New Job Application</h2>
    <p style="margin:0 0 16px; font-size:13px; color:#64748b;">Submitted {{ $application->created_at->format('M j, Y g:i A') }}</p>

    @include('emails.partials.facts-table', ['rows' => [
        'Role' => $application->job?->title,
        'Name' => $application->name,
        'Email' => $application->email,
        'Phone' => $application->phone,
        'Account' => $application->user?->name,
        'Resume' => $application->resume ? 'Attached' : 'Not provided',
    ]])

    @if ($application->cover_letter)
        <p style="margin:16px 0 4px; font-size:12px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Cover Letter</p>
        <p style="margin:0; font-size:13px; color:#1e293b; white-space:pre-wrap;">{{ $application->cover_letter }}</p>
    @endif
@endsection
