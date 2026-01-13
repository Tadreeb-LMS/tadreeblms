@extends('backend.layouts.app')

@section('title', 'Employee' . ' | ' . app_name())

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
#password-field::-ms-reveal {
    display: none;
}
    </style>
@endpush
@section('content')

<form id="addUserTrainee" method="POST" action="{{ route('admin.employee.store') }}" enctype="multipart/form-data">
    @csrf

    <input type="text" name="fakeusernameremembered" style="display:none">
    <input type="password" name="fakepasswordremembered" style="display:none">

    <div>
        <div class="d-flex justify-content-between align-items-center pb-3">
            <div>
                <h4 class="text-20">Create</h4>
            </div>

            <div>
                <a href="{{ route('admin.employee.index') }}">
                    <button type="button" class="add-btn">View List</button>
                </a>
            </div>
        </div>

        <div class="card" style="border: none;">
            <div class="card-body pt-0">
                <div class="row">

                    <!-- First Name -->
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label" for="first_name">
                            {{ __('labels.backend.teachers.fields.first_name') }}
                        </label>
                        <input type="text" name="first_name" id="first_name"
                               class="form-control" maxlength="191"
                               placeholder="{{ __('labels.backend.teachers.fields.first_name') }}" required>
                    </div>

                    <!-- Last Name -->
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label" for="last_name">
                            {{ __('labels.backend.teachers.fields.last_name') }}
                        </label>
                        <input type="text" name="last_name" id="last_name"
                               class="form-control" maxlength="191"
                               placeholder="{{ __('labels.backend.teachers.fields.last_name') }}" required>
                    </div>

                    <!-- Arabic First Name -->
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label" for="arabic_first_name">
                            First Name in Arabic
                        </label>
                        <input type="text" name="arabic_first_name" id="arabic_first_name"
                               class="form-control" maxlength="191"
                               placeholder="First Name In Arabic" required>
                    </div>

                    <!-- Arabic Last Name -->
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label" for="arabic_last_name">
                            Last Name in Arabic
                        </label>
                        <input type="text" name="arabic_last_name" id="arabic_last_name"
                               class="form-control" maxlength="191"
                               placeholder="Last Name In Arabic" required>
                    </div>

                    <!-- Employee ID -->
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label" for="emp_id">Employee ID</label>
                        <input type="text" name="emp_id" id="emp_id" class="form-control" placeholder="Employee Id" required>
                    </div>

                    <!-- Email -->
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label" for="email">
                            {{ __('labels.backend.teachers.fields.email') }}
                        </label>
                        <input type="email" name="email" id="email" class="form-control" maxlength="191"
                               placeholder="{{ __('labels.backend.teachers.fields.email') }}"
                               autocomplete="new-email" required>
                    </div>

                    <!-- Password -->
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label" for="password">Password</label>

                        <div class="position-relative">
                            <input type="password" name="password" id="password-field" class="form-control"
                                   autocomplete="new-password"
                                   placeholder="{{ __('labels.backend.teachers.fields.password') }}" required>

                            <span class="password-toggle" onclick="togglePassword()"
                                  style="position: absolute; top: 50%; right: 10px; 
                                  transform: translateY(-50%); cursor: pointer;">
                                <i class="fa fa-eye" id="toggle-icon" style="color:#ccc;"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Department -->
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">Select Department</label>
                        <select name="department" class="form-control custom-select-box" required>
                            <option value="">Select One</option>
                            @foreach ($departments as $row)
                                <option value="{{ $row->id }}">{{ $row->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Position -->
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label">Select Position</label>
                        <select name="position" class="form-control custom-select-box" required>
                            <option value="">Select One</option>
                            @foreach ($positions as $row)
                                <option value="{{ $row->title }}">{{ $row->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Image -->
                    <div class="col-lg-6 col-sm-12 mt-3">
                        <label class="form-control-label" for="employeeImage">
                            {{ __('labels.backend.teachers.fields.image') }}
                        </label>

                        <div class="custom-file-upload-wrapper">
                            <input type="file" name="image" id="employeeImage" class="custom-file-input" required>
                            <label for="employeeImage" class="custom-file-label">
                                <i class="fa fa-upload mr-1"></i> Choose a file
                            </label>
                        </div>
                    </div>

                    <!-- Gender -->
                    <div class="col-lg-4 col-sm-12 mt-3">
                        <label class="form-control-label">Gender</label>
                        <div>
                            <label class="radio-inline mr-3 mb-0">
                                <input type="radio" name="gender" value="male" checked> Male
                            </label>
                            <label class="radio-inline mr-3 mb-0">
                                <input type="radio" name="gender" value="female"> Female
                            </label>
                        </div>
                    </div>

                    <!-- Preferred Language -->
                    <div class="col-lg-4 col-sm-12 mt-3">
                        <label class="form-control-label">Preferred Language</label>
                        <div>
                            <label class="radio-inline mr-3 mb-0">
                                <input type="radio" name="fav_lang" value="english" checked> English
                            </label>
                            <label class="radio-inline mr-3 mb-0">
                                <input type="radio" name="fav_lang" value="arabic"> Arabic
                            </label>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-lg-4 col-sm-12 mt-3">
                        <label class="form-control-label">Status</label>
                        <label class="switch switch-lg switch-3d switch-primary">
                            <input type="checkbox" name="active" value="1" class="switch-input" checked>
                            <span class="switch-label"></span>
                            <span class="switch-handle"></span>
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="col-12 mt-3 d-flex justify-content-between">
                        <a href="{{ route('admin.employee.index') }}">
                            <button class="cancel-btn" type="button">Cancel</button>
                        </a>

                        <button type="submit" class="add-btn">Submit</button>
                    </div>

                </div>
            </div>

            <input type="hidden" id="feedback_index" value="{{ route('admin.employee.index') }}">
            <input type="hidden" id="user-assisment" value="{{ url('user/assignments/create?assis_new') }}">
            <input type="hidden" id="add-user" value="{{ url('user/assessment_accounts/new_assisment/create') }}">

        </div>
    </div>
</form>

@endsection

@push('after-scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="/js/helpers/form-submit.js"></script>
    <script>
        @if (old('payment_method') && old('payment_method') == 'bank')
            $('.paypal_details').hide();
            $('.bank_details').show();
        @elseif (old('payment_method') && old('payment_method') == 'paypal')
            $('.paypal_details').show();
            $('.bank_details').hide();
        @else
            $('.paypal_details').hide();
        @endif
        $(document).on('change', '#payment_method', function() {
            if ($(this).val() === 'bank') {
                $('.paypal_details').hide();
                $('.bank_details').show();
            } else {
                $('.paypal_details').show();
                $('.bank_details').hide();
            }
        });
    </script>
    <script>
        var nxt_url_val = '';

        $('.frm_submit').on('click', function() {
            nxt_url_val = $(this).val();
        });
        $(document).on('submit', '#addUserTrainee', function(e) {
            e.preventDefault();
            hrefurl = $(location).attr("href");
            last_part = hrefurl.substr(hrefurl.lastIndexOf('/') + 8)
            // alert(last_part)
            let obj = $(this);

            setTimeout(() => {
                let data = $('#addUserTrainee').serialize();
                let url = '{{ route('admin.employee.store') }}';
                var redirect_url = $("#feedback_index").val();
                var redirect_url_course = $("#user-assisment").val();
                var redirect_add_course = $("#add-user").val();
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: data,
                    datatype: "json",
                    beforeSend: function() {
                        $("#loader").removeClass("d-none");
                    },
                    complete: function() {
                        $("#loader").addClass("d-none");
                    },
                    success: function(res) {
                        console.log(res)
                        if (last_part == 'new_user') {
                            window.location.href = redirect_url_course;
                            return;
                        }
                        if (last_part == 'add_user') {
                            window.location.href = redirect_add_course;
                            return;
                        } else {
                            alert('Activation mail send successfully')
                            window.location.href = redirect_url;
                            return;
                        }
                    },
                    error: function(res) {
                        console.log('resresres', res);
                        showErrorMessage(obj, res);
                        $('[type="submit"]').prop('disabled', false);
                    }
                })
            }, 100);
        })
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

    $(document).on('input change', '#addUserTrainee input, #addUserTrainee textarea, #addUserTrainee select', function() {
        
        $(this).closest('.email-info').find('.text-danger').text('');
        $(this).removeClass('is-invalid');
        
    });
</script>
@endpush
