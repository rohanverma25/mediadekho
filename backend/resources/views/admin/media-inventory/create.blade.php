@extends('admin.layouts.app')

@section('title', 'Add Media Inventory')

@section('content')
    <form method="POST" action="{{ route('admin.media-inventory.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.media-inventory._general-fields', ['inventory' => null])

        <div class="card mb-3">
            <div class="card-header">Gallery</div>
            <div class="card-body">
                <input type="file" name="gallery[]" class="form-control @error('gallery') is-invalid @enderror" multiple accept="image/jpeg,image/png,image/webp">
                <div class="form-text">jpg, png, or webp. First image becomes the cover image.</div>
                @error('gallery.*')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Documents</div>
            <div class="card-body">
                <input type="file" name="documents[]" class="form-control @error('documents') is-invalid @enderror" multiple accept=".pdf,.docx,.xlsx">
                <div class="form-text">pdf, docx, or xlsx.</div>
                @error('documents.*')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Create Inventory</button>
            <a href="{{ route('admin.media-inventory.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
