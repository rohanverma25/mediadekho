@extends('admin.layouts.app')

@section('title', 'Careers')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Job Openings</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#jobModal" id="btnAddJob">
                <i class="bi bi-plus-lg"></i> Add Job
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="jobsTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Applications</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="jobModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="jobForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="jobModalTitle">Add Job</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="job_id">

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="job_title" class="form-control" required>
                            <div class="invalid-feedback" data-field="title"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="job_description" class="form-control" rows="6"></textarea>
                            <div class="invalid-feedback" data-field="description"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" id="job_department" class="form-control" placeholder="e.g. Sales">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" id="job_location" class="form-control" placeholder="e.g. Ahmedabad / Remote">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Type</label>
                                <select name="type" id="job_type" class="form-select" required>
                                    <option value="full-time">Full-Time</option>
                                    <option value="part-time">Part-Time</option>
                                    <option value="internship">Internship</option>
                                    <option value="contract">Contract</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="job_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="job_sort_order" class="form-control" value="0" min="0">
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
    const jobModal = new bootstrap.Modal('#jobModal');

    $('#job_description').summernote({
        height: 220,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['codeview']],
        ],
    });

    const table = $('#jobsTable').DataTable({
        ajax: {
            url: '{{ route('admin.jobs.data') }}',
        },
        columns: [
            { data: 'title' },
            { data: 'department', defaultContent: '—' },
            { data: 'location', defaultContent: '—' },
            { data: 'type' },
            { data: 'applications_count', render: (count) => count > 0 ? `<span class="badge text-bg-info">${count}</span>` : '—' },
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
        $('#jobForm .invalid-feedback').text('');
        $('#jobForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#jobForm [name="${field}"]`).addClass('is-invalid');
            $(`#jobForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    $('#btnAddJob').on('click', function () {
        clearErrors();
        $('#jobForm')[0].reset();
        $('#job_id').val('');
        $('#job_description').summernote('code', '');
        $('#job_type').val('full-time');
        $('#job_status').val('active');
        $('#jobModalTitle').text('Add Job');
    });

    $('#jobsTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(`/admin/jobs/${id}/edit`, function (res) {
            const j = res.job;

            $('#job_id').val(j.id);
            $('#job_title').val(j.title);
            $('#job_description').summernote('code', j.description ?? '');
            $('#job_department').val(j.department);
            $('#job_location').val(j.location);
            $('#job_type').val(j.type);
            $('#job_status').val(j.status);
            $('#job_sort_order').val(j.sort_order);
            $('#jobModalTitle').text('Edit Job');
            jobModal.show();
        });
    });

    $('#jobsTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this job? Any applications submitted for it will be deleted too. This cannot be undone.')) return;

        $.ajax({
            url: `/admin/jobs/${id}`,
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this job.');
            },
        });
    });

    $('#jobForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        $('#job_description').val($('#job_description').summernote('code'));

        const id = $('#job_id').val();
        const url = id ? `/admin/jobs/${id}` : '{{ route('admin.jobs.store') }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url,
            method,
            data: $(this).serialize(),
            success: function () {
                jobModal.hide();
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
