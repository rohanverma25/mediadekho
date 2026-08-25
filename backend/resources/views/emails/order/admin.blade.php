@extends('emails.layout')

@section('subject', 'New Paid Order')

@section('content')
    <h2 style="margin:0 0 4px; font-size:18px; color:#0f172a;">New Paid Order — {{ $order->order_number }}</h2>
    <p style="margin:0 0 16px; font-size:13px; color:#64748b;">Paid {{ $order->paid_at?->format('M j, Y g:i A') }}</p>

    @include('emails.partials.facts-table', ['rows' => [
        'Customer' => $order->user?->name,
        'Email' => $order->user?->email,
        'Total' => '₹'.number_format($order->grand_total, 2),
        'Razorpay Payment ID' => $order->razorpay_payment_id,
    ]])

    <p style="margin:16px 0 4px; font-size:12px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Items</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        @foreach ($order->items as $item)
            <tr>
                <td style="padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:13px; color:#1e293b;">{{ $item->title }} &times; {{ $item->quantity }}</td>
                <td style="padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:13px; color:#1e293b; text-align:right;">₹{{ number_format($item->line_total, 2) }}</td>
            </tr>
        @endforeach
    </table>
@endsection
