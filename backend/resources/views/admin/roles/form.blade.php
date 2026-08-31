@extends('admin.layouts.app')

@section('title', $role ? 'Edit Role' : 'Add Role')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0">{{ $role ? 'Edit Role' : 'Add Role' }}</h5>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <form action="{{ $role ? route('admin.roles.update', $role) : route('admin.roles.store') }}" method="POST">
        @csrf
        @if ($role)
            @method('PUT')
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <label class="form-label">Role Name</label>
                <input
                    type="text"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $role?->name) }}"
                    @if ($role && ($protected ?? false)) readonly @endif
                    required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if ($role && ($protected ?? false))
                    <div class="form-text">This is a built-in role — its name can't be changed, but you can still adjust its permissions below.</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">Permissions</div>
            <div class="card-body">
                @foreach ($groupedPermissions as $groupLabel => $permissions)
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-1 mb-2">
                            <h6 class="mb-0">{{ $groupLabel }}</h6>
                            <button type="button" class="btn btn-link btn-sm p-0 btn-toggle-group" data-group="group-{{ $loop->index }}">Select all</button>
                        </div>
                        <div class="row" id="group-{{ $loop->index }}">
                            @foreach ($permissions as $permission)
                                <div class="col-md-4 col-sm-6 mb-2">
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            id="perm-{{ $permission->id }}"
                                            class="form-check-input"
                                            value="{{ $permission->name }}"
                                            @checked($checkedPermissions->contains($permission->name))>
                                        <label class="form-check-label small" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
$(function () {
    $('.btn-toggle-group').on('click', function () {
        const group = $('#' + $(this).data('group'));
        const boxes = group.find('input[type="checkbox"]');
        const shouldCheck = boxes.filter(':checked').length < boxes.length;
        boxes.prop('checked', shouldCheck);
    });
});
</script>
@endpush
