@extends('admin.layouts.app')

@section('title', $order->order_number)

@php
    $statusColor = match ($order->status) {
        'paid' => 'success',
        'pending' => 'warning',
        'failed' => 'danger',
        'refunded' => 'dark',
        default => 'secondary',
    };
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0">{{ $order->order_number }}</h5>
            <span class="text-muted small">Placed {{ $order->created_at->format('M j, Y g:i A') }}</span>
        </div>
        <div class="d-flex gap-2">
            <span class="badge text-bg-{{ $statusColor }} align-self-center">{{ ucfirst($order->status) }}</span>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">Back to List</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Order Details</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Customer</dt>
                <dd class="col-sm-9">{{ $order->user?->name ?? 'Guest' }} @if($order->user?->email) ({{ $order->user->email }}) @endif</dd>
                <dt class="col-sm-3">Subtotal</dt>
                <dd class="col-sm-9">₹{{ number_format($order->subtotal, 2) }}</dd>
                <dt class="col-sm-3">Discount</dt>
                <dd class="col-sm-9">-₹{{ number_format($order->discount_total, 2) }}</dd>
                <dt class="col-sm-3">Tax</dt>
                <dd class="col-sm-9">₹{{ number_format($order->tax_total, 2) }}</dd>
                <dt class="col-sm-3">Grand Total</dt>
                <dd class="col-sm-9 fw-semibold">₹{{ number_format($order->grand_total, 2) }} {{ $order->currency }}</dd>
                <dt class="col-sm-3">Razorpay Order ID</dt>
                <dd class="col-sm-9">{{ $order->razorpay_order_id ?? '—' }}</dd>
                <dt class="col-sm-3">Razorpay Payment ID</dt>
                <dd class="col-sm-9">{{ $order->razorpay_payment_id ?? '—' }}</dd>
                <dt class="col-sm-3">Paid At</dt>
                <dd class="col-sm-9">{{ $order->paid_at?->format('M j, Y g:i A') ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Line Items</div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Tax</th>
                        <th>Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->title }}</td>
                            <td>{{ $item->category ?? '—' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>₹{{ number_format($item->unit_price, 2) }}</td>
                            <td>₹{{ number_format($item->tax_amount, 2) }}</td>
                            <td class="fw-semibold">₹{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Update Status</div>
        <div class="card-body">
            <p class="text-muted small">
                Only <code>cancelled</code>/<code>refunded</code> can be set here — <code>paid</code> is only ever set automatically once payment is verified.
            </p>
            <div class="d-flex gap-2">
                <select id="order_status" class="form-select form-select-sm w-auto">
                    <option value="cancelled" @selected($order->status === 'cancelled')>Cancelled</option>
                    <option value="refunded" @selected($order->status === 'refunded')>Refunded</option>
                </select>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveOrderStatus">Save Status</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#btnSaveOrderStatus').on('click', function () {
        $.ajax({
            url: '{{ route('admin.orders.update', $order) }}',
            method: 'PUT',
            data: { status: $('#order_status').val() },
            success: function () {
                location.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to update this order.');
            },
        });
    });
});
</script>
@endpush
