@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>{{ isset($role) ? 'Edit Role' : 'Add Role' }}</h5>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary float-end">Back</a>
    </div>

    <div class="card-body">
        <form action="{{ isset($role) ? route('admin.roles.update', $role->id) : route('admin.roles.store') }}" method="POST">
            @csrf
            @if(isset($role))
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="name" class="form-label">Role Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $role->name ?? old('name') }}" required>
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
                                $default_permission_checked = false;
                            @endphp

                            @if($module == 'backend')
                                @php
                                    $default_permission_checked = true;
                                @endphp
                            @endif
                            <div class="form-check ms-3">
                                <input type="checkbox"
                                    name="permissions[]"
                                    class="form-check-input permission-{{ $module }}"
                                    value="{{ $permission->id }}"
                                    id="perm_{{ $permission->id }}"
                                    @if($default_permission_checked) checked @endif
                                    onclick="return false;"
                                   >
                                <label class="form-check-label" for="perm_{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-success">{{ isset($role) ? 'Update Role' : 'Create Role' }}</button>
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
