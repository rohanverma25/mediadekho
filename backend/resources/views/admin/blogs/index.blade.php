@extends('admin.layouts.app')

@section('title', 'Blogs')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Blogs</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#blogModal" id="btnAddBlog">
                <i class="bi bi-plus-lg"></i> Add Blog Post
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="blogsTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Excerpt</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="blogModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="blogForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="blogModalTitle">Add Blog Post</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="blog_id">

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="blog_title" class="form-control" required>
                            <div class="invalid-feedback" data-field="title"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Excerpt <span class="text-muted small">(short summary shown on the blog list)</span></label>
                            <textarea name="excerpt" id="blog_excerpt" class="form-control" rows="2" maxlength="500"></textarea>
                            <div class="invalid-feedback" data-field="excerpt"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" id="blog_content" class="form-control" rows="6"></textarea>
                            <div class="invalid-feedback" data-field="content"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Featured Image</label>
                            <input type="file" name="featured_image" id="blog_featured_image" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="invalid-feedback" data-field="featured_image"></div>
                            <img id="blog_image_preview" src="" alt="Preview" class="mt-2 rounded border d-none" width="160" height="100" style="object-fit:cover;">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Author Name</label>
                                <input type="text" name="author_name" id="blog_author_name" class="form-control">
                                <div class="invalid-feedback" data-field="author_name"></div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="blog_status" class="form-select" required>
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Published Date</label>
                                <input type="date" name="published_at" id="blog_published_at" class="form-control">
                                <div class="invalid-feedback" data-field="published_at"></div>
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
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script>
$(function () {
    const blogModal = new bootstrap.Modal('#blogModal');

    $('#blog_content').summernote({
        height: 260,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture']],
            ['view', ['codeview']],
        ],
    });

    const table = $('#blogsTable').DataTable({
        ajax: {
            url: '{{ route('admin.blogs.data') }}',
        },
        columns: [
            { data: 'featured_image_url', orderable: false, render: (url) => url ? `<img src="${url}" class="rounded border" width="64" height="40" style="object-fit:cover;">` : '<span class="text-muted">—</span>' },
            { data: 'title' },
            { data: 'excerpt_preview', defaultContent: '—' },
            { data: 'author_name', defaultContent: '—' },
            { data: 'status', render: (status) => `<span class="badge text-bg-${status === 'published' ? 'success' : 'secondary'}">${status}</span>` },
            { data: 'published_at', defaultContent: '—' },
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
        $('#blogForm .invalid-feedback').text('');
        $('#blogForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#blogForm [name="${field}"]`).addClass('is-invalid');
            $(`#blogForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    function resetImagePreview(existingUrl = null) {
        const preview = $('#blog_image_preview');
        $('#blog_featured_image').val('');

        if (existingUrl) {
            preview.attr('src', existingUrl).removeClass('d-none');
        } else {
            preview.attr('src', '').addClass('d-none');
        }
    }

    $('#blog_featured_image').on('change', function () {
        const file = this.files[0];
        if (! file) return;

        const reader = new FileReader();
        reader.onload = (e) => $('#blog_image_preview').attr('src', e.target.result).removeClass('d-none');
        reader.readAsDataURL(file);
    });

    $('#btnAddBlog').on('click', function () {
        clearErrors();
        $('#blogForm')[0].reset();
        $('#blog_id').val('');
        $('#blog_content').summernote('code', '');
        $('#blog_status').val('draft');
        resetImagePreview();
        $('#blogModalTitle').text('Add Blog Post');
    });

    $('#blogsTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(`/admin/blogs/${id}/edit`, function (res) {
            const b = res.blog;

            $('#blog_id').val(b.id);
            $('#blog_title').val(b.title);
            $('#blog_excerpt').val(b.excerpt);
            $('#blog_content').summernote('code', b.content ?? '');
            $('#blog_author_name').val(b.author_name);
            $('#blog_status').val(b.status);
            $('#blog_published_at').val(b.published_at ? b.published_at.substring(0, 10) : '');
            resetImagePreview(b.featured_image_url);
            $('#blogModalTitle').text('Edit Blog Post');
            blogModal.show();
        });
    });

    $('#blogsTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this blog post? This cannot be undone.')) return;

        $.ajax({
            url: `/admin/blogs/${id}`,
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this blog post.');
            },
        });
    });

    $('#blogForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        // Summernote's textarea isn't kept in sync automatically — pull the
        // editor's current HTML into the underlying field before building
        // FormData from it.
        $('#blog_content').val($('#blog_content').summernote('code'));

        const id = $('#blog_id').val();
        const url = id ? `/admin/blogs/${id}` : '{{ route('admin.blogs.store') }}';
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
                blogModal.hide();
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
