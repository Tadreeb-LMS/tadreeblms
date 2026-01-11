@extends('backend.layouts.app')

@section('title', 'Edit Teacher | ' . app_name())

@push('after-styles')
<link rel="stylesheet" href="{{ asset('assets/css/colors/switch.css') }}">
<style>
.switch.switch-3d.switch-lg {
    width: 40px;
    height: 20px;
}
.switch.switch-3d.switch-lg .switch-handle {
    width: 20px;
    height: 20px;
}
</style>
@endpush

@section('content')

<form method="POST"
      action="{{ route('admin.teachers.update', $teacher->id) }}"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

<div class="">

    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="text-20">Edit Teacher</h4>
        <a href="{{ route('admin.teachers.index') }}" class="add-btn">
            View Teachers
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">

                <!-- ID Number -->
                <div class="col-lg-6 mt-3">
                    <label>Id Number</label>
                    <input type="text"
                           name="id_number"
                           class="form-control"
                           value="{{ $teacher->id_number }}"
                           required>
                </div>

                <!-- First Name -->
                <div class="col-lg-6 mt-3">
                    <label>First Name</label>
                    <input type="text"
                           name="first_name"
                           class="form-control"
                           value="{{ $teacher->first_name }}"
                           required>
                </div>

                <!-- Last Name -->
                <div class="col-lg-6 mt-3">
                    <label>Last Name</label>
                    <input type="text"
                           name="last_name"
                           class="form-control"
                           value="{{ $teacher->last_name }}"
                           required>
                </div>

                <!-- Email -->
                <div class="col-lg-6 mt-3">
                    <label>Email</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="{{ $teacher->email }}"
                           readonly>
                </div>

                <!-- Password -->
                <div class="col-lg-6 mt-3">
                    <label>Password</label>
                    <div class="position-relative">
                        <input type="password"
                               name="password"
                               id="password-field"
                               class="form-control">
                        <span onclick="togglePassword()"
                              style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer">
                            <i class="fa fa-eye" id="toggle-icon"></i>
                        </span>
                    </div>
                </div>

                <!-- Image -->
                <div class="col-lg-6 mt-3">
                    <label>Profile Image</label>
                    <input type="file"
                           name="image"
                           class="form-control">
                </div>

                <!-- Gender -->
                <div class="col-lg-6 mt-3">
                    <label>Gender</label><br>
                    <label>
                        <input type="radio"
                               name="gender"
                               value="male"
                               {{ $teacher->gender === 'male' ? 'checked' : '' }}>
                        Male
                    </label>
                    <label class="ml-3">
                        <input type="radio"
                               name="gender"
                               value="female"
                               {{ $teacher->gender === 'female' ? 'checked' : '' }}>
                        Female
                    </label>
                </div>

                <!-- Classification Number -->
                <div class="col-lg-6 mt-3">
                    <label>Classification Number</label>
                    <input type="text"
                           name="classfi_number"
                           class="form-control"
                           value="{{ $teacher->classfi_number }}">
                </div>

                <!-- Nationality -->
                <div class="col-lg-6 mt-3">
                    <label>Nationality</label>
                    <select name="nationality" class="form-control">
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}"
                                {{ $teacher->nationality == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="col-lg-6 mt-3">
                    <label>Status</label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                               name="active"
                               value="1"
                               class="custom-control-input"
                               id="status-{{ $teacher->id }}"
                               {{ $teacher->active ? 'checked' : '' }}>
                        <label class="custom-control-label"
                               for="status-{{ $teacher->id }}"></label>
                    </div>
                </div>

                <!-- Description -->
                {{-- <div class="col-lg-12 mt-3">
                    <label>Description</label>
                    <textarea name="description"
                              class="form-control"
                              rows="4">{{ optional($teacher->teacherProfile)->description }}</textarea>
                </div> --}}

                <!-- Buttons -->
                <div class="col-lg-12 mt-4 d-flex justify-content-between">
                    <a href="{{ route('admin.teachers.index') }}"
                       class="btn btn-secondary">
                        Cancel
                    </a>

                    <button type="submit"
                            class="btn btn-primary">
                        Update
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

</form>

@endsection

@push('after-scripts')
<script>
function togglePassword() {
    const field = document.getElementById('password-field');
    const icon = document.getElementById('toggle-icon');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
@endpush
