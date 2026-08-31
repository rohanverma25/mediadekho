@extends('admin.layouts.app')

@section('title', 'Media Inventory')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form id="filterForm" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-1">Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Frequency</label>
                    <select name="frequency_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($frequencies as $frequency)
                            <option value="{{ $frequency->id }}">{{ $frequency->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Language</label>
                    <select name="language_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($languages as $language)
                            <option value="{{ $language->id }}">{{ $language->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm">
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Media Inventory</span>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.media-inventory.export') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-download"></i> Export CSV
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload"></i> Import CSV
                </button>
                <a href="{{ route('admin.media-inventory.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Add Inventory
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="inventoryTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Frequency</th>
                        <th>Language</th>
                        <th>MSME Startups</th>
                        <th>Brand/Company</th>
                        <th>B2B</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.media-inventory.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Import Media Inventory (CSV)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">
                            Required columns: <code>title</code>, <code>category</code>.
                            Rows are matched by <code>title</code> — existing items get updated, new ones get created.
                            Optional columns: <code>subcategory</code>, <code>frequency</code>, <code>language</code>, <code>short_description</code>, <code>status</code>.
                            Optional pricing columns: <code>base_price</code>, <code>retail_price</code>, <code>b2c_price</code>, <code>b2b_price</code>, <code>enterprise_price</code>.
                        </p>
                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    // route() is resolved server-side against the real request, so it stays
    // correct under a subfolder deployment — a hardcoded "/admin/..." path
    // would not.
    const inventoryShowUrl = (id) => '{{ route('admin.media-inventory.show', ['__ID__']) }}'.replace('__ID__', id);
    const inventoryEditUrl = (id) => '{{ route('admin.media-inventory.edit', ['__ID__']) }}'.replace('__ID__', id);
    const inventoryResourceUrl = (id) => '{{ route('admin.media-inventory.update', ['__ID__']) }}'.replace('__ID__', id);

    function money(value) {
        return value === null || value === undefined ? '—' : '₹' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    const table = $('#inventoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.media-inventory.data') }}',
            data: function (d) {
                $('#filterForm').serializeArray().forEach((field) => { d[field.name] = field.value; });
            },
        },
        columns: [
            { data: 'cover_image_url', orderable: false, render: (url) => url ? `<img src="${url}" class="rounded" width="48" height="48" style="object-fit:cover;">` : '<span class="text-muted">—</span>' },
            { data: 'title' },
            { data: 'category', defaultContent: '—' },
            { data: 'frequency', defaultContent: '—' },
            { data: 'language', defaultContent: '—' },
            { data: 'retail_price', render: money },
            { data: 'b2c_price', render: money },
            { data: 'b2b_price', render: money },
            { data: 'status', render: (status) => {
                const color = status === 'published' ? 'success' : (status === 'draft' ? 'secondary' : 'dark');
                return `<span class="badge text-bg-${color}">${status}</span>`;
            }},
            { data: 'created_by', defaultContent: '—' },
            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: (row) => `
                    <a href="${inventoryShowUrl(row.id)}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    <a href="${inventoryEditUrl(row.id)}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${row.id}"><i class="bi bi-trash"></i></button>
                `,
            },
        ],
    });

    $('#filterForm').on('change', function () {
        table.ajax.reload();
    });

    $('#inventoryTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this inventory item? This cannot be undone.')) return;

        const token = $('meta[name="csrf-token"]').attr('content');
        const form = $('<form>', { method: 'POST', action: inventoryResourceUrl(id) });
        form.append($('<input>', { type: 'hidden', name: '_token', value: token }));
        form.append($('<input>', { type: 'hidden', name: '_method', value: 'DELETE' }));
        $('body').append(form);
        form.submit();
    });
});
</script>
@endpush
