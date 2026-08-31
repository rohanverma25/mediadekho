@extends('admin.layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Roles & Permissions</span>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Role
            </a>
        </div>
        <div class="card-body">
            <p class="small text-muted">Controls what admin-panel staff can see and do. Customer account tiers (Retail/B2C/B2B/Enterprise) live separately and aren't shown here.</p>
            <table class="table table-hover align-middle w-100" id="rolesTable">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th>Staff Assigned</th>
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
    const roleEditUrl = (id) => '{{ route('admin.roles.edit', ['__ID__']) }}'.replace('__ID__', id);
    const roleResourceUrl = (id) => '{{ route('admin.roles.update', ['__ID__']) }}'.replace('__ID__', id);

    const table = $('#rolesTable').DataTable({
        ajax: {
            url: '{{ route('admin.roles.data') }}',
        },
        columns: [
            {
                data: null,
                render: (row) => `${row.name} ${row.protected ? '<span class="badge text-bg-secondary ms-1">Built-in</span>' : ''}`,
            },
            { data: 'permissions_count' },
            { data: 'users_count' },
            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: (row) => `
                    <a href="${roleEditUrl(row.id)}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    ${row.protected
                        ? ''
                        : `<button class="btn btn-sm btn-outline-danger btn-delete-role" data-id="${row.id}"><i class="bi bi-trash"></i></button>`}
                `,
            },
        ],
    });

    $('#rolesTable').on('click', '.btn-delete-role', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this role? This cannot be undone.')) return;

        $.ajax({
            url: roleResourceUrl(id),
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this role.');
            },
        });
    });
});
</script>
@endpush
