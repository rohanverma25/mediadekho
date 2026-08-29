@extends('admin.layouts.app')

@section('title', 'Meta Tags')

@section('content')
    <div class="card">
        <div class="card-header">Meta Tags</div>
        <div class="card-body">
            <p class="small text-muted">SEO title, description, and social-share image for each static page. Category and listing pages have their own meta fields on their own edit forms.</p>
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pageMetas as $pageMeta)
                        <tr>
                            <td class="fw-semibold">{{ $pageMeta->label }}</td>
                            <td class="text-truncate d-inline-block" style="max-width:260px;">{{ $pageMeta->title ?: '—' }}</td>
                            <td class="text-truncate d-inline-block" style="max-width:320px;">{{ $pageMeta->description ?: '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.page-meta.edit', $pageMeta) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
