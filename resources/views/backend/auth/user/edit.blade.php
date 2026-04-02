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
                           class="form-control @error('first_name') is-invalid @enderror"
                           value="{{ old('first_name', $user->first_name) }}"
                           maxlength="191"
                           required>
                    @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                           class="form-control @error('last_name') is-invalid @enderror"
                           value="{{ old('last_name', $user->last_name) }}"
                           maxlength="191"
                           required>
                    @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email) }}"
                           readonly
                           maxlength="191"
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- {{-- Employee Type --}}
            <div class="form-group row">
                <label class="col-md-2">Type</label>
                <div class="col-md-10 mt-2">
                    <select name="employee_type" class="form-control">
                        <option value="" {{ $user->employee_type == '' ? 'selected' : '' }}>General</option>
                        <option value="internal" {{ $user->employee_type == 'internal' ? 'selected' : '' }}>Internal</option>
                        <option value="external" {{ $user->employee_type == 'external' ? 'selected' : '' }}>External</option>
                    </select>
                </div>
            </div> -->

                <!-- Change Password Toggle -->
                <div class="form-group row">
                    <div class="col-md-10 offset-md-2">
                        <div class="form-check">
                            <input class="form-check-input"
                                type="checkbox"
                                id="change_password"
                                value="1"
                                name="change_password"
                                {{ old('change_password') ? 'checked' : '' }}
                                onchange="togglePasswordFields()">
                            <label class="form-check-label" for="change_password">
                                Want to edit password?
                            </label>
                        </div>
                        @error('change_password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group row {{ old('change_password') ? '' : 'd-none' }}" id="password-section">
                    <label class="col-md-2 form-control-label">
                        @lang('validation.attributes.backend.access.users.password')
                    </label>
                    <div class="col-md-10 position-relative">
                        <input type="password"
                            name="password"
                            id="password-field"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Enter new password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Password Confirmation -->
                <div class="form-group row {{ old('change_password') ? '' : 'd-none' }}" id="password-confirm-section">
                    <label class="col-md-2 form-control-label">
                        @lang('validation.attributes.backend.access.users.password_confirmation')
                    </label>
                    <div class="col-md-10">
                        <input type="password"
                            name="password_confirmation"
                            class="form-control @error('password_confirmation') is-invalid @enderror"
                            placeholder="Confirm new password">
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
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
                                @php
                                    $selectedRoles = old('roles', $userRoles);
                                    $roleLabels = [
                                        'trainer' => 'Trainer',
                                        'trainee' => 'Trainee',
                                        'student' => 'Student',
                                        'teacher' => 'Teacher',
                                        'administrator' => 'Administrator',
                                        'admin' => 'Admin',
                                    ];
                                @endphp
                                @foreach($roles as $role)
                                    @if(1)
                                        <div class="card mb-2">
                                            <div class="card-header">
                                                <div class="form-check">
                                                    <input type="radio"
                                                           name="roles[]"
                                                           id="role-{{ $role->id }}"
                                                           value="{{ $role->name }}"
                                                           class="form-check-input @error('roles') is-invalid @enderror"
                                                           {{ in_array($role->name, $selectedRoles) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="role-{{ $role->id }}">
                                                        {{ $roleLabels[strtolower($role->name)] ?? ucwords(str_replace('_', ' ', $role->name)) }}
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
                                @error('roles')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @error('roles.*')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Department --}}
            <div class="form-group row">
                <label for="department" class="col-md-2 form-control-label">
                    @lang('Department')
                </label>
                <div class="col-md-10">
                    <select name="department" id="department" class="form-control @error('department') is-invalid @enderror">
                        <option value="">@lang('Select Department')</option>
                        @if(isset($departments))
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ (string) old('department', optional(optional($user->employee)->department_details)->id) === (string) $dept->id ? 'selected' : '' }}>
                                    {{ $dept->title }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('department')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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

@push('after-styles')
<style>
    @keyframes validationAlertBlink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.35; }
    }

    .validation-alert-blink {
        animation: validationAlertBlink 0.35s ease-in-out 3;
    }
</style>
@endpush

@push('after-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const validationAlert = document.querySelector('.alert.alert-danger');
        if (validationAlert) {
            validationAlert.classList.add('validation-alert-blink');
        }
    });

    function togglePasswordFields() {
        const checked = document.getElementById('change_password').checked;

        const passwordSection = document.getElementById('password-section');
        const passwordConfirmSection = document.getElementById('password-confirm-section');
        const passwordField = document.getElementById('password-field');
        const passwordConfirmationField = document.querySelector('[name="password_confirmation"]');

        passwordSection.classList.toggle('d-none', !checked);
        passwordConfirmSection.classList.toggle('d-none', !checked);

        if (!checked) {
            if (passwordField) {
                passwordField.value = '';
            }
            if (passwordConfirmationField) {
                passwordConfirmationField.value = '';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        togglePasswordFields();
    });


</script>
@endpush
