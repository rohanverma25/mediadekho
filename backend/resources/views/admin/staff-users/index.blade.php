@extends('admin.layouts.app')

@section('title', 'Staff Users')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Staff Users</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#staffUserModal" id="btnAddStaffUser">
                <i class="bi bi-plus-lg"></i> Add Staff User
            </button>
        </div>
        <div class="card-body">
            <p class="small text-muted">Accounts that can log into this admin panel. Customers (Retail/B2C/B2B/Enterprise) register themselves on the storefront and are managed separately under Customers.</p>
            <table class="table table-hover align-middle w-100" id="staffUsersTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Added</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="staffUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="staffUserForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staffUserModalTitle">Add Staff User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="staff_id">

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="staff_name" class="form-control" required>
                            <div class="invalid-feedback" data-field="name"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="staff_email" class="form-control" required>
                            <div class="invalid-feedback" data-field="email"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" id="staff_role" class="form-select" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-field="role"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" id="staff_password" class="form-control" autocomplete="new-password">
                            <div class="form-text" id="staff_password_hint">At least 8 characters.</div>
                            <div class="invalid-feedback" data-field="password"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    const staffUserModal = new bootstrap.Modal('#staffUserModal');

    const staffEditUrl = (id) => '{{ route('admin.staff-users.edit', ['__ID__']) }}'.replace('__ID__', id);
    const staffResourceUrl = (id) => '{{ route('admin.staff-users.update', ['__ID__']) }}'.replace('__ID__', id);

    const table = $('#staffUsersTable').DataTable({
        ajax: {
            url: '{{ route('admin.staff-users.data') }}',
        },
        columns: [
            { data: 'name' },
            { data: 'email' },
            { data: 'role', render: (role) => `<span class="badge text-bg-secondary">${role ?? '—'}</span>` },
            { data: 'created_at' },
            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: (row) => `
                    <button class="btn btn-sm btn-outline-primary btn-edit" data-id="${row.id}"><i class="bi bi-pencil"></i></button>
                    ${row.is_self
                        ? ''
                        : `<button class="btn btn-sm btn-outline-danger btn-delete" data-id="${row.id}"><i class="bi bi-trash"></i></button>`}
                `,
            },
        ],
    });

    function clearErrors() {
        $('#staffUserForm .invalid-feedback').text('');
        $('#staffUserForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#staffUserForm [name="${field}"]`).addClass('is-invalid');
            $(`#staffUserForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    $('#btnAddStaffUser').on('click', function () {
        clearErrors();
        $('#staffUserForm')[0].reset();
        $('#staff_id').val('');
        $('#staff_password').prop('required', true);
        $('#staff_password_hint').text('At least 8 characters.');
        $('#staffUserModalTitle').text('Add Staff User');
    });

    $('#staffUsersTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(staffEditUrl(id), function (res) {
            const u = res.user;

            $('#staff_id').val(u.id);
            $('#staff_name').val(u.name);
            $('#staff_email').val(u.email);
            $('#staff_role').val(u.role);
            $('#staff_password').val('').prop('required', false);
            $('#staff_password_hint').text('Leave blank to keep the current password.');
            $('#staffUserModalTitle').text('Edit Staff User');
            staffUserModal.show();
        });
    });

    $('#staffUsersTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Remove this staff user? They will lose admin panel access immediately.')) return;

        $.ajax({
            url: staffResourceUrl(id),
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to remove this staff user.');
            },
        });
    });

    $('#staffUserForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#staff_id').val();
        const url = id ? staffResourceUrl(id) : '{{ route('admin.staff-users.store') }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url,
            method,
            data: $(this).serialize(),
            success: function () {
                staffUserModal.hide();
                table.ajax.reload();
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    showErrors(xhr.responseJSON.errors);
                } else {
                    alert(xhr.responseJSON?.message ?? 'Something went wrong.');
                }
            },
        });
    });
});
</script>
@endpush
