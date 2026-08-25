@extends('emails.layout')

@section('subject', 'New Customer Registered')

@section('content')
    <h2 style="margin:0 0 4px; font-size:18px; color:#0f172a;">New Customer Registered</h2>
    <p style="margin:0 0 16px; font-size:13px; color:#64748b;">Registered {{ $user->created_at->format('M j, Y g:i A') }}</p>

    @include('emails.partials.facts-table', ['rows' => [
        'Name' => $user->name,
        'Email' => $user->email,
        'Phone' => $user->phone,
        'Company' => $user->company,
        'Account Type' => $user->getRoleNames()->first(),
    ]])
@endsection
