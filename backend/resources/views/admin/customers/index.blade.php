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
                        <th>Account Type</th>
                        <th>Orders</th>
                        <th>Registered</th>
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
    $('#customersTable').DataTable({
        ajax: {
            url: '{{ route('admin.customers.data') }}',
        },
        order: [[6, 'desc']],
        columns: [
            { data: 'name' },
            { data: 'email' },
            { data: 'phone', defaultContent: '—' },
            { data: 'company', defaultContent: '—' },
            { data: 'role', render: (role) => `<span class="badge text-bg-secondary">${role ?? '—'}</span>` },
            { data: 'orders_count' },
            { data: 'created_at' },
        ],
    });
});
</script>
@endpush
