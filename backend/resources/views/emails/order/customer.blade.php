@extends('emails.layout')

@section('subject', 'Order Confirmed')

@section('content')
    <h2 style="margin:0 0 12px; font-size:18px; color:#0f172a;">Thanks, {{ $order->user?->name }}!</h2>
    <p style="margin:0 0 16px; font-size:13px; color:#334155; line-height:1.6;">
        Your payment has been confirmed for order <strong>{{ $order->order_number }}</strong>. Here's a summary:
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        @foreach ($order->items as $item)
            <tr>
                <td style="padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:13px; color:#1e293b;">{{ $item->title }} &times; {{ $item->quantity }}</td>
                <td style="padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:13px; color:#1e293b; text-align:right;">₹{{ number_format($item->line_total, 2) }}</td>
            </tr>
        @endforeach
        <tr>
            <td style="padding:10px 0 0; font-size:14px; font-weight:bold; color:#0f172a;">Total Paid</td>
            <td style="padding:10px 0 0; font-size:14px; font-weight:bold; color:#c01625; text-align:right;">₹{{ number_format($order->grand_total, 2) }}</td>
        </tr>
    </table>
@endsection
