@php
    $old = fn ($field, $default = null) => old($field, $inventory?->{$field} ?? $default);
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
@endpush

<div class="card mb-3">
    <div class="card-header">General Information</div>
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">Select category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($old('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Sub Category</label>
            <select name="subcategory_id" id="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror">
                <option value="">None</option>
            </select>
            @error('subcategory_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Frequency</label>
            <select name="frequency_id" class="form-select @error('frequency_id') is-invalid @enderror" required>
                <option value="">Select frequency</option>
                @foreach ($frequencies as $frequency)
                    <option value="{{ $frequency->id }}" @selected($old('frequency_id') == $frequency->id)>{{ $frequency->name }}</option>
                @endforeach
            </select>
            @error('frequency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Language</label>
            <select name="language_id" class="form-select @error('language_id') is-invalid @enderror" required>
                <option value="">Select language</option>
                @foreach ($languages as $language)
                    <option value="{{ $language->id }}" @selected($old('language_id') == $language->id)>{{ $language->name }}</option>
                @endforeach
            </select>
            @error('language_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                @foreach (['draft', 'published', 'archived'] as $status)
                    <option value="{{ $status }}" @selected($old('status', 'draft') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Inventory Title</label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ $old('title') }}" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="form-label">Short Description</label>
            <input type="text" name="short_description" class="form-control @error('short_description') is-invalid @enderror" value="{{ $old('short_description') }}" maxlength="500">
            @error('short_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" id="inventory_description" class="form-control" rows="4">{{ $old('description') }}</textarea>
        </div>

        <div class="col-12">
            <label class="form-label">Image</label>
            <input type="file" name="image" id="inventory_image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <img id="inventory_image_preview" src="{{ $inventory?->image_url }}" alt="Preview" class="mt-2 rounded border {{ $inventory?->image_url ? '' : 'd-none' }}" width="140" height="140" style="object-fit:cover;">
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Key Insights</span>
        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddInsight"><i class="bi bi-plus-lg"></i> Add Insight</button>
    </div>
    <div class="card-body">
        <div id="keyInsightsRows">
            @forelse ($old('key_insights', $inventory?->keyInsights->map(fn ($i) => ['label' => $i->label, 'value' => $i->value])->all() ?? []) as $index => $insight)
                <div class="row g-2 mb-2 key-insight-row">
                    <div class="col-md-5">
                        <input type="text" name="key_insights[{{ $index }}][label]" class="form-control" placeholder="Label (e.g. Reach)" value="{{ $insight['label'] ?? '' }}">
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="key_insights[{{ $index }}][value]" class="form-control" placeholder="Value (e.g. 50,000)" value="{{ $insight['value'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger w-100 btn-remove-insight"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            @empty
            @endforelse
        </div>
        <p class="text-muted small mb-0" id="noInsightsHint" style="{{ $old('key_insights', $inventory?->keyInsights->isNotEmpty()) ? 'display:none;' : '' }}">No insights added yet.</p>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script>
$(function () {
    $('#inventory_description').summernote({
        height: 200,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['codeview']],
        ],
    });

    $('#inventory_image').on('change', function () {
        const file = this.files[0];
        if (! file) return;

        const reader = new FileReader();
        reader.onload = (e) => $('#inventory_image_preview').attr('src', e.target.result).removeClass('d-none');
        reader.readAsDataURL(file);
    });

    const subcategories = @json($categories->keyBy('id')->map(fn ($c) => $c->children->map(fn ($child) => ['id' => $child->id, 'name' => $child->name])));
    const selectedSubcategoryId = {{ $old('subcategory_id') ? (int) $old('subcategory_id') : ($inventory?->subcategory_id ?? 'null') }};

    function loadSubcategories(categoryId, selected = null) {
        const select = $('#subcategory_id');
        select.find('option:not(:first)').remove();
        const children = subcategories[categoryId] ?? [];
        children.forEach((c) => {
            select.append(`<option value="${c.id}" ${c.id === selected ? 'selected' : ''}>${c.name}</option>`);
        });
    }

    $('#category_id').on('change', function () {
        loadSubcategories($(this).val());
    });

    if ($('#category_id').val()) {
        loadSubcategories($('#category_id').val(), selectedSubcategoryId);
    }

    let insightIndex = $('.key-insight-row').length;

    function toggleInsightsHint() {
        $('#noInsightsHint').toggle($('.key-insight-row').length === 0);
    }

    $('#btnAddInsight').on('click', function () {
        const i = insightIndex++;
        $('#keyInsightsRows').append(`
            <div class="row g-2 mb-2 key-insight-row">
                <div class="col-md-5">
                    <input type="text" name="key_insights[${i}][label]" class="form-control" placeholder="Label (e.g. Reach)">
                </div>
                <div class="col-md-5">
                    <input type="text" name="key_insights[${i}][value]" class="form-control" placeholder="Value (e.g. 50,000)">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100 btn-remove-insight"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `);
        toggleInsightsHint();
    });

    $('#keyInsightsRows').on('click', '.btn-remove-insight', function () {
        $(this).closest('.key-insight-row').remove();
        toggleInsightsHint();
    });

    toggleInsightsHint();
});
</script>
@endpush
