@extends('admin.layouts.app')

@section('title', $inventory->title)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0">{{ $inventory->title }}</h5>
            <span class="text-muted small">{{ $inventory->slug }}</span>
        </div>
        <div class="d-flex gap-2">
            <span class="badge text-bg-{{ $inventory->status === 'published' ? 'success' : ($inventory->status === 'draft' ? 'secondary' : 'dark') }} align-self-center">{{ ucfirst($inventory->status) }}</span>
            <a href="{{ route('admin.media-inventory.edit', $inventory) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i> Edit</a>
            <a href="{{ route('admin.media-inventory.index') }}" class="btn btn-sm btn-outline-secondary">Back to List</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Media Information</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Category</dt>
                <dd class="col-sm-9">{{ $inventory->category?->name ?? '—' }}</dd>
                <dt class="col-sm-3">Sub Category</dt>
                <dd class="col-sm-9">{{ $inventory->subcategory?->name ?? '—' }}</dd>
                <dt class="col-sm-3">Frequency</dt>
                <dd class="col-sm-9">{{ $inventory->frequency?->name ?? '—' }}</dd>
                <dt class="col-sm-3">Language</dt>
                <dd class="col-sm-9">{{ $inventory->language?->name ?? '—' }}</dd>
                <dt class="col-sm-3">Short Description</dt>
                <dd class="col-sm-9">{{ $inventory->short_description ?? '—' }}</dd>
                <dt class="col-sm-3">Created By</dt>
                <dd class="col-sm-9">{{ $inventory->creator?->name ?? '—' }} on {{ $inventory->created_at->format('M j, Y') }}</dd>
                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{!! $inventory->description ?: '—' !!}</dd>
            </dl>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Image</div>
        <div class="card-body">
            @if ($inventory->image_url)
                <img src="{{ $inventory->image_url }}" width="140" height="140" class="rounded border" style="object-fit:cover;">
            @else
                <p class="text-muted mb-0">No image uploaded.</p>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Key Insights</div>
        <div class="card-body">
            @forelse ($inventory->keyInsights as $insight)
                <div class="d-flex justify-content-between border-bottom py-1">
                    <span class="fw-semibold">{{ $insight->label }}</span>
                    <span>{{ $insight->value }}</span>
                </div>
            @empty
                <p class="text-muted mb-0">No key insights added.</p>
            @endforelse
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Gallery</div>
        <div class="card-body d-flex flex-wrap gap-2">
            @forelse ($inventory->images as $image)
                <div class="position-relative">
                    <img src="{{ $image->url }}" width="100" height="100" class="rounded border" style="object-fit:cover;">
                    @if ($image->is_cover)
                        <span class="badge text-bg-primary position-absolute top-0 start-0 m-1">Cover</span>
                    @endif
                </div>
            @empty
                <p class="text-muted mb-0">No images.</p>
            @endforelse
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Documents</div>
        <div class="card-body">
            <ul class="list-group">
                @forelse ($inventory->files as $file)
                    <li class="list-group-item"><a href="{{ $file->url }}" target="_blank">{{ $file->original_name }}</a></li>
                @empty
                    <li class="list-group-item text-muted">No documents.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Pricing</div>
        <div class="card-body">
            @if ($priceBreakdown)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Tier</th>
                                <th>Markup %</th>
                                <th>Price</th>
                                <th>Selling Price</th>
                                <th>Final Price</th>
                                <th>Net Profit</th>
                                <th>Margin %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($priceBreakdown['tiers'] as $tier => $data)
                                <tr>
                                    <td class="text-capitalize fw-semibold">{{ $tier }}</td>
                                    @if ($data)
                                        <td>{{ $data['markup_percentage'] !== null ? number_format($data['markup_percentage'], 2).'%' : '—' }}</td>
                                        <td>₹{{ number_format($data['price'], 2) }}</td>
                                        <td>₹{{ number_format($data['selling_price'], 2) }}</td>
                                        <td class="fw-semibold">₹{{ number_format($data['final_price'], 2) }}</td>
                                        <td class="{{ $data['net_profit'] < 0 ? 'text-danger fw-semibold' : '' }}">₹{{ number_format($data['net_profit'], 2) }}</td>
                                        <td>{{ number_format($data['margin_percentage'], 2) }}%</td>
                                    @else
                                        <td colspan="6" class="text-muted">Not set</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">No pricing configured yet.</p>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Availability</div>
        <div class="card-body">
            @if ($inventory->availabilitySlots->isEmpty())
                <p class="text-muted mb-0">No calendar entries.</p>
            @else
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($inventory->availabilitySlots->sortBy('date') as $slot)
                        <span class="badge text-bg-{{ $slot->status === 'available' ? 'success' : ($slot->status === 'booked' ? 'warning' : 'dark') }}">
                            {{ $slot->date->toDateString() }} — {{ ucfirst($slot->status) }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Audit History</div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Description</th>
                        <th>Causer</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td><span class="badge text-bg-secondary">{{ $activity->event }}</span></td>
                            <td>{{ $activity->description }}</td>
                            <td>{{ $activity->causer?->name ?? 'System' }}</td>
                            <td>{{ $activity->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No activity recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
