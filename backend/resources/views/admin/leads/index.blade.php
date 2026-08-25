@extends('admin.layouts.app')

@section('title', 'Contact Leads')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Contact Leads</span>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="leadsTable">
                <thead>
                    <tr>
                        <th>Submitted</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Subject</th>
                        <th>Account</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="leadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lead Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Name</dt><dd class="col-sm-9" id="lead_view_name"></dd>
                        <dt class="col-sm-3">Email</dt><dd class="col-sm-9" id="lead_view_email"></dd>
                        <dt class="col-sm-3">Phone</dt><dd class="col-sm-9" id="lead_view_phone"></dd>
                        <dt class="col-sm-3">Company</dt><dd class="col-sm-9" id="lead_view_company"></dd>
                        <dt class="col-sm-3">Location</dt><dd class="col-sm-9" id="lead_view_location"></dd>
                        <dt class="col-sm-3">Subject</dt><dd class="col-sm-9" id="lead_view_subject"></dd>
                        <dt class="col-sm-3">Submitted</dt><dd class="col-sm-9" id="lead_view_created_at"></dd>
                        <dt class="col-sm-3">Description</dt><dd class="col-sm-9" id="lead_view_description" style="white-space: pre-wrap;"></dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <select id="lead_view_status" class="form-select form-select-sm w-auto">
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="closed">Closed</option>
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveLeadStatus">Save Status</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    const leadModal = new bootstrap.Modal('#leadModal');
    let activeLeadId = null;

    // route() is resolved server-side against the real request, so it stays
    // correct under a subfolder deployment — a hardcoded "/admin/..." path
    // would not.
    const leadResourceUrl = (id) => '{{ route('admin.leads.update', ['__ID__']) }}'.replace('__ID__', id);

    const statusBadge = (status) => {
        const map = { new: 'danger', contacted: 'warning', closed: 'success' };
        return `<span class="badge text-bg-${map[status] ?? 'secondary'}">${status}</span>`;
    };

    const table = $('#leadsTable').DataTable({
        ajax: {
            url: '{{ route('admin.leads.data') }}',
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'created_at' },
            { data: 'name' },
            { data: 'email' },
            { data: 'phone', defaultContent: '—' },
            { data: 'company_name', defaultContent: '—' },
            { data: 'subject', defaultContent: '—' },
            { data: 'customer_name', defaultContent: '<span class="text-muted">Guest</span>' },
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

    // The leads list is small back-office data — reuse the already-fetched
    // row data for the detail modal instead of a second request.
    let rowsById = {};
    table.on('xhr', function () {
        const json = table.ajax.json();
        rowsById = Object.fromEntries((json?.data ?? []).map((r) => [r.id, r]));
    });

    $('#leadsTable').on('click', '.btn-view', function () {
        const row = rowsById[$(this).data('id')];
        if (! row) return;

        activeLeadId = row.id;
        $('#lead_view_name').text(row.name);
        $('#lead_view_email').text(row.email);
        $('#lead_view_phone').text(row.phone || '—');
        $('#lead_view_company').text(row.company_name || '—');
        $('#lead_view_location').text(row.location || '—');
        $('#lead_view_subject').text(row.subject || '—');
        $('#lead_view_created_at').text(row.created_at);
        $('#lead_view_description').text(row.description);
        $('#lead_view_status').val(row.status);
        leadModal.show();
    });

    $('#btnSaveLeadStatus').on('click', function () {
        if (! activeLeadId) return;

        $.ajax({
            url: leadResourceUrl(activeLeadId),
            method: 'PUT',
            data: { status: $('#lead_view_status').val() },
            success: function () {
                leadModal.hide();
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to update this lead.');
            },
        });
    });

    $('#leadsTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this lead? This cannot be undone.')) return;

        $.ajax({
            url: leadResourceUrl(id),
            method: 'DELETE',
            success: function () {
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this lead.');
            },
        });
    });
});
</script>
@endpush
