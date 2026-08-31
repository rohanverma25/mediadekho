@extends('admin.layouts.app')

@section('title', 'Media Listing Requests')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Media Listing Requests</span>
        </div>
        <div class="card-body">
            <p class="small text-muted">Submitted from the public "List Your Media" page — vendors pitching their own media inventory to be listed on the platform.</p>
            <table class="table table-hover align-middle w-100" id="requestsTable">
                <thead>
                    <tr>
                        <th>Submitted</th>
                        <th>Media / Property</th>
                        <th>Company</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="requestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Media Listing Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Media / Property</dt><dd class="col-sm-9" id="req_view_media_title"></dd>
                        <dt class="col-sm-3">Media Type</dt><dd class="col-sm-9" id="req_view_media_type"></dd>
                        <dt class="col-sm-3">Location</dt><dd class="col-sm-9" id="req_view_location"></dd>
                        <dt class="col-sm-3">Approx. Rate</dt><dd class="col-sm-9" id="req_view_rate"></dd>
                        <dt class="col-sm-3">Company</dt><dd class="col-sm-9" id="req_view_company"></dd>
                        <dt class="col-sm-3">Contact Name</dt><dd class="col-sm-9" id="req_view_contact_name"></dd>
                        <dt class="col-sm-3">Email</dt><dd class="col-sm-9" id="req_view_email"></dd>
                        <dt class="col-sm-3">Phone</dt><dd class="col-sm-9" id="req_view_phone"></dd>
                        <dt class="col-sm-3">Photo</dt><dd class="col-sm-9" id="req_view_image"></dd>
                        <dt class="col-sm-3">Media Kit</dt><dd class="col-sm-9" id="req_view_media_kit"></dd>
                        <dt class="col-sm-3">Submitted</dt><dd class="col-sm-9" id="req_view_created_at"></dd>
                        <dt class="col-sm-3">Details</dt><dd class="col-sm-9" id="req_view_description" style="white-space: pre-wrap;"></dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <select id="req_view_status" class="form-select form-select-sm w-auto">
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="listed">Listed</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveRequestStatus">Save Status</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    const requestModal = new bootstrap.Modal('#requestModal');
    let activeRequestId = null;

    const requestResourceUrl = (id) => '{{ route('admin.media-listing-requests.update', ['__ID__']) }}'.replace('__ID__', id);

    const statusBadge = (status) => {
        const map = { new: 'danger', contacted: 'warning', listed: 'success', rejected: 'secondary' };
        return `<span class="badge text-bg-${map[status] ?? 'secondary'}">${status}</span>`;
    };

    const table = $('#requestsTable').DataTable({
        ajax: {
            url: '{{ route('admin.media-listing-requests.data') }}',
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'created_at' },
            { data: 'media_title' },
            { data: 'company_name' },
            { data: 'contact_name' },
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

    $('#requestsTable').on('click', '.btn-view', function () {
        const row = rowsById[$(this).data('id')];
        if (! row) return;

        activeRequestId = row.id;
        $('#req_view_media_title').text(row.media_title);
        $('#req_view_media_type').text(row.media_type || '—');
        $('#req_view_location').text(row.location || '—');
        $('#req_view_rate').text(row.approximate_rate || '—');
        $('#req_view_company').text(row.company_name);
        $('#req_view_contact_name').text(row.contact_name);
        $('#req_view_email').html(`<a href="mailto:${row.email}">${row.email}</a>`);
        $('#req_view_phone').html(`<a href="tel:${row.phone}">${row.phone}</a>`);
        $('#req_view_image').html(row.image_url ? `<img src="${row.image_url}" class="rounded border" width="120" style="object-fit:cover;">` : '—');
        $('#req_view_media_kit').html(row.media_kit_url ? `<a href="${row.media_kit_url}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-arrow-down"></i> ${row.media_kit_original_name || 'Download'}</a>` : '—');
        $('#req_view_created_at').text(row.created_at);
        $('#req_view_description').text(row.description || '—');
        $('#req_view_status').val(row.status);
        requestModal.show();
    });

    $('#btnSaveRequestStatus').on('click', function () {
        if (! activeRequestId) return;

        $.ajax({
            url: requestResourceUrl(activeRequestId),
            method: 'PUT',
            data: { status: $('#req_view_status').val() },
            success: function () {
                requestModal.hide();
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to update this request.');
            },
        });
    });

    $('#requestsTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this request? This cannot be undone.')) return;

        $.ajax({
            url: requestResourceUrl(id),
            method: 'DELETE',
            success: function () {
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this request.');
            },
        });
    });
});
</script>
@endpush
