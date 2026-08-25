@extends('admin.layouts.app')

@section('title', 'Frequency')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Frequency</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#frequencyModal" id="btnAddFrequency">
                <i class="bi bi-plus-lg"></i> Add Frequency
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="frequenciesTable">
                <thead>
                    <tr>
                        <th>Frequency</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="frequencyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="frequencyForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="frequencyModalTitle">Add Frequency</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="frequency_id">

                        <div class="mb-3">
                            <label class="form-label">Frequency</label>
                            <input type="text" name="name" id="frequency_name" class="form-control" placeholder="e.g. Daily, Weekly, Monthly" required>
                            <div class="invalid-feedback" data-field="name"></div>
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
    const frequencyModal = new bootstrap.Modal('#frequencyModal');

    // route() is resolved server-side against the real request, so it stays
    // correct under a subfolder deployment — a hardcoded "/admin/..." path
    // would not.
    const frequencyEditUrl = (id) => '{{ route('admin.frequencies.edit', ['__ID__']) }}'.replace('__ID__', id);
    const frequencyResourceUrl = (id) => '{{ route('admin.frequencies.update', ['__ID__']) }}'.replace('__ID__', id);

    const table = $('#frequenciesTable').DataTable({
        ajax: {
            url: '{{ route('admin.frequencies.data') }}',
        },
        columns: [
            { data: 'name' },
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
        $('#frequencyForm .invalid-feedback').text('');
        $('#frequencyForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#frequencyForm [name="${field}"]`).addClass('is-invalid');
            $(`#frequencyForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    $('#btnAddFrequency').on('click', function () {
        clearErrors();
        $('#frequencyForm')[0].reset();
        $('#frequency_id').val('');
        $('#frequencyModalTitle').text('Add Frequency');
    });

    $('#frequenciesTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(frequencyEditUrl(id), function (res) {
            $('#frequency_id').val(res.frequency.id);
            $('#frequency_name').val(res.frequency.name);
            $('#frequencyModalTitle').text('Edit Frequency');
            frequencyModal.show();
        });
    });

    $('#frequenciesTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this frequency? This cannot be undone.')) return;

        $.ajax({
            url: frequencyResourceUrl(id),
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this frequency.');
            },
        });
    });

    $('#frequencyForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#frequency_id').val();
        const url = id ? frequencyResourceUrl(id) : '{{ route('admin.frequencies.store') }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url,
            method,
            data: $(this).serialize(),
            success: function () {
                frequencyModal.hide();
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
