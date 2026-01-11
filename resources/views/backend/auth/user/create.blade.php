@extends('backend.layouts.app')

@section('title', __('labels.backend.access.users.management') . ' | ' . __('labels.backend.access.users.create'))

@section('breadcrumb-links')
@include('backend.auth.user.includes.breadcrumb-links')
@endsection

@section('content')
<form method="POST" action="{{ route('admin.auth.user.store') }}" class="form-horizontal">
    @csrf

    <h4 class="pb-3 d-flex">
        @lang('labels.backend.access.users.management')
        <small class="text-muted ml-3 mt-1">@lang('labels.backend.access.users.create')</small>
    </h4>

    <div class="card">
        <div class="card-body">

            <!-- First Name -->
            <div class="form-group row">
                <label for="first_name" class="col-md-2 form-control-label">
                    @lang('validation.attributes.backend.access.users.first_name')
                </label>
                <div class="col-md-10">
                    <input type="text" name="first_name" id="first_name" class="form-control"
                        placeholder="@lang('validation.attributes.backend.access.users.first_name')" maxlength="191"
                        required autofocus>
                </div>
            </div>

            <!-- Last Name -->
            <div class="form-group row">
                <label for="last_name" class="col-md-2 form-control-label">
                    @lang('validation.attributes.backend.access.users.last_name')
                </label>
                <div class="col-md-10">
                    <input type="text" name="last_name" id="last_name" class="form-control"
                        placeholder="@lang('validation.attributes.backend.access.users.last_name')" maxlength="191"
                        required>
                </div>
            </div>

            <!-- Email -->
            <div class="form-group row">
                <label for="email" class="col-md-2 form-control-label">
                    @lang('validation.attributes.backend.access.users.email')
                </label>
                <div class="col-md-10">
                    <input type="email" name="email" id="email" class="form-control"
                        placeholder="@lang('validation.attributes.backend.access.users.email')" maxlength="191" required>
                </div>
            </div>

            <!-- Employee Type -->
            <div class="form-group row">
                <label class="col-md-2 form-control-label">Type</label>
                <div class="col-md-10 mt-2 custom-select-wrapper">
                    <select name="employee_type" class="form-control custom-select-box">
                        <option value="">General</option>
                        <option value="internal">Internal</option>
                        <option value="external">External</option>
                    </select>
                    <span class="custom-select-icon" style="right: 23px;">
                        <i class="fa fa-chevron-down"></i>
                    </span>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group row">
                <label for="password" class="col-md-2 form-control-label">
                    @lang('validation.attributes.backend.access.users.password')
                </label>
                <div class="col-md-10 position-relative">
                    <input type="password" name="password" id="password-field" class="form-control"
                        placeholder="@lang('validation.attributes.backend.access.users.password')" required>
                    <span class="password-toggle" onclick="togglePassword()" style="position: absolute; top: 50%; right: 25px; transform: translateY(-50%); cursor: pointer;">
                        <i class="fa fa-eye" id="toggle-icon" style="color: #ccc;"></i>
                    </span>
                </div>
            </div>

            <!-- Password Confirmation -->
            <div class="form-group row">
                <label for="password_confirmation" class="col-md-2 form-control-label">
                    @lang('validation.attributes.backend.access.users.password_confirmation')
                </label>
                <div class="col-md-10">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="form-control"
                        placeholder="@lang('validation.attributes.backend.access.users.password_confirmation')" required>
                </div>
            </div>

            <!-- Active -->
            <div class="form-group row">
                <label for="active" class="col-md-2 form-control-label">
                    @lang('validation.attributes.backend.access.users.active')
                </label>
                <div class="col-md-10">
                    <label class="switch switch-label switch-pill switch-primary">
                        <input type="checkbox" name="active" value="1" class="switch-input" checked>
                        <span class="switch-slider" data-checked="yes" data-unchecked="no"></span>
                    </label>
                </div>
            </div>

            <!-- Confirmed -->
            <div class="form-group row">
                <label for="confirmed" class="col-md-2 form-control-label">
                    @lang('validation.attributes.backend.access.users.confirmed')
                </label>
                <div class="col-md-10">
                    <label class="switch switch-label switch-pill switch-primary">
                        <input type="checkbox" name="confirmed" value="1" class="switch-input" checked>
                        <span class="switch-slider" data-checked="yes" data-unchecked="no"></span>
                    </label>
                </div>
            </div>

            <!-- Roles -->
            <div class="form-group row">
                <label class="col-md-2 form-control-label">
                    @lang('labels.backend.access.users.table.abilities')
                </label>
                <div class="col-md-10">
                    @foreach($roles as $role)
                        <div class="form-check">
                            <input type="radio" name="roles[]" value="{{ $role->name }}"
                                id="role-{{ $role->id }}" class="form-check-input"
                                {{ old('roles') && in_array($role->name, old('roles')) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role-{{ $role->id }}">
                                {{ ucwords($role->name) }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="row mt-3">
                <div class="col-12 d-flex justify-content-between">
                    <a href="{{ route('admin.auth.user.index') }}" class="btn btn-secondary">
                        @lang('buttons.general.cancel')
                    </a>
                    <button type="submit" class="btn btn-primary">
                        @lang('buttons.general.crud.create')
                    </button>
                </div>
            </div>

        </div><!-- card-body -->
    </div><!-- card -->
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
</script>
@endpush

