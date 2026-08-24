@extends('admin.layouts.app')

@section('title', 'Job Applications')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Job Applications</span>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="applicationsTable">
                <thead>
                    <tr>
                        <th>Submitted</th>
                        <th>Job</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Resume</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="applicationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Application Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Job</dt><dd class="col-sm-9" id="app_view_job"></dd>
                        <dt class="col-sm-3">Name</dt><dd class="col-sm-9" id="app_view_name"></dd>
                        <dt class="col-sm-3">Email</dt><dd class="col-sm-9" id="app_view_email"></dd>
                        <dt class="col-sm-3">Phone</dt><dd class="col-sm-9" id="app_view_phone"></dd>
                        <dt class="col-sm-3">Resume</dt><dd class="col-sm-9" id="app_view_resume"></dd>
                        <dt class="col-sm-3">Submitted By</dt><dd class="col-sm-9" id="app_view_submitted_by"></dd>
                        <dt class="col-sm-3">Submitted</dt><dd class="col-sm-9" id="app_view_created_at"></dd>
                        <dt class="col-sm-3">Cover Letter</dt><dd class="col-sm-9" id="app_view_cover_letter" style="white-space: pre-wrap;"></dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <select id="app_view_status" class="form-select form-select-sm w-auto">
                        <option value="new">New</option>
                        <option value="shortlisted">Shortlisted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveApplicationStatus">Save Status</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    const applicationModal = new bootstrap.Modal('#applicationModal');
    let activeApplicationId = null;

    const statusBadge = (status) => {
        const map = { new: 'danger', shortlisted: 'success', rejected: 'secondary' };
        return `<span class="badge text-bg-${map[status] ?? 'secondary'}">${status}</span>`;
    };

    const table = $('#applicationsTable').DataTable({
        ajax: {
            url: '{{ route('admin.job-applications.data') }}',
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'created_at' },
            { data: 'job_title', defaultContent: '—' },
            { data: 'name' },
            { data: 'email' },
            { data: 'phone', defaultContent: '—' },
            { data: 'resume_url', orderable: false, render: (url) => url ? `<a href="${url}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-arrow-down"></i> Download</a>` : '<span class="text-muted">—</span>' },
            { data: 'status', render: statusBadge },
            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: (row) => `
                    <button class="btn btn-sm btn-outline-primary btn-view" data-id="${row.id}"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${row.id}"><i class="bi bi-trash"></i></button>
                `,
            },
        ],
    });

    let rowsById = {};
    table.on('xhr', function () {
        const json = table.ajax.json();
        rowsById = Object.fromEntries((json?.data ?? []).map((r) => [r.id, r]));
    });

    $('#applicationsTable').on('click', '.btn-view', function () {
        const row = rowsById[$(this).data('id')];
        if (! row) return;

        activeApplicationId = row.id;
        $('#app_view_job').text(row.job_title || '—');
        $('#app_view_name').text(row.name);
        $('#app_view_email').text(row.email);
        $('#app_view_phone').text(row.phone || '—');
        $('#app_view_resume').html(row.resume_url ? `<a href="${row.resume_url}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-arrow-down"></i> ${row.resume_original_name || 'Download'}</a>` : '—');
        $('#app_view_submitted_by').text(row.submitted_by || 'Guest (not logged in)');
        $('#app_view_created_at').text(row.created_at);
        $('#app_view_cover_letter').text(row.cover_letter || '—');
        $('#app_view_status').val(row.status);
        applicationModal.show();
    });

    $('#btnSaveApplicationStatus').on('click', function () {
        if (! activeApplicationId) return;

        $.ajax({
            url: `/admin/job-applications/${activeApplicationId}`,
            method: 'PUT',
            data: { status: $('#app_view_status').val() },
            success: function () {
                applicationModal.hide();
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to update this application.');
            },
        });
    });

    $('#applicationsTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this application? This cannot be undone.')) return;

        $.ajax({
            url: `/admin/job-applications/${id}`,
            method: 'DELETE',
            success: function () {
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this application.');
            },
        });
    });
});
</script>
@endpush
