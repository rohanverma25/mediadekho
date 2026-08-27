@extends('admin.layouts.app')

@section('title', 'Videos')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Videos</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#videoModal" id="btnAddVideo">
                <i class="bi bi-plus-lg"></i> Add Video
            </button>
        </div>
        <div class="card-body">
            <p class="small text-muted">Shown as a slider on the homepage. Paste any YouTube link — watch, share, or embed URLs all work.</p>
            <table class="table table-hover align-middle w-100" id="videosTable">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>YouTube URL</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="videoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="videoForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="videoModalTitle">Add Video</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="video_id">

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="video_title" class="form-control" required>
                            <div class="invalid-feedback" data-field="title"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" name="youtube_url" id="video_youtube_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." required>
                            <div class="invalid-feedback" data-field="youtube_url"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="video_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="video_sort_order" class="form-control" value="0" min="0">
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
    const videoModal = new bootstrap.Modal('#videoModal');

    // route() is resolved server-side against the real request, so it stays
    // correct under a subfolder deployment — a hardcoded "/admin/..." path
    // would not.
    const videoEditUrl = (id) => '{{ route('admin.videos.edit', ['__ID__']) }}'.replace('__ID__', id);
    const videoResourceUrl = (id) => '{{ route('admin.videos.update', ['__ID__']) }}'.replace('__ID__', id);

    const table = $('#videosTable').DataTable({
        ajax: {
            url: '{{ route('admin.videos.data') }}',
        },
        columns: [
            { data: 'thumbnail_url', orderable: false, render: (url) => url ? `<img src="${url}" class="rounded" width="80" height="45" style="object-fit:cover;">` : '<span class="text-muted">—</span>' },
            { data: 'title' },
            { data: 'youtube_url', render: (url) => `<a href="${url}" target="_blank" rel="noopener" class="text-truncate d-inline-block" style="max-width:220px;">${url}</a>` },
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
        $('#videoForm .invalid-feedback').text('');
        $('#videoForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#videoForm [name="${field}"]`).addClass('is-invalid');
            $(`#videoForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    $('#btnAddVideo').on('click', function () {
        clearErrors();
        $('#videoForm')[0].reset();
        $('#video_id').val('');
        $('#videoModalTitle').text('Add Video');
    });

    $('#videosTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(videoEditUrl(id), function (res) {
            const v = res.video;

            $('#video_id').val(v.id);
            $('#video_title').val(v.title);
            $('#video_youtube_url').val(v.youtube_url);
            $('#video_status').val(v.status);
            $('#video_sort_order').val(v.sort_order);
            $('#videoModalTitle').text('Edit Video');
            videoModal.show();
        });
    });

    $('#videosTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this video? This cannot be undone.')) return;

        $.ajax({
            url: videoResourceUrl(id),
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this video.');
            },
        });
    });

    $('#videoForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#video_id').val();
        const url = id ? videoResourceUrl(id) : '{{ route('admin.videos.store') }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url,
            method,
            data: $(this).serialize(),
            success: function () {
                videoModal.hide();
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
