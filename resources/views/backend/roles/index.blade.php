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
                                <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('{{ __('admin_pages.roles.delete_role_confirm') }}')">{{ __('admin_pages.roles.delete') }}</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
