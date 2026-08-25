@extends('emails.layout')

@section('subject', 'Welcome!')

@section('content')
    <h2 style="margin:0 0 12px; font-size:18px; color:#0f172a;">Welcome, {{ $user->name }}!</h2>
    <p style="margin:0; font-size:13px; color:#334155; line-height:1.6;">
        Thanks for creating an account with {{ config('app.name') }}. You can now browse media inventory, save items to your cart, and track your orders and enquiries from your dashboard.
    </p>
@endsection
