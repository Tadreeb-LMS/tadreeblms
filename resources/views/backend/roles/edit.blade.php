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
        <h5>{{ __('admin_pages.roles.edit_role') }}</h5>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary float-end">{{ __('admin_pages.roles.back') }}</a>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">{{ __('admin_pages.roles.role_name') }}</label>
                <input type="text" name="name" id="name" readonly class="form-control" value="{{ $role->name }}" required>
            </div>

            <div class="mb-3">
                <h6>{{ __('admin_pages.roles.permissions') }}</h6>

                <div class="permission-blocks row">
                @foreach($permissions as $module => $modulePermissions)
                    <div class="mb-2 border p-2 rounded permission-module">
                        <strong>{{ ucfirst(str_replace('_', ' ', $module)) }}</strong>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input select-all" data-module="{{ $module }}" id="select_all_{{ $module }}">
                            <label class="form-check-label" for="select_all_{{ $module }}">{{ __('admin_pages.roles.select_all') }}</label>
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

            <button type="submit" class="btn btn-success">{{ __('admin_pages.roles.update_role') }}</button>
        </form>
    </div>
</div>
@push('after-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Find a module's permissions through its group container. Building a
    // class selector from the module name breaks for names with spaces
    // (e.g. "view backend"), which match no checkbox at all.
    function modulePermissions(selectAllCheckbox) {
        return selectAllCheckbox.closest('.permission-module')
            .querySelectorAll('input[name="permissions[]"][type="checkbox"]');
    }

    // Reflect the children in the module's "Select All" checkbox: checked only
    // when every permission of that module is checked.
    function syncModuleCheckbox(selectAllCheckbox) {
        const permissions = [...modulePermissions(selectAllCheckbox)];
        selectAllCheckbox.checked = permissions.length > 0 && permissions.every(cb => cb.checked);
    }

    document.querySelectorAll('.select-all').forEach(function (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            // Disabled permissions (the always-on "backend" module) are
            // submitted through hidden inputs and must stay checked.
            modulePermissions(selectAllCheckbox).forEach(function (cb) {
                if (!cb.disabled) {
                    cb.checked = selectAllCheckbox.checked;
                }
            });
            syncModuleCheckbox(selectAllCheckbox);
        });

        modulePermissions(selectAllCheckbox).forEach(function (cb) {
            cb.addEventListener('change', function () {
                syncModuleCheckbox(selectAllCheckbox);
            });
        });

        // Initial state: a role that already has every permission of a
        // module should open with that module's "Select All" checked.
        syncModuleCheckbox(selectAllCheckbox);
    });

});
</script>
@endpush

@endsection

