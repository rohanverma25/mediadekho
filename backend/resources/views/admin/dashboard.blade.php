@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-3">
                <div class="text-uppercase text-muted small fw-semibold">Total Inventory</div>
                <div class="fs-3 fw-bold">{{ number_format($stats['total_inventory']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-3">
                <div class="text-uppercase text-muted small fw-semibold">Published</div>
                <div class="fs-3 fw-bold">{{ number_format($stats['published_inventory']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-3">
                <div class="text-uppercase text-muted small fw-semibold">Draft</div>
                <div class="fs-3 fw-bold">{{ number_format($stats['draft_inventory']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-3">
                <div class="text-uppercase text-muted small fw-semibold">Categories</div>
                <div class="fs-3 fw-bold">{{ number_format($stats['total_categories']) }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Recently Added Inventory</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Frequency</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentInventory as $item)
                        <tr>
                            <td><a href="{{ route('admin.media-inventory.edit', $item) }}">{{ $item->title }}</a></td>
                            <td>{{ $item->category?->name }}</td>
                            <td>{{ $item->frequency?->name }}</td>
                            <td><span class="badge text-bg-{{ $item->status === 'published' ? 'success' : ($item->status === 'draft' ? 'secondary' : 'dark') }}">{{ ucfirst($item->status) }}</span></td>
                            <td>{{ $item->creator?->name }}</td>
                            <td>{{ $item->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No inventory yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
