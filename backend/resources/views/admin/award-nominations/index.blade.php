@extends('admin.layouts.app')

@section('title', 'Award Nominations')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Award Nominations</span>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="nominationsTable">
                <thead>
                    <tr>
                        <th>Submitted</th>
                        <th>Award</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Submitted By</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="nominationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nomination Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Award</dt><dd class="col-sm-9" id="nom_view_award"></dd>
                        <dt class="col-sm-3">Name</dt><dd class="col-sm-9" id="nom_view_name"></dd>
                        <dt class="col-sm-3">Email</dt><dd class="col-sm-9" id="nom_view_email"></dd>
                        <dt class="col-sm-3">Phone</dt><dd class="col-sm-9" id="nom_view_phone"></dd>
                        <dt class="col-sm-3">Company</dt><dd class="col-sm-9" id="nom_view_company"></dd>
                        <dt class="col-sm-3">Submitted By</dt><dd class="col-sm-9" id="nom_view_submitted_by"></dd>
                        <dt class="col-sm-3">Submitted</dt><dd class="col-sm-9" id="nom_view_created_at"></dd>
                        <dt class="col-sm-3">Nomination Details</dt><dd class="col-sm-9" id="nom_view_description" style="white-space: pre-wrap;"></dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <select id="nom_view_status" class="form-select form-select-sm w-auto">
                        <option value="new">New</option>
                        <option value="shortlisted">Shortlisted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveNominationStatus">Save Status</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    const nominationModal = new bootstrap.Modal('#nominationModal');
    let activeNominationId = null;

    // route() is resolved server-side against the real request, so it stays
    // correct under a subfolder deployment — a hardcoded "/admin/..." path
    // would not.
    const nominationResourceUrl = (id) => '{{ route('admin.award-nominations.update', ['__ID__']) }}'.replace('__ID__', id);

    const statusBadge = (status) => {
        const map = { new: 'danger', shortlisted: 'success', rejected: 'secondary' };
        return `<span class="badge text-bg-${map[status] ?? 'secondary'}">${status}</span>`;
    };

    const table = $('#nominationsTable').DataTable({
        ajax: {
            url: '{{ route('admin.award-nominations.data') }}',
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'created_at' },
            { data: 'award_title', defaultContent: '—' },
            { data: 'name' },
            { data: 'email' },
            { data: 'phone', defaultContent: '—' },
            { data: 'submitted_by', render: (v) => v ? `<span class="badge text-bg-info">${v}</span>` : '<span class="text-muted">Guest</span>' },
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

    $('#nominationsTable').on('click', '.btn-view', function () {
        const row = rowsById[$(this).data('id')];
        if (! row) return;

        activeNominationId = row.id;
        $('#nom_view_award').text(row.award_title || '—');
        $('#nom_view_name').text(row.name);
        $('#nom_view_email').text(row.email);
        $('#nom_view_phone').text(row.phone || '—');
        $('#nom_view_company').text(row.company_name || '—');
        $('#nom_view_submitted_by').text(row.submitted_by || 'Guest (not logged in)');
        $('#nom_view_created_at').text(row.created_at);
        $('#nom_view_description').text(row.description);
        $('#nom_view_status').val(row.status);
        nominationModal.show();
    });

    $('#btnSaveNominationStatus').on('click', function () {
        if (! activeNominationId) return;

        $.ajax({
            url: nominationResourceUrl(activeNominationId),
            method: 'PUT',
            data: { status: $('#nom_view_status').val() },
            success: function () {
                nominationModal.hide();
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to update this nomination.');
            },
        });
    });

    $('#nominationsTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this nomination? This cannot be undone.')) return;

        $.ajax({
            url: nominationResourceUrl(id),
            method: 'DELETE',
            success: function () {
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this nomination.');
            },
        });
    });
});
</script>
@endpush
