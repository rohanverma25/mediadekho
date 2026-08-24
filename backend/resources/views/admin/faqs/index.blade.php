@extends('admin.layouts.app')

@section('title', 'FAQs')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>FAQs</span>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#faqModal" id="btnAddFaq">
                <i class="bi bi-plus-lg"></i> Add FAQ
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle w-100" id="faqsTable">
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>Answer</th>
                        <th>Linked To</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="faqModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="faqForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="faqModalTitle">Add FAQ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="faq_id">

                        <div class="mb-3">
                            <label class="form-label">Question</label>
                            <input type="text" name="question" id="faq_question" class="form-control" required>
                            <div class="invalid-feedback" data-field="question"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Answer</label>
                            <textarea name="answer" id="faq_answer" class="form-control" rows="4"></textarea>
                            <div class="invalid-feedback" data-field="answer"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Link To</label>
                                <select name="link_type" id="faq_link_type" class="form-select">
                                    <option value="">None</option>
                                    <option value="category">Category</option>
                                    <option value="inventory">Media Inventory</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" id="faq_link_id_label">Select item</label>
                                <select name="link_id" id="faq_link_id" class="form-select" disabled>
                                    <option value="">— Choose Link To first —</option>
                                </select>
                                <div class="invalid-feedback" data-field="link_id"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="faq_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="faq_sort_order" class="form-control" value="0" min="0">
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
    const faqModal = new bootstrap.Modal('#faqModal');

    const categories = @json($categories->map(fn ($c) => ['id' => $c->id, 'label' => $c->name]));
    const inventories = @json($inventories->map(fn ($i) => ['id' => $i->id, 'label' => $i->title]));

    $('#faq_answer').summernote({
        height: 180,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['codeview']],
        ],
    });

    function loadLinkOptions(type, selected = null) {
        const select = $('#faq_link_id');
        select.empty();

        if (! type) {
            select.append('<option value="">— Choose Link To first —</option>').prop('disabled', true);
            $('#faq_link_id_label').text('Select item');
            return;
        }

        const options = type === 'category' ? categories : inventories;
        $('#faq_link_id_label').text(type === 'category' ? 'Category' : 'Media Inventory Item');
        select.prop('disabled', false).append('<option value="">Select…</option>');
        options.forEach((o) => {
            select.append(`<option value="${o.id}" ${String(o.id) === String(selected) ? 'selected' : ''}>${o.label}</option>`);
        });
    }

    $('#faq_link_type').on('change', function () {
        loadLinkOptions($(this).val());
    });

    const table = $('#faqsTable').DataTable({
        ajax: {
            url: '{{ route('admin.faqs.data') }}',
        },
        columns: [
            { data: 'question' },
            { data: 'answer_preview' },
            { data: 'linked_label', defaultContent: '—' },
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
        $('#faqForm .invalid-feedback').text('');
        $('#faqForm .is-invalid').removeClass('is-invalid');
    }

    function showErrors(errors) {
        Object.keys(errors).forEach((field) => {
            $(`#faqForm [name="${field}"]`).addClass('is-invalid');
            $(`#faqForm .invalid-feedback[data-field="${field}"]`).text(errors[field][0]);
        });
    }

    $('#btnAddFaq').on('click', function () {
        clearErrors();
        $('#faqForm')[0].reset();
        $('#faq_id').val('');
        $('#faq_answer').summernote('code', '');
        loadLinkOptions('');
        $('#faqModalTitle').text('Add FAQ');
    });

    $('#faqsTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(`/admin/faqs/${id}/edit`, function (res) {
            const f = res.faq;

            $('#faq_id').val(f.id);
            $('#faq_question').val(f.question);
            $('#faq_answer').summernote('code', f.answer ?? '');
            $('#faq_status').val(f.status);
            $('#faq_sort_order').val(f.sort_order);
            $('#faq_link_type').val(f.link_type ?? '');
            loadLinkOptions(f.link_type, f.link_id);
            $('#faqModalTitle').text('Edit FAQ');
            faqModal.show();
        });
    });

    $('#faqsTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (! confirm('Delete this FAQ? This cannot be undone.')) return;

        $.ajax({
            url: `/admin/faqs/${id}`,
            method: 'DELETE',
            success: function () {
                table.ajax.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete this FAQ.');
            },
        });
    });

    $('#faqForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#faq_id').val();
        const url = id ? `/admin/faqs/${id}` : '{{ route('admin.faqs.store') }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url,
            method,
            data: $(this).serialize(),
            success: function () {
                faqModal.hide();
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
