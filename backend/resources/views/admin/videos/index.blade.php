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
            <p class="small text-muted">Shown as a slider on the homepage. Paste a YouTube link, or upload a video file directly.</p>
            <table class="table table-hover align-middle w-100" id="videosTable">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Source</th>
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
                <form id="videoForm" enctype="multipart/form-data">
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
                            <label class="form-label d-block">Video Source</label>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="source_type" id="source_type_youtube" value="youtube" checked>
                                <label class="btn btn-outline-primary btn-sm" for="source_type_youtube">YouTube Link</label>
                                <input type="radio" class="btn-check" name="source_type" id="source_type_upload" value="upload">
                                <label class="btn btn-outline-primary btn-sm" for="source_type_upload">Upload File</label>
                            </div>
                            <div class="invalid-feedback" data-field="source_type"></div>
                        </div>

                        <div class="mb-3" id="youtube_url_group">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" name="youtube_url" id="video_youtube_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                            <div class="invalid-feedback" data-field="youtube_url"></div>
                        </div>

                        <div class="mb-3 d-none" id="video_file_group">
                            <label class="form-label">Video File</label>
                            <input type="file" name="video_file" id="video_file" class="form-control" accept="video/mp4,video/webm,video/quicktime,video/ogg">
                            <div class="form-text">MP4, WebM, MOV, or OGG — up to 40MB.</div>
                            <div class="invalid-feedback" data-field="video_file"></div>
                            <video id="video_file_preview" class="mt-2 rounded border d-none" width="200" controls></video>
                        </div>

                        <div class="mb-3 d-none" id="thumbnail_file_group">
                            <label class="form-label">Thumbnail Image <span class="text-muted">(optional)</span></label>
                            <input type="file" name="thumbnail_file" id="thumbnail_file" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Shown as the poster/cover before the video plays.</div>
                            <div class="invalid-feedback" data-field="thumbnail_file"></div>
                            <img id="thumbnail_file_preview" src="" alt="Preview" class="mt-2 rounded border d-none" width="140" height="80" style="object-fit:cover;">
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
            {
                data: null,
                render: (row) => row.source_type === 'upload'
                    ? `<span class="badge text-bg-info">Uploaded</span>`
                    : `<a href="${row.youtube_url}" target="_blank" rel="noopener" class="text-truncate d-inline-block" style="max-width:200px;">${row.youtube_url}</a>`,
            },
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

    function toggleSourceFields() {
        const isUpload = $('input[name="source_type"]:checked').val() === 'upload';
        $('#youtube_url_group').toggleClass('d-none', isUpload);
        $('#video_file_group, #thumbnail_file_group').toggleClass('d-none', ! isUpload);
        $('#video_youtube_url').prop('required', ! isUpload);
    }

    $('input[name="source_type"]').on('change', toggleSourceFields);

    $('#video_file').on('change', function () {
        const file = this.files[0];
        if (! file) return;
        $('#video_file_preview').attr('src', URL.createObjectURL(file)).removeClass('d-none');
    });

    $('#thumbnail_file').on('change', function () {
        const file = this.files[0];
        if (! file) return;

        const reader = new FileReader();
        reader.onload = (e) => $('#thumbnail_file_preview').attr('src', e.target.result).removeClass('d-none');
        reader.readAsDataURL(file);
    });

    $('#btnAddVideo').on('click', function () {
        clearErrors();
        $('#videoForm')[0].reset();
        $('#video_id').val('');
        $('#video_file_preview, #thumbnail_file_preview').addClass('d-none');
        $('#videoModalTitle').text('Add Video');
        toggleSourceFields();
    });

    $('#videosTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(videoEditUrl(id), function (res) {
            const v = res.video;

            $('#video_id').val(v.id);
            $('#video_title').val(v.title);
            $(`input[name="source_type"][value="${v.source_type}"]`).prop('checked', true);
            $('#video_youtube_url').val(v.youtube_url);
            $('#video_status').val(v.status);
            $('#video_sort_order').val(v.sort_order);
            $('#video_file_preview, #thumbnail_file_preview').addClass('d-none');
            if (v.video_url) $('#video_file_preview').attr('src', v.video_url).removeClass('d-none');
            if (v.thumbnail_url) $('#thumbnail_file_preview').attr('src', v.thumbnail_url).removeClass('d-none');
            toggleSourceFields();
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

        // File inputs are present, so this must go as multipart/form-data —
        // and PHP can't parse a multipart PUT body, hence the classic
        // Laravel workaround: always POST, with `_method` telling the
        // router which verb to actually dispatch to.
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

    toggleSourceFields();
});
</script>
@endpush
