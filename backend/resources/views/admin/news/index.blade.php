@extends('admin.layouts.app')

@section('title', 'News')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>News</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newsModal" id="btnAddNews">
                <i class="bi bi-plus-lg"></i> Add News
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="newsTable">
                <thead>
                    <tr>
                        <th>Screenshot</th>
                        <th>Title</th>
                        <th>Link</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="newsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="newsForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newsModalTitle">Add News</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="news_id">

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="news_title" class="form-control" required>
                            <div class="invalid-feedback" data-field="title"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link <span class="text-muted small">(third-party news article URL)</span></label>
                            <input type="url" name="link" id="news_link" class="form-control" placeholder="https://example.com/article" required>
                            <div class="invalid-feedback" data-field="link"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Screenshot Image <span id="news_image_required_hint" class="text-danger">*</span></label>
                            <input type="file" name="image" id="news_image_file" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="invalid-feedback" data-field="image"></div>
                            <img id="news_image_preview" src="" alt="Preview" class="mt-2 rounded border d-none" width="160" height="100" style="object-fit:cover;">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="news_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="news_sort_order" class="form-control" value="0" min="0">
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
    const newsModal = new bootstrap.Modal('#newsModal');

    const table = $('#newsTable').DataTable({
        ajax: {
            url: '{{ route('admin.news.data') }}',
        },
        columns: [
            { data: 'image_url', orderable: false, render: (url) => url ? `<img src="${url}" class="rounded border" width="72" height="45" style="object-fit:cover;">` : '<span class="text-muted">—</span>' },
            { data: 'title' },
            { data: 'link', render: (url) => `<a href="${url}" target="_blank" rel="noopener" class="text-truncate d-inline-block" style="max-width:220px;">${url}</a>` },
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
        $('#newsForm .invalid-feedback').text('');
        $('#newsForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#newsForm [name="${field}"]`).addClass('is-invalid');
            $(`#newsForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    function resetImagePreview(existingUrl = null) {
        const preview = $('#news_image_preview');
        $('#news_image_file').val('');

        if (existingUrl) {
            preview.attr('src', existingUrl).removeClass('d-none');
        } else {
            preview.attr('src', '').addClass('d-none');
        }
    }

    $('#news_image_file').on('change', function () {
        const file = this.files[0];
        if (! file) return;

        const reader = new FileReader();
        reader.onload = (e) => $('#news_image_preview').attr('src', e.target.result).removeClass('d-none');
        reader.readAsDataURL(file);
    });

    $('#btnAddNews').on('click', function () {
        clearErrors();
        $('#newsForm')[0].reset();
        $('#news_id').val('');
        $('#news_image_file').prop('required', true);
        $('#news_image_required_hint').removeClass('d-none');
        resetImagePreview();
        $('#newsModalTitle').text('Add News');
    });

    $('#newsTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(`/admin/news/${id}/edit`, function (res) {
            const n = res.news;

            $('#news_id').val(n.id);
            $('#news_title').val(n.title);
            $('#news_link').val(n.link);
            $('#news_status').val(n.status);
            $('#news_sort_order').val(n.sort_order);
            $('#news_image_file').prop('required', false);
            $('#news_image_required_hint').addClass('d-none');
            resetImagePreview(n.image_url);
            $('#newsModalTitle').text('Edit News');
            newsModal.show();
        });
    });

    $('#newsTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this news item? This cannot be undone.')) return;

        $.ajax({
            url: `/admin/news/${id}`,
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this news item.');
            },
        });
    });

    $('#newsForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#news_id').val();
        const url = id ? `/admin/news/${id}` : '{{ route('admin.news.store') }}';
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
                newsModal.hide();
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
