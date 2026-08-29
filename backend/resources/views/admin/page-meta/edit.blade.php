@extends('admin.layouts.app')

@section('title', 'Edit Meta Tags — ' . $pageMeta->label)

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0">{{ $pageMeta->label }} — Meta Tags</h5>
        <a href="{{ route('admin.page-meta.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <form action="{{ route('admin.page-meta.update', $pageMeta) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $pageMeta->title) }}" maxlength="255">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Leave blank to use the page's built-in default title.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" maxlength="500">{{ old('description', $pageMeta->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Leave blank to use the page's built-in default description. Aim for under 160 characters.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Social Share Image (OG Image)</label>
                    <input type="file" name="og_image" class="form-control @error('og_image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                    @error('og_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Shown when this page is shared on WhatsApp, Facebook, or Twitter. Leave blank to keep the current image.</div>
                    @if ($pageMeta->og_image_url)
                        <img src="{{ $pageMeta->og_image_url }}" alt="Current OG image" class="mt-2 rounded border" width="200" style="object-fit:cover;">
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.page-meta.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
