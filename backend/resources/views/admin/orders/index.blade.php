@extends('admin.layouts.app')

@section('title', 'Orders')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Orders</span>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="ordersTable">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    const statusBadge = (status) => {
        const map = { pending: 'warning', paid: 'success', failed: 'danger', cancelled: 'secondary', refunded: 'dark' };
        return `<span class="badge text-bg-${map[status] ?? 'secondary'}">${status}</span>`;
    };

    $('#ordersTable').DataTable({
        ajax: {
            url: '{{ route('admin.orders.data') }}',
        },
        order: [[5, 'desc']],
        columns: [
            { data: 'order_number' },
            {
                data: null,
                render: (row) => `${row.customer_name}${row.customer_email ? `<br><span class="text-muted small">${row.customer_email}</span>` : ''}`,
            },
            { data: 'items_count' },
            { data: 'grand_total', render: (amount) => `₹${Number(amount).toLocaleString('en-IN')}` },
            { data: 'status', render: statusBadge },
            { data: 'created_at' },
            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: (row) => `<a href="/admin/orders/${row.id}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>`,
            },
        ],
    });
});
</script>
@endpush
