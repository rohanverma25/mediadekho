@extends('admin.layouts.app')

@section('title', 'Activity Log')

@section('content')
    <div class="card">
        <div class="card-header">Activity Log</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Subject</th>
                        <th>Description</th>
                        <th>Causer</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td><span class="badge text-bg-secondary">{{ $activity->event }}</span></td>
                            <td>{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</td>
                            <td>{{ $activity->description }}</td>
                            <td>{{ $activity->causer?->name ?? 'System' }}</td>
                            <td>{{ $activity->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No activity recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $activities->links() }}
        </div>
    </div>
@endsection
