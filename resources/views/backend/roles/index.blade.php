@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Roles</h5>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">Add Role</a>
    </div>

    <div class="card-body">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th width="60%">Name</th>
                {{-- <th>Permissions</th> --}}
                <th width="40%">Actions</th>
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
                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ route('admin.roles.destroy', $role->id) }}"
                              method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete role?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
