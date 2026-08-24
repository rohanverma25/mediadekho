@extends('admin.layouts.app')

@section('title', 'Client Logos')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Client Logos</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#clientLogoModal" id="btnAddClientLogo">
                <i class="bi bi-plus-lg"></i> Add Client Logo
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="clientLogosTable">
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>Name</th>
                        <th>Website</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="clientLogoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="clientLogoForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="clientLogoModalTitle">Add Client Logo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="client_logo_id">

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="client_logo_name" class="form-control" required>
                            <div class="invalid-feedback" data-field="name"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Website URL</label>
                            <input type="url" name="website_url" id="client_logo_website_url" class="form-control" placeholder="https://client-website.com">
                            <div class="invalid-feedback" data-field="website_url"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Logo <span id="client_logo_required_hint" class="text-danger">*</span></label>
                            <input type="file" name="logo" id="client_logo_file" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                            <div class="invalid-feedback" data-field="logo"></div>
                            <img id="client_logo_preview" src="" alt="Preview" class="mt-2 rounded border d-none" width="120" height="120" style="object-fit:contain;">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="client_logo_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="client_logo_sort_order" class="form-control" value="0" min="0">
                            </div>
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
    const clientLogoModal = new bootstrap.Modal('#clientLogoModal');

    const table = $('#clientLogosTable').DataTable({
        ajax: {
            url: '{{ route('admin.client-logos.data') }}',
        },
        columns: [
            { data: 'logo_url', orderable: false, render: (url) => url ? `<img src="${url}" class="rounded border" width="48" height="48" style="object-fit:contain;">` : '<span class="text-muted">—</span>' },
            { data: 'name' },
            { data: 'website_url', render: (url) => url ? `<a href="${url}" target="_blank" rel="noopener">${url}</a>` : '<span class="text-muted">—</span>' },
            { data: 'status', render: (status) => `<span class="badge text-bg-${status === 'active' ? 'success' : 'secondary'}">${status}</span>` },
            { data: 'sort_order' },
            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: (row) => `
                    <button class="btn btn-sm btn-outline-primary btn-edit" data-id="${row.id}"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${row.id}"><i class="bi bi-trash"></i></button>
                `,
            },
        ],
    });

    function clearErrors() {
        $('#clientLogoForm .invalid-feedback').text('');
        $('#clientLogoForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#clientLogoForm [name="${field}"]`).addClass('is-invalid');
            $(`#clientLogoForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    function resetLogoPreview(existingUrl = null) {
        const preview = $('#client_logo_preview');
        $('#client_logo_file').val('');

        if (existingUrl) {
            preview.attr('src', existingUrl).removeClass('d-none');
        } else {
            preview.attr('src', '').addClass('d-none');
        }
    }

    $('#client_logo_file').on('change', function () {
        const file = this.files[0];
        if (! file) return;

        const reader = new FileReader();
        reader.onload = (e) => $('#client_logo_preview').attr('src', e.target.result).removeClass('d-none');
        reader.readAsDataURL(file);
    });

    $('#btnAddClientLogo').on('click', function () {
        clearErrors();
        $('#clientLogoForm')[0].reset();
        $('#client_logo_id').val('');
        $('#client_logo_file').prop('required', true);
        $('#client_logo_required_hint').removeClass('d-none');
        resetLogoPreview();
        $('#clientLogoModalTitle').text('Add Client Logo');
    });

    $('#clientLogosTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(`/admin/client-logos/${id}/edit`, function (res) {
            const l = res.logo;

            $('#client_logo_id').val(l.id);
            $('#client_logo_name').val(l.name);
            $('#client_logo_website_url').val(l.website_url);
            $('#client_logo_status').val(l.status);
            $('#client_logo_sort_order').val(l.sort_order);
            $('#client_logo_file').prop('required', false);
            $('#client_logo_required_hint').addClass('d-none');
            resetLogoPreview(l.logo_url);
            $('#clientLogoModalTitle').text('Edit Client Logo');
            clientLogoModal.show();
        });
    });

    $('#clientLogosTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this client logo? This cannot be undone.')) return;

        $.ajax({
            url: `/admin/client-logos/${id}`,
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this client logo.');
            },
        });
    });

    $('#clientLogoForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#client_logo_id').val();
        const url = id ? `/admin/client-logos/${id}` : '{{ route('admin.client-logos.store') }}';
        const formData = new FormData(this);
        if (id) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                clientLogoModal.hide();
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
