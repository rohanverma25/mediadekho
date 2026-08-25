@extends('admin.layouts.app')

@section('title', 'Media Categories')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Media Categories</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#categoryModal" id="btnAddCategory">
                <i class="bi bi-plus-lg"></i> Add Category
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="categoriesTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Icon</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="categoryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalTitle">Add Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="category_id">

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="category_name" class="form-control" required>
                            <div class="invalid-feedback" data-field="name"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="category_description" class="form-control" rows="4"></textarea>
                            <div class="invalid-feedback" data-field="description"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">YouTube Video Link</label>
                            <input type="url" name="youtube_video_link" id="category_youtube_video_link" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                            <div class="invalid-feedback" data-field="youtube_video_link"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" id="category_image" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="invalid-feedback" data-field="image"></div>
                            <img id="category_image_preview" src="" alt="Preview" class="mt-2 rounded border d-none" width="120" height="120" style="object-fit:cover;">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon</label>
                                <input type="text" name="icon" id="category_icon" class="form-control" placeholder="bi-newspaper">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="category_sort_order" class="form-control" value="0" min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="category_status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
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
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script>
$(function () {
    const categoryModal = new bootstrap.Modal('#categoryModal');

    // Hardcoding "/admin/..." here breaks the moment the app is deployed
    // under a subfolder (e.g. /mediadekho/backend/public/) — route() is
    // resolved server-side against the real request, so it's always correct.
    const categoryEditUrl = (id) => '{{ route('admin.categories.edit', ['__ID__']) }}'.replace('__ID__', id);
    const categoryResourceUrl = (id) => '{{ route('admin.categories.update', ['__ID__']) }}'.replace('__ID__', id);

    $('#category_description').summernote({
        height: 180,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['codeview']],
        ],
    });

    const table = $('#categoriesTable').DataTable({
        ajax: {
            url: '{{ route('admin.categories.data') }}',
        },
        columns: [
            { data: 'image_url', orderable: false, render: (url) => url ? `<img src="${url}" class="rounded" width="48" height="48" style="object-fit:cover;">` : '<span class="text-muted">—</span>' },
            { data: 'name' },
            { data: 'icon', defaultContent: '—' },
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
        $('#categoryForm .invalid-feedback').text('');
        $('#categoryForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#categoryForm [name="${field}"]`).addClass('is-invalid');
            $(`#categoryForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    function resetImagePreview(existingUrl = null) {
        const preview = $('#category_image_preview');
        $('#category_image').val('');

        if (existingUrl) {
            preview.attr('src', existingUrl).removeClass('d-none');
        } else {
            preview.attr('src', '').addClass('d-none');
        }
    }

    $('#category_image').on('change', function () {
        const file = this.files[0];
        if (! file) return;

        const reader = new FileReader();
        reader.onload = (e) => $('#category_image_preview').attr('src', e.target.result).removeClass('d-none');
        reader.readAsDataURL(file);
    });

    $('#btnAddCategory').on('click', function () {
        clearErrors();
        $('#categoryForm')[0].reset();
        $('#category_id').val('');
        $('#category_description').summernote('code', '');
        resetImagePreview();
        $('#categoryModalTitle').text('Add Category');
    });

    $('#categoriesTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(categoryEditUrl(id), function (res) {
            const c = res.category;

            $('#category_id').val(c.id);
            $('#category_name').val(c.name);
            $('#category_description').summernote('code', c.description ?? '');
            $('#category_youtube_video_link').val(c.youtube_video_link);
            $('#category_icon').val(c.icon);
            $('#category_sort_order').val(c.sort_order);
            $('#category_status').val(c.status);
            resetImagePreview(c.image_url);
            $('#categoryModalTitle').text('Edit Category');
            categoryModal.show();
        });
    });

    $('#categoriesTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this category? This cannot be undone.')) return;

        $.ajax({
            url: categoryResourceUrl(id),
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this category.');
            },
        });
    });

    $('#categoryForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#category_id').val();
        const url = id ? categoryResourceUrl(id) : '{{ route('admin.categories.store') }}';
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
                categoryModal.hide();
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
