@extends('admin.layouts.app')

@section('title', 'Magazines')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Magazines</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#magazineModal" id="btnAddMagazine">
                <i class="bi bi-plus-lg"></i> Add Magazine
            </button>
        </div>
        <div class="card-body">
            <p class="small text-muted">Readable at <code>/magazines-reader</code> on the storefront — each issue opens in the in-browser PDF reader.</p>
            <table class="table table-hover align-middle w-100" id="magazinesTable">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Published</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="magazineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="magazineForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="magazineModalTitle">Add Magazine</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="magazine_id">

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="magazine_title" class="form-control" required>
                            <div class="invalid-feedback" data-field="title"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="magazine_description" class="form-control" rows="2" maxlength="500"></textarea>
                            <div class="invalid-feedback" data-field="description"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cover Image</label>
                            <input type="file" name="cover_image" id="magazine_cover_image" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="invalid-feedback" data-field="cover_image"></div>
                            <img id="magazine_cover_preview" src="" alt="Preview" class="mt-2 rounded border d-none" width="100" height="140" style="object-fit:cover;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">PDF File</label>
                            <input type="file" name="pdf_file" id="magazine_pdf_file" class="form-control" accept="application/pdf">
                            <div class="form-text">Up to 40MB. Read inline on the Magazine Reader page.</div>
                            <div class="invalid-feedback" data-field="pdf_file"></div>
                            <a id="magazine_pdf_current" href="" target="_blank" rel="noopener" class="d-none small">View current PDF</a>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="magazine_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Published Date</label>
                                <input type="date" name="published_at" id="magazine_published_at" class="form-control">
                                <div class="invalid-feedback" data-field="published_at"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="magazine_sort_order" class="form-control" value="0" min="0">
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
    const magazineModal = new bootstrap.Modal('#magazineModal');

    const magazineEditUrl = (id) => '{{ route('admin.magazines.edit', ['__ID__']) }}'.replace('__ID__', id);
    const magazineResourceUrl = (id) => '{{ route('admin.magazines.update', ['__ID__']) }}'.replace('__ID__', id);

    const table = $('#magazinesTable').DataTable({
        ajax: {
            url: '{{ route('admin.magazines.data') }}',
        },
        columns: [
            { data: 'cover_image_url', orderable: false, render: (url) => url ? `<img src="${url}" class="rounded border" width="48" height="64" style="object-fit:cover;">` : '<span class="text-muted">—</span>' },
            { data: 'title' },
            { data: 'published_at', defaultContent: '—' },
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
        $('#magazineForm .invalid-feedback').text('');
        $('#magazineForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#magazineForm [name="${field}"]`).addClass('is-invalid');
            $(`#magazineForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    function resetCoverPreview(existingUrl = null) {
        const preview = $('#magazine_cover_preview');
        $('#magazine_cover_image').val('');

        if (existingUrl) {
            preview.attr('src', existingUrl).removeClass('d-none');
        } else {
            preview.attr('src', '').addClass('d-none');
        }
    }

    function resetPdfLink(existingUrl = null) {
        const link = $('#magazine_pdf_current');
        $('#magazine_pdf_file').val('');

        if (existingUrl) {
            link.attr('href', existingUrl).removeClass('d-none');
        } else {
            link.attr('href', '').addClass('d-none');
        }
    }

    $('#magazine_cover_image').on('change', function () {
        const file = this.files[0];
        if (! file) return;

        const reader = new FileReader();
        reader.onload = (e) => $('#magazine_cover_preview').attr('src', e.target.result).removeClass('d-none');
        reader.readAsDataURL(file);
    });

    $('#btnAddMagazine').on('click', function () {
        clearErrors();
        $('#magazineForm')[0].reset();
        $('#magazine_id').val('');
        $('#magazine_status').val('active');
        resetCoverPreview();
        resetPdfLink();
        $('#magazineModalTitle').text('Add Magazine');
    });

    $('#magazinesTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(magazineEditUrl(id), function (res) {
            const m = res.magazine;

            $('#magazine_id').val(m.id);
            $('#magazine_title').val(m.title);
            $('#magazine_description').val(m.description);
            $('#magazine_status').val(m.status);
            $('#magazine_published_at').val(m.published_at ? m.published_at.substring(0, 10) : '');
            $('#magazine_sort_order').val(m.sort_order);
            resetCoverPreview(m.cover_image_url);
            resetPdfLink(m.pdf_url);
            $('#magazineModalTitle').text('Edit Magazine');
            magazineModal.show();
        });
    });

    $('#magazinesTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this magazine? This cannot be undone.')) return;

        $.ajax({
            url: magazineResourceUrl(id),
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this magazine.');
            },
        });
    });

    $('#magazineForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#magazine_id').val();
        const url = id ? magazineResourceUrl(id) : '{{ route('admin.magazines.store') }}';
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
                magazineModal.hide();
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
