@extends('admin.layouts.app')

@section('title', 'Media Sub Categories')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Media Sub Categories</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#subcategoryModal" id="btnAddSubcategory">
                <i class="bi bi-plus-lg"></i> Add Sub Category
            </button>
        </div>
        <div class="card-body">
            @if ($parents->isEmpty())
                <div class="alert alert-warning mb-0">
                    You need at least one top-level category before you can create a sub-category.
                    <a href="{{ route('admin.categories.index') }}">Create one here.</a>
                </div>
            @else
                <table class="table table-hover align-middle w-100" id="subcategoriesTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Parent Category</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="modal fade" id="subcategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="subcategoryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="subcategoryModalTitle">Add Sub Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="subcategory_id">

                        <div class="mb-3">
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" id="subcategory_parent_id" class="form-select" required>
                                <option value="">Select a top-level category</option>
                                @foreach ($parents as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-field="parent_id"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="subcategory_name" class="form-control" required>
                            <div class="invalid-feedback" data-field="name"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="subcategory_sort_order" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="subcategory_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
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
    if (! $('#subcategoriesTable').length) return;

    const subcategoryModal = new bootstrap.Modal('#subcategoryModal');

    // route() is resolved server-side against the real request, so it stays
    // correct under a subfolder deployment — a hardcoded "/admin/..." path
    // would not.
    const subcategoryEditUrl = (id) => '{{ route('admin.subcategories.edit', ['__ID__']) }}'.replace('__ID__', id);
    const subcategoryResourceUrl = (id) => '{{ route('admin.subcategories.update', ['__ID__']) }}'.replace('__ID__', id);

    const table = $('#subcategoriesTable').DataTable({
        ajax: {
            url: '{{ route('admin.subcategories.data') }}',
        },
        columns: [
            { data: 'name' },
            { data: 'parent', defaultContent: '—' },
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
        $('#subcategoryForm .invalid-feedback').text('');
        $('#subcategoryForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#subcategoryForm [name="${field}"]`).addClass('is-invalid');
            $(`#subcategoryForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    $('#btnAddSubcategory').on('click', function () {
        clearErrors();
        $('#subcategoryForm')[0].reset();
        $('#subcategory_id').val('');
        $('#subcategoryModalTitle').text('Add Sub Category');
    });

    $('#subcategoriesTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(subcategoryEditUrl(id), function (res) {
            const c = res.category;

            $('#subcategory_id').val(c.id);
            $('#subcategory_parent_id').val(c.parent_id);
            $('#subcategory_name').val(c.name);
            $('#subcategory_sort_order').val(c.sort_order);
            $('#subcategory_status').val(c.status);
            $('#subcategoryModalTitle').text('Edit Sub Category');
            subcategoryModal.show();
        });
    });

    $('#subcategoriesTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this sub-category? This cannot be undone.')) return;

        $.ajax({
            url: subcategoryResourceUrl(id),
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this sub-category.');
            },
        });
    });

    $('#subcategoryForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#subcategory_id').val();
        const url = id ? subcategoryResourceUrl(id) : '{{ route('admin.subcategories.store') }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url,
            method,
            data: $(this).serialize(),
            success: function () {
                subcategoryModal.hide();
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
