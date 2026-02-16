@extends('backend.layouts.app')

@section('title', 'Employee'.' | '.app_name())
@push('after-styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
    <link rel="stylesheet" href="{{asset('assets/css/colors/switch.css')}}">
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

@include('backend.includes.license-warning')

<form action="{{ route('admin.employee.external.store') }}" method="POST" enctype="multipart/form-data" class="form-horizontal">
    @csrf

    <div>
        <div class="d-flex justify-content-between align-items-center pb-3">
            <div>
                <h4 class="text-20">Create Trainee</h4>
            </div>

            <div>
                <a href="{{ route('admin.employee.external.create') }}">
                    <button type="button" class="add-btn">View Trainee</button>
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">

                    {{-- First Name --}}
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" maxlength="191" placeholder="First Name" required autofocus>
                    </div>

                    {{-- Last Name --}}
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" maxlength="191" placeholder="Last Name" required>
                    </div>

                    {{-- Email --}}
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">Email</label>
                        <input type="email" name="email" class="form-control" maxlength="191" placeholder="Email" required>
                    </div>

                    {{-- Password --}}
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">Password</label>
                        <div class="position-relative">
                            <input type="password" name="password" id="password-field" class="form-control" placeholder="Password" required>
                            <span class="password-toggle" onclick="togglePassword()" style="position:absolute;top:50%;right:10px;transform:translateY(-50%);cursor:pointer;">
                                <i class="fa fa-eye" id="toggle-icon"></i>
                            </span>
                        </div>
                    </div>

                    {{-- Image Upload --}}
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">Image</label>
                        <div class="custom-file-upload-wrapper">
                            <input type="file" name="image" id="customFileInput" class="custom-file-input">
                            <label for="customFileInput" class="custom-file-label">
                                <i class="fa fa-upload mr-1"></i> Choose a file
                            </label>
                        </div>
                    </div>

                    {{-- ID Number --}}
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">ID Number</label>
                        <input type="text" name="id_number" class="form-control" placeholder="ID Number" required>
                    </div>

                    {{-- Classification Number --}}
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">Classification Number</label>
                        <input type="text" name="class_number" class="form-control" placeholder="Classification Number">
                    </div>

                    {{-- Nationality --}}
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">Nationality</label>
                        <div class="custom-select-wrapper mt-2">
                            <select name="nationality" class="form-control custom-select-box" required>
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                            <span class="custom-select-icon">
                                <i class="fa fa-chevron-down"></i>
                            </span>
                        </div>
                    </div>

                    {{-- Date of Birth --}}
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-control" required>
                    </div>

                    {{-- Mobile Number --}}
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">Mobile Number</label>
                        <input type="text" name="mobile_number" class="form-control" placeholder="Mobile Number" required>
                    </div>

                    {{-- Gender --}}
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">Gender</label>
                        <div>
                            <label class="mr-3"><input type="radio" name="gender" value="male"> Male</label>
                            <label class="mr-3"><input type="radio" name="gender" value="female"> Female</label>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">Status</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="switch" name="active" value="1" checked>
                            <label class="custom-control-label" for="switch"></label>
                        </div>
                    </div>

                    {{-- Submit + Cancel --}}
                    <div class="col-12 d-flex justify-content-between mt-3">
                        <a href="{{ route('admin.employee.index') }}">
                            <button type="button" class="cancel-btn">Cancel</button>
                        </a>

                        <button type="submit" class="add-btn">Submit</button>
                    </div>

                </div>
            </div>
        </div>
    </div>

</form>
@endsection

@push('after-scripts')
<script>
    @if(old('payment_method') && old('payment_method') == 'bank')
    $('.paypal_details').hide();
    $('.bank_details').show();
    @elseif(old('payment_method') && old('payment_method') == 'paypal')
    $('.paypal_details').show();
    $('.bank_details').hide();
    @else
    $('.paypal_details').hide();
    @endif
    $(document).on('change', '#payment_method', function(){
        if($(this).val() === 'bank'){
            $('.paypal_details').hide();
            $('.bank_details').show();
        }else{
            $('.paypal_details').show();
            $('.bank_details').hide();
        }
    });
</script>
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
<script>
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const label = input.nextElementSibling;
            const fileName = e.target.files.length > 0 ? e.target.files[0].name : 'Choose a file';
            label.innerHTML = '<i class="fa fa-upload mr-1"></i> ' + fileName;
        });
    });
</script>
@endpush
