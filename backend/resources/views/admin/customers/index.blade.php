@extends('admin.layouts.app')

@section('title', 'Customers')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Registered Customers</span>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="customersTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>GSTIN</th>
                        <th>Account Type</th>
                        <th>Status</th>
                        <th>Orders</th>
                        <th>Registered</th>
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
    // route() is resolved server-side against the real request, so it stays
    // correct under a subfolder deployment — a hardcoded "/admin/..." path
    // would not.
    const customerApproveUrl = (id) => '{{ route('admin.customers.approve', ['__ID__']) }}'.replace('__ID__', id);
    const customerRejectUrl = (id) => '{{ route('admin.customers.reject', ['__ID__']) }}'.replace('__ID__', id);

    const statusBadge = (status) => {
        const map = { approved: 'success', pending: 'warning', rejected: 'danger' };
        return `<span class="badge text-bg-${map[status] ?? 'secondary'}">${status ?? 'approved'}</span>`;
    };

    const table = $('#customersTable').DataTable({
        ajax: {
            url: '{{ route('admin.customers.data') }}',
        },
        order: [[7, 'desc']],
        columns: [
            { data: 'name' },
            { data: 'email' },
            { data: 'phone', defaultContent: '—' },
            { data: 'company', defaultContent: '—' },
            { data: 'gst_number', defaultContent: '—' },
            { data: 'role', render: (role) => `<span class="badge text-bg-secondary">${role ?? '—'}</span>` },
            { data: 'approval_status', render: statusBadge },
            { data: 'orders_count' },
            { data: 'created_at' },
            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: (row) => row.approval_status === 'pending'
                    ? `
                        <button class="btn btn-sm btn-outline-success btn-approve" data-id="${row.id}"><i class="bi bi-check-lg"></i> Approve</button>
                        <button class="btn btn-sm btn-outline-danger btn-reject" data-id="${row.id}"><i class="bi bi-x-lg"></i> Reject</button>
                    `
                    : '',
            },
        ],
    });

    $('#customersTable').on('click', '.btn-approve', function () {
        const id = $(this).data('id');
        if (! confirm('Approve this account? They will be able to log in immediately.')) return;

        $.ajax({
            url: customerApproveUrl(id),
            method: 'PUT',
            success: function () {
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to approve this account.');
            },
        });
    });

    $('#customersTable').on('click', '.btn-reject', function () {
        const id = $(this).data('id');
        if (! confirm('Reject this account? They will not be able to log in.')) return;

        $.ajax({
            url: customerRejectUrl(id),
            method: 'PUT',
            success: function () {
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to reject this account.');
            },
        });
    });
});
</script>
@endpush
