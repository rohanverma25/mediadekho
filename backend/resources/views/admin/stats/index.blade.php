@extends('admin.layouts.app')

@section('title', 'Stats')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Stats</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#statModal" id="btnAddStat">
                <i class="bi bi-plus-lg"></i> Add Stat
            </button>
        </div>
        <div class="card-body">
            <p class="small text-muted">Shown as a row of counters (e.g. "700+ Marketing &amp; PR Campaigns") on the homepage.</p>
            <table class="table table-hover align-middle w-100" id="statsTable">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Value</th>
                        <th>Label</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="statModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="statForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="statModalTitle">Add Stat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="stat_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Value</label>
                                <input type="text" name="value" id="stat_value" class="form-control" placeholder="e.g. 700+" required>
                                <div class="invalid-feedback" data-field="value"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Label</label>
                                <input type="text" name="label" id="stat_label" class="form-control" placeholder="e.g. Marketing & PR Campaigns" required>
                                <div class="invalid-feedback" data-field="label"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Icon <span class="text-muted">(Font Awesome class, e.g. fa-solid fa-lightbulb)</span></label>
                            <input type="text" name="icon" id="stat_icon" class="form-control" placeholder="fa-solid fa-lightbulb">
                            <div class="invalid-feedback" data-field="icon"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="stat_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="stat_sort_order" class="form-control" value="0" min="0">
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
    const statModal = new bootstrap.Modal('#statModal');

    // route() is resolved server-side against the real request, so it stays
    // correct under a subfolder deployment — a hardcoded "/admin/..." path
    // would not.
    const statEditUrl = (id) => '{{ route('admin.stats.edit', ['__ID__']) }}'.replace('__ID__', id);
    const statResourceUrl = (id) => '{{ route('admin.stats.update', ['__ID__']) }}'.replace('__ID__', id);

    const table = $('#statsTable').DataTable({
        ajax: {
            url: '{{ route('admin.stats.data') }}',
        },
        columns: [
            { data: 'icon', orderable: false, render: (icon) => icon ? `<i class="${icon}"></i>` : '<span class="text-muted">—</span>' },
            { data: 'value' },
            { data: 'label' },
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
        $('#statForm .invalid-feedback').text('');
        $('#statForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#statForm [name="${field}"]`).addClass('is-invalid');
            $(`#statForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    $('#btnAddStat').on('click', function () {
        clearErrors();
        $('#statForm')[0].reset();
        $('#stat_id').val('');
        $('#statModalTitle').text('Add Stat');
    });

    $('#statsTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(statEditUrl(id), function (res) {
            const s = res.stat;

            $('#stat_id').val(s.id);
            $('#stat_value').val(s.value);
            $('#stat_label').val(s.label);
            $('#stat_icon').val(s.icon);
            $('#stat_status').val(s.status);
            $('#stat_sort_order').val(s.sort_order);
            $('#statModalTitle').text('Edit Stat');
            statModal.show();
        });
    });

    $('#statsTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this stat? This cannot be undone.')) return;

        $.ajax({
            url: statResourceUrl(id),
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this stat.');
            },
        });
    });

    $('#statForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#stat_id').val();
        const url = id ? statResourceUrl(id) : '{{ route('admin.stats.store') }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url,
            method,
            data: $(this).serialize(),
            success: function () {
                statModal.hide();
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
