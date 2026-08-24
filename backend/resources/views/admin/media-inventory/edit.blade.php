@extends('admin.layouts.app')

@section('title', 'Edit Media Inventory')

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.media-inventory.show', $inventory) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-eye"></i> View Details &amp; Audit History
        </a>
    </div>

    <form method="POST" action="{{ route('admin.media-inventory.update', $inventory) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.media-inventory._general-fields', ['inventory' => $inventory])

        <div class="card mb-3">
            <div class="card-header">Gallery</div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    @forelse ($inventory->images as $image)
                        <div class="col-auto position-relative">
                            <img src="{{ $image->url }}" width="90" height="90" class="rounded border" style="object-fit:cover;">
                            @if ($image->is_cover)
                                <span class="badge text-bg-primary position-absolute top-0 start-0 m-1">Cover</span>
                            @endif
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 btn-remove-image" data-url="{{ route('admin.media-inventory.images.destroy', [$inventory, $image]) }}" style="padding:0.1rem 0.35rem;">&times;</button>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No images uploaded yet.</p>
                    @endforelse
                </div>
                <input type="file" name="gallery[]" class="form-control @error('gallery') is-invalid @enderror" multiple accept="image/jpeg,image/png,image/webp">
                <div class="form-text">jpg, png, or webp. Uploads here are added to the gallery above.</div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Documents</div>
            <div class="card-body">
                <ul class="list-group mb-3">
                    @forelse ($inventory->files as $file)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{ $file->url }}" target="_blank">{{ $file->original_name }}</a>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-file" data-url="{{ route('admin.media-inventory.files.destroy', [$inventory, $file]) }}">Remove</button>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No documents uploaded yet.</li>
                    @endforelse
                </ul>
                <input type="file" name="documents[]" class="form-control" multiple accept=".pdf,.docx,.xlsx">
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('admin.media-inventory.index') }}" class="btn btn-outline-secondary">Back to List</a>
        </div>
    </form>

    @include('admin.media-inventory._pricing-section')
@endsection

@push('scripts')
<script>
$(function () {
    function removeCard(button, selector) {
        $.ajax({
            url: $(button).data('url'),
            method: 'DELETE',
            success: function () {
                $(button).closest(selector).remove();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to remove.');
            },
        });
    }

    $('.btn-remove-image').on('click', function () {
        if (confirm('Remove this image?')) removeCard(this, '.col-auto');
    });

    $('.btn-remove-file').on('click', function () {
        if (confirm('Remove this document?')) removeCard(this, 'li');
    });
});
</script>
@endpush
