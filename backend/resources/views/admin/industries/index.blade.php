@extends('admin.layouts.app')

@section('title', 'Industries')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Industries</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#industryModal" id="btnAddIndustry">
                <i class="bi bi-plus-lg"></i> Add Industry
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="industriesTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="industryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="industryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="industryModalTitle">Add Industry</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="industry_id">

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="industry_title" class="form-control" required>
                            <div class="invalid-feedback" data-field="title"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image <span id="industry_image_required_hint" class="text-danger">*</span></label>
                            <input type="file" name="image" id="industry_image_file" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="invalid-feedback" data-field="image"></div>
                            <img id="industry_image_preview" src="" alt="Preview" class="mt-2 rounded border d-none" width="120" height="120" style="object-fit:cover;">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="industry_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="industry_sort_order" class="form-control" value="0" min="0">
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
    const industryModal = new bootstrap.Modal('#industryModal');

    // route() is resolved server-side against the real request, so it stays
    // correct under a subfolder deployment — a hardcoded "/admin/..." path
    // would not.
    const industryEditUrl = (id) => '{{ route('admin.industries.edit', ['__ID__']) }}'.replace('__ID__', id);
    const industryResourceUrl = (id) => '{{ route('admin.industries.update', ['__ID__']) }}'.replace('__ID__', id);

    const table = $('#industriesTable').DataTable({
        ajax: {
            url: '{{ route('admin.industries.data') }}',
        },
        columns: [
            { data: 'image_url', orderable: false, render: (url) => url ? `<img src="${url}" class="rounded" width="48" height="48" style="object-fit:cover;">` : '<span class="text-muted">—</span>' },
            { data: 'title' },
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
        $('#industryForm .invalid-feedback').text('');
        $('#industryForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#industryForm [name="${field}"]`).addClass('is-invalid');
            $(`#industryForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    function resetImagePreview(existingUrl = null) {
        const preview = $('#industry_image_preview');
        $('#industry_image_file').val('');

        if (existingUrl) {
            preview.attr('src', existingUrl).removeClass('d-none');
        } else {
            preview.attr('src', '').addClass('d-none');
        }
    }

    $('#industry_image_file').on('change', function () {
        const file = this.files[0];
        if (! file) return;

        const reader = new FileReader();
        reader.onload = (e) => $('#industry_image_preview').attr('src', e.target.result).removeClass('d-none');
        reader.readAsDataURL(file);
    });

    $('#btnAddIndustry').on('click', function () {
        clearErrors();
        $('#industryForm')[0].reset();
        $('#industry_id').val('');
        $('#industry_image_file').prop('required', true);
        $('#industry_image_required_hint').removeClass('d-none');
        resetImagePreview();
        $('#industryModalTitle').text('Add Industry');
    });

    $('#industriesTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(industryEditUrl(id), function (res) {
            const i = res.industry;

            $('#industry_id').val(i.id);
            $('#industry_title').val(i.title);
            $('#industry_status').val(i.status);
            $('#industry_sort_order').val(i.sort_order);
            $('#industry_image_file').prop('required', false);
            $('#industry_image_required_hint').addClass('d-none');
            resetImagePreview(i.image_url);
            $('#industryModalTitle').text('Edit Industry');
            industryModal.show();
        });
    });

    $('#industriesTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this industry? This cannot be undone.')) return;

        $.ajax({
            url: industryResourceUrl(id),
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this industry.');
            },
        });
    });

    $('#industryForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#industry_id').val();
        const url = id ? industryResourceUrl(id) : '{{ route('admin.industries.store') }}';
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
                industryModal.hide();
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
