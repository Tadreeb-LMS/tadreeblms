@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>{{ __('admin_pages.roles.title') }}</h5>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">{{ __('admin_pages.roles.add_role') }}</a>
    </div>

    <div class="card-body">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th width="60%">{{ __('admin_pages.roles.name') }}</th>
                {{-- <th>Permissions</th> --}}
                <th width="40%">{{ __('strings.backend.general.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($roles as $role)
                <tr>
                    <td>{{ $role->name }}</td>
                    {{-- <td>
                        @foreach($role->permissions as $permission)
                            <span class="badge bg-info">{{ $permission->name }}</span>
                        @endforeach
                    </td> --}}
                    <td>
                        @if($role->system_role != 1)
                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-primary">{{ __('admin_pages.roles.edit') }}</a>
                            <form action="{{ route('admin.roles.destroy', $role->id) }}"
                                method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button"
                                        class="btn btn-sm btn-danger delete-role-btn"
                                        data-toggle="modal"
                                        data-target="#deleteRoleModal"
                                        data-role-name="{{ $role->name }}"
                                        data-delete-url="{{ route('admin.roles.destroy', $role->id) }}">
                                    {{ __('admin_pages.roles.delete') }}
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- Delete Role Modal -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Delete Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="deleteRoleForm" method="POST">
                @csrf
                @method('DELETE')

                <div class="modal-body">
                    <p id="deleteRoleMessage"></p>

                    <div class="alert alert-warning">
                        <strong>Warning:</strong>
                        This action may affect users currently assigned to this role and cannot be undone.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-danger">
                        Delete Role
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
@push('after-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const deleteButtons = document.querySelectorAll('.delete-role-btn');

    deleteButtons.forEach(button => {

        button.addEventListener('click', function () {

            const roleName = this.dataset.roleName;
            const deleteUrl = this.dataset.deleteUrl;

            document.getElementById('deleteRoleMessage').innerHTML =
                `Are you sure you want to delete the <strong>${roleName}</strong> role?<br>`;

            document.getElementById('deleteRoleForm').action = deleteUrl;
        });

    });

});
</script>
@endpush
