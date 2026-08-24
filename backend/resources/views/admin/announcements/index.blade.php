@extends('admin.layouts.app')

@section('title', 'Announcements')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Announcements</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#announcementModal" id="btnAddAnnouncement">
                <i class="bi bi-plus-lg"></i> Add Announcement
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="announcementsTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Event Date</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="announcementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="announcementForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="announcementModalTitle">Add Announcement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="announcement_id">

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="announcement_title" class="form-control" required>
                            <div class="invalid-feedback" data-field="title"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" id="announcement_message" class="form-control" rows="4" required></textarea>
                            <div class="invalid-feedback" data-field="message"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Event Date</label>
                                <input type="date" name="event_date" id="announcement_event_date" class="form-control">
                                <div class="invalid-feedback" data-field="event_date"></div>
                                <div class="form-text">Leave blank for a general announcement with no specific event date.</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="announcement_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="announcement_sort_order" class="form-control" value="0" min="0">
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
    const announcementModal = new bootstrap.Modal('#announcementModal');

    $('#announcement_message').summernote({
        height: 180,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['codeview']],
        ],
    });

    const table = $('#announcementsTable').DataTable({
        ajax: {
            url: '{{ route('admin.announcements.data') }}',
        },
        columns: [
            { data: 'title' },
            { data: 'message_preview' },
            { data: 'event_date', defaultContent: '—' },
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
        $('#announcementForm .invalid-feedback').text('');
        $('#announcementForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#announcementForm [name="${field}"]`).addClass('is-invalid');
            $(`#announcementForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    $('#btnAddAnnouncement').on('click', function () {
        clearErrors();
        $('#announcementForm')[0].reset();
        $('#announcement_id').val('');
        $('#announcement_message').summernote('code', '');
        $('#announcementModalTitle').text('Add Announcement');
    });

    $('#announcementsTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(`/admin/announcements/${id}/edit`, function (res) {
            const a = res.announcement;

            $('#announcement_id').val(a.id);
            $('#announcement_title').val(a.title);
            $('#announcement_message').summernote('code', a.message ?? '');
            $('#announcement_event_date').val(a.event_date);
            $('#announcement_status').val(a.status);
            $('#announcement_sort_order').val(a.sort_order);
            $('#announcementModalTitle').text('Edit Announcement');
            announcementModal.show();
        });
    });

    $('#announcementsTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this announcement? This cannot be undone.')) return;

        $.ajax({
            url: `/admin/announcements/${id}`,
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this announcement.');
            },
        });
    });

    $('#announcementForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#announcement_id').val();
        const url = id ? `/admin/announcements/${id}` : '{{ route('admin.announcements.store') }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url,
            method,
            data: $(this).serialize(),
            success: function () {
                announcementModal.hide();
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
