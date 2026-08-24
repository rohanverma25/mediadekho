@extends('admin.layouts.app')

@section('title', 'Awards')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Awards</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#awardModal" id="btnAddAward">
                <i class="bi bi-plus-lg"></i> Add Award
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="awardsTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Organization</th>
                        <th>Event Date</th>
                        <th>Nominations</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="awardModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="awardForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="awardModalTitle">Add Award</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="award_id">

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="award_title" class="form-control" required>
                            <div class="invalid-feedback" data-field="title"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="award_description" class="form-control" rows="5"></textarea>
                            <div class="invalid-feedback" data-field="description"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" id="award_image_file" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="invalid-feedback" data-field="image"></div>
                            <img id="award_image_preview" src="" alt="Preview" class="mt-2 rounded border d-none" width="160" height="100" style="object-fit:cover;">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Type</label>
                                <select name="type" id="award_type" class="form-select" required>
                                    <option value="upcoming">Upcoming (accepts nominations)</option>
                                    <option value="past">Past Association (showcase only)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Organization</label>
                                <input type="text" name="organization" id="award_organization" class="form-control" placeholder="e.g. IAA India">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Event Date</label>
                                <input type="date" name="event_date" id="award_event_date" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="award_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="award_sort_order" class="form-control" value="0" min="0">
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
    const awardModal = new bootstrap.Modal('#awardModal');

    $('#award_description').summernote({
        height: 200,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['codeview']],
        ],
    });

    const table = $('#awardsTable').DataTable({
        ajax: {
            url: '{{ route('admin.awards.data') }}',
        },
        columns: [
            { data: 'image_url', orderable: false, render: (url) => url ? `<img src="${url}" class="rounded border" width="64" height="40" style="object-fit:cover;">` : '<span class="text-muted">—</span>' },
            { data: 'title' },
            { data: 'type', render: (type) => `<span class="badge text-bg-${type === 'upcoming' ? 'primary' : 'secondary'}">${type}</span>` },
            { data: 'organization', defaultContent: '—' },
            { data: 'event_date', defaultContent: '—' },
            { data: 'nominations_count', render: (count) => count > 0 ? `<span class="badge text-bg-info">${count}</span>` : '—' },
            { data: 'status', render: (status) => `<span class="badge text-bg-${status === 'active' ? 'success' : 'secondary'}">${status}</span>` },
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
        $('#awardForm .invalid-feedback').text('');
        $('#awardForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#awardForm [name="${field}"]`).addClass('is-invalid');
            $(`#awardForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    function resetImagePreview(existingUrl = null) {
        const preview = $('#award_image_preview');
        $('#award_image_file').val('');

        if (existingUrl) {
            preview.attr('src', existingUrl).removeClass('d-none');
        } else {
            preview.attr('src', '').addClass('d-none');
        }
    }

    $('#award_image_file').on('change', function () {
        const file = this.files[0];
        if (! file) return;

        const reader = new FileReader();
        reader.onload = (e) => $('#award_image_preview').attr('src', e.target.result).removeClass('d-none');
        reader.readAsDataURL(file);
    });

    $('#btnAddAward').on('click', function () {
        clearErrors();
        $('#awardForm')[0].reset();
        $('#award_id').val('');
        $('#award_description').summernote('code', '');
        $('#award_type').val('upcoming');
        $('#award_status').val('active');
        resetImagePreview();
        $('#awardModalTitle').text('Add Award');
    });

    $('#awardsTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(`/admin/awards/${id}/edit`, function (res) {
            const a = res.award;

            $('#award_id').val(a.id);
            $('#award_title').val(a.title);
            $('#award_description').summernote('code', a.description ?? '');
            $('#award_type').val(a.type);
            $('#award_organization').val(a.organization);
            $('#award_event_date').val(a.event_date ? a.event_date.substring(0, 10) : '');
            $('#award_status').val(a.status);
            $('#award_sort_order').val(a.sort_order);
            resetImagePreview(a.image_url);
            $('#awardModalTitle').text('Edit Award');
            awardModal.show();
        });
    });

    $('#awardsTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this award? Any nominations submitted for it will be deleted too. This cannot be undone.')) return;

        $.ajax({
            url: `/admin/awards/${id}`,
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this award.');
            },
        });
    });

    $('#awardForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        $('#award_description').val($('#award_description').summernote('code'));

        const id = $('#award_id').val();
        const url = id ? `/admin/awards/${id}` : '{{ route('admin.awards.store') }}';
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
                awardModal.hide();
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
