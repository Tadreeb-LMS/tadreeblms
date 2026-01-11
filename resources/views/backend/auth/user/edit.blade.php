@extends('backend.layouts.app')

@section('title', __('labels.backend.access.users.management') . ' | ' . __('labels.backend.access.users.edit'))

@section('breadcrumb-links')
    @include('backend.auth.user.includes.breadcrumb-links')
@endsection

@section('content')

<form method="POST"
      action="{{ route('admin.auth.user.update', $user->id) }}"
      class="form-horizontal">
    @csrf
    @method('PATCH')

    <div class="pb-3 d-flex justify-content-between">
        <h4>
            @lang('labels.backend.access.users.management')
            <small class="text-muted ml-3">
                @lang('labels.backend.access.users.edit')
            </small>
        </h4>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- First Name --}}
            <div class="form-group row">
                <label class="col-md-2 form-control-label" for="first_name">
                    {{ __('validation.attributes.backend.access.users.first_name') }}
                </label>
                <div class="col-md-10">
                    <input type="text"
                           name="first_name"
                           id="first_name"
                           class="form-control"
                           value="{{ old('first_name', $user->first_name) }}"
                           maxlength="191"
                           required>
                </div>
            </div>

            {{-- Last Name --}}
            <div class="form-group row">
                <label class="col-md-2 form-control-label" for="last_name">
                    {{ __('validation.attributes.backend.access.users.last_name') }}
                </label>
                <div class="col-md-10">
                    <input type="text"
                           name="last_name"
                           id="last_name"
                           class="form-control"
                           value="{{ old('last_name', $user->last_name) }}"
                           maxlength="191"
                           required>
                </div>
            </div>

            {{-- Email --}}
            <div class="form-group row">
                <label class="col-md-2 form-control-label" for="email">
                    {{ __('validation.attributes.backend.access.users.email') }}
                </label>
                <div class="col-md-10">
                    <input type="email"
                           name="email"
                           id="email"
                           class="form-control"
                           value="{{ $user->email }}"
                           readonly
                           maxlength="191"
                           required>
                </div>
            </div>

            {{-- Employee Type --}}
            <div class="form-group row">
                <label class="col-md-2">Type</label>
                <div class="col-md-10 mt-2">
                    <select name="employee_type" class="form-control">
                        <option value="" {{ $user->employee_type == '' ? 'selected' : '' }}>General</option>
                        <option value="internal" {{ $user->employee_type == 'internal' ? 'selected' : '' }}>Internal</option>
                        <option value="external" {{ $user->employee_type == 'external' ? 'selected' : '' }}>External</option>
                    </select>
                </div>
            </div>

                <!-- Change Password Toggle -->
                <div class="form-group row">
                    <div class="col-md-10 offset-md-2">
                        <div class="form-check">
                            <input class="form-check-input"
                                type="checkbox"
                                id="change_password"
                                name="change_password"
                                onchange="togglePasswordFields()">
                            <label class="form-check-label" for="change_password">
                                Want to edit password?
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group row d-none" id="password-section">
                    <label class="col-md-2 form-control-label">
                        @lang('validation.attributes.backend.access.users.password')
                    </label>
                    <div class="col-md-10 position-relative">
                        <input type="text"
                            name="password"
                            id="password-field"
                            class="form-control"
                            placeholder="Enter new password">
                    </div>
                </div>

                <!-- Password Confirmation -->
                {{-- <div class="form-group row d-none" id="password-confirm-section">
                    <label class="col-md-2 form-control-label">
                        @lang('validation.attributes.backend.access.users.password_confirmation')
                    </label>
                    <div class="col-md-10">
                        <input type="password"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Confirm new password">
                    </div>
                </div> --}}

    <div class="pb-3 d-flex justify-content-between">
        <h4>
            @lang('labels.backend.access.users.management')
            <small class="text-muted ml-3">
                @lang('labels.backend.access.users.edit')
            </small>
        </h4>
    </div>

            {{-- Roles --}}
            <div class="form-group row">
                <label class="col-md-2 form-control-label">Abilities</label>

                <div class="col-md-10 table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>@lang('labels.backend.access.users.table.roles')</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                @foreach($roles as $role)
                                    @if(1)
                                        <div class="card mb-2">
                                            <div class="card-header">
                                                <div class="form-check">
                                                    <input type="radio"
                                                           name="roles[]"
                                                           id="role-{{ $role->id }}"
                                                           value="{{ $role->name }}"
                                                           class="form-check-input"
                                                           {{ in_array($role->name, $userRoles) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="role-{{ $role->id }}">
                                                        {{ ucwords($role->name) }}
                                                    </label>
                                                </div>
                                            </div>

                                            {{-- <div class="card-body">
                                                @if($role->permissions->count())
                                                    @foreach($role->permissions as $permission)
                                                        <i class="fas fa-dot-circle"></i>
                                                        {{ ucwords($permission->name) }} <br>
                                                    @endforeach
                                                @else
                                                    @lang('labels.general.none')
                                                @endif
                                            </div> --}}
                                        </div>
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="row">
                <div class="col-12 d-flex justify-content-between">
                    <a href="{{ route('admin.auth.user.index') }}" class="btn btn-secondary">
                        {{ __('buttons.general.cancel') }}
                    </a>

                    <button type="submit" class="btn btn-primary">
                        {{ __('buttons.general.crud.update') }}
                    </button>
                </div>
            </div>

        </div>
    </div>

</form>
@endsection
@push('after-scripts')
<script>
    function togglePassword() {
        var passwordField = document.getElementById("password-field");
        var icon = document.getElementById("toggle-icon");
        if (passwordField.type === "password") {
            passwordField.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }

    
    function togglePasswordFields() {
        const checked = document.getElementById('change_password').checked;

        document.getElementById('password-section').classList.toggle('d-none', !checked);
        document.getElementById('password-confirm-section').classList.toggle('d-none', !checked);

        if (!checked) {
            document.getElementById('password-field').value = '';
            document.querySelector('[name="password_confirmation"]').value = '';
        }
    }


</script>
@endpush
