@extends('backend.layouts.app')

@section('content')
<style>
    .permission-blocks {
        display: flex;
        gap: 15px;
    }
</style>
<div class="card">
    <div class="card-header">
        <h5>Edit Role</h5>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary float-end">Back</a>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Role Name</label>
                <input type="text" name="name" id="name" readonly class="form-control" value="{{ $role->name }}" required>
            </div>

            <div class="mb-3">
                <h6>Permissions</h6>

                <div class="permission-blocks row">
                @foreach($permissions as $module => $modulePermissions)
                    <div class="mb-2 border p-2 rounded">
                        <strong>{{ ucfirst($module) }}</strong>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input select-all" data-module="{{ $module }}" id="select_all_{{ $module }}">
                            <label class="form-check-label" for="select_all_{{ $module }}">Select All</label>
                        </div>

                        @foreach($modulePermissions as $permission)
                            @php
                                $default_permission_checked = ($module === 'backend');
                                $isChecked = $role->permissions->contains('id', $permission->id) || $default_permission_checked;
                            @endphp

                            <div class="form-check ms-3">
                                <input type="checkbox"
                                    name="permissions[]"
                                    class="form-check-input permission-{{ $module }}"
                                    value="{{ $permission->id }}"
                                    id="perm_{{ $permission->id }}"
                                    {{ $isChecked ? 'checked' : '' }}
                                    {{ $default_permission_checked ? 'disabled' : '' }}>

                                {{-- Hidden input to submit disabled permissions --}}
                                @if($default_permission_checked)
                                    <input type="hidden" name="permissions[]" value="{{ $permission->id }}">
                                @endif

                                <label class="form-check-label" for="perm_{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        @endforeach

                    </div>
                @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-success">Update Role</button>
        </form>
    </div>
</div>
@push('after-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.select-all').forEach(function(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {

            //alert('hi'); // THIS WILL NOW SHOW ✅

            const module = this.dataset.module;
            const permissions = document.querySelectorAll(
                'input.permission-' + module
            );

            permissions.forEach(cb => cb.checked = this.checked);
        });
    });

});
</script>
@endpush

@endsection


