@extends('admin.layouts.app')

@section('title', 'Language')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Language</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#languageModal" id="btnAddLanguage">
                <i class="bi bi-plus-lg"></i> Add Language
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="languagesTable">
                <thead>
                    <tr>
                        <th>Language</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="languageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="languageForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="languageModalTitle">Add Language</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="language_id">

                        <div class="mb-3">
                            <label class="form-label">Language</label>
                            <input type="text" name="name" id="language_name" class="form-control" placeholder="e.g. English, Hindi, Spanish" required>
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
    const languageModal = new bootstrap.Modal('#languageModal');

    const table = $('#languagesTable').DataTable({
        ajax: {
            url: '{{ route('admin.languages.data') }}',
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
        $('#languageForm .invalid-feedback').text('');
        $('#languageForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#languageForm [name="${field}"]`).addClass('is-invalid');
            $(`#languageForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    $('#btnAddLanguage').on('click', function () {
        clearErrors();
        $('#languageForm')[0].reset();
        $('#language_id').val('');
        $('#languageModalTitle').text('Add Language');
    });

    $('#languagesTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(`/admin/languages/${id}/edit`, function (res) {
            $('#language_id').val(res.language.id);
            $('#language_name').val(res.language.name);
            $('#languageModalTitle').text('Edit Language');
            languageModal.show();
        });
    });

    $('#languagesTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this language? This cannot be undone.')) return;

        $.ajax({
            url: `/admin/languages/${id}`,
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this language.');
            },
        });
    });

    $('#languageForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#language_id').val();
        const url = id ? `/admin/languages/${id}` : '{{ route('admin.languages.store') }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url,
            method,
            data: $(this).serialize(),
            success: function () {
                languageModal.hide();
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
