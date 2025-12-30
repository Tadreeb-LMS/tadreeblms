@extends('backend.layouts.app')

@section('title', __('labels.backend.teachers.title').' | '.app_name())
@push('after-styles')
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

<form id="addTeacher" method="POST" action="{{ route('admin.teachers.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="d-flex justify-content-between pb-3 align-items-center">
        <h4>@lang('Create Trainer')</h4>

        <div>
            <a href="{{ route('admin.teachers.index') }}">
                <button type="button" class="add-btn">
                    @lang('labels.backend.teachers.view')
                </button>
            </a>
        </div>
    </div>

    <div class="card" style="border: none;">
        <div class="card-body">
            <div class="row">

                <!-- ID Number -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>Id Number <span style="color: red;">*</span></label>
                    <input class="form-control border-focus"
                           type="text"
                           name="id_number"
                           placeholder="Id Number"
                           required>
                </div>

                <!-- First Name -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>@lang('labels.backend.teachers.fields.first_name') <span style="color: red;">*</span></label>
                    <input class="form-control border-focus"
                           type="text"
                           name="first_name"
                           placeholder="@lang('labels.backend.teachers.fields.first_name')"
                           maxlength="191"
                           required>
                </div>

                <!-- Last Name -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>@lang('labels.backend.teachers.fields.last_name') <span style="color: red;">*</span></label>
                    <input class="form-control border-focus"
                           type="text"
                           name="last_name"
                           placeholder="@lang('labels.backend.teachers.fields.last_name')"
                           maxlength="191"
                           required>
                </div>

                <!-- Email -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>@lang('labels.backend.teachers.fields.email') <span style="color: red;">*</span></label>
                    <input class="form-control"
                           type="email"
                           name="email"
                           placeholder="@lang('labels.backend.teachers.fields.email')"
                           maxlength="191"
                           autocomplete="new-email"
                           required>
                </div>

                <!-- Password -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>@lang('labels.backend.teachers.fields.password') <span style="color: red;">*</span></label>
                    <div class="position-relative">
                        <input class="form-control"
                               type="password"
                               name="password"
                               id="password-field"
                               placeholder="@lang('labels.backend.teachers.fields.password')"
                               autocomplete="new-password"
                               required>

                        <span class="password-toggle"
                              onclick="togglePassword()"
                              style="position:absolute; top:50%; right:10px; transform:translateY(-50%); cursor:pointer;">
                            <i class="fa fa-eye" style="color:#ccc;" id="toggle-icon"></i>
                        </span>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>Confirm Password <span style="color: red;">*</span></label>
                    <div class="position-relative">
                        <input class="form-control"
                               type="password"
                               name="password_confirmation"
                               id="password-confirmation-field"
                               placeholder="Confirm Password"
                               autocomplete="new-password"
                               required>

                        <span class="password-toggle"
                              onclick="togglePasswordConfirmation()"
                              style="position:absolute; top:50%; right:10px; transform:translateY(-50%); cursor:pointer;">
                            <i class="fa fa-eye" style="color:#ccc;" id="toggle-icon-confirmation"></i>
                        </span>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>@lang('labels.backend.teachers.fields.image')</label>
                    <div class="custom-file-upload-wrapper">
                        <input type="file"
                               name="image"
                               id="teacherImage"
                               class="custom-file-input">
                        <label for="teacherImage" class="custom-file-label">
                            <i class="fa fa-upload mr-1"></i> Choose a file
                        </label>
                    </div>
                </div>

                <!-- Gender -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>@lang('labels.backend.general_settings.user_registration_settings.fields.gender')</label>
                    <div class="mt-2">
                        <label class="radio-inline mr-3 mb-0">
                            <input type="radio" name="gender" value="male"> @lang('validation.attributes.frontend.male')
                        </label>

                        <label class="radio-inline mr-3 mb-0">
                            <input type="radio" name="gender" value="female"> @lang('validation.attributes.frontend.female')
                        </label>
                    </div>
                </div>

                <!-- Classification Number -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>Classification Number <span style="color: red;">*</span></label>
                    <input class="form-control"
                           type="text"
                           name="classfi_number"
                           placeholder="Classification Number"
                           required>
                </div>

                <!-- Nationality -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>Nationality</label>
                    <div class="custom-select-wrapper mt-2">
                        <select name="nationality" class="form-control custom-select-box">
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

                <!-- CV Upload -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>Cv Upload</label>
                    <div class="custom-file-upload-wrapper">
                        <input type="file"
                               name="cv_file"
                               id="cvUpload"
                               class="custom-file-input">
                        <label for="cvUpload" class="custom-file-label">
                            <i class="fa fa-upload mr-1"></i> Choose a file
                        </label>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>@lang('labels.teacher.facebook_link')</label>
                    <input class="form-control"
                           type="text"
                           name="facebook_link"
                           placeholder="@lang('labels.teacher.facebook_link')">
                </div>

                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>@lang('labels.teacher.twitter_link')</label>
                    <input class="form-control"
                           type="text"
                           name="twitter_link"
                           placeholder="@lang('labels.teacher.twitter_link')">
                </div>

                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>@lang('labels.teacher.linkedin_link')</label>
                    <input class="form-control"
                           type="text"
                           name="linkedin_link"
                           placeholder="@lang('labels.teacher.linkedin_link')">
                </div>

                <!-- Status -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>@lang('labels.backend.teachers.fields.status')</label>

                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                               class="custom-control-input"
                               id="switch"
                               name="status"
                               value="1">
                        <label class="custom-control-label" for="switch"></label>
                    </div>
                </div>

                <!-- Description -->
                <div class="col-lg-6 col-sm-12 mt-3">
                    <label>@lang('labels.teacher.description')</label>
                    <textarea class="form-control mt-2"
                              name="description"
                              style="height:100px"
                              placeholder="@lang('labels.teacher.description')"></textarea>
                </div>

                <!-- Buttons -->
                <div class="col-lg-12 col-sm-12 mt-4">
                    <div class="d-flex justify-content-between">
                        <div class="mr-4">
                            <a href="{{ route('admin.teachers.index') }}">
                                <button type="button" class="btn btn-secondary">
                                    @lang('buttons.general.cancel')
                                </button>
                            </a>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary">
                                @lang('buttons.general.crud.create')
                            </button>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="teacher" value="{{ route('admin.teachers.index') }}">
                <input type="hidden" id="new-assisment" value="{{ route('admin.courses.create') }}">

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

$(document).on('submit', '#addTeacher', function (e) {
    e.preventDefault();
    hrefurl=$(location).attr("href");
  last_part=hrefurl.substr(hrefurl.lastIndexOf('/') + 8)
    setTimeout(() => {
        let data = $('#addTeacher').serialize();
        let url = '{{route('admin.teachers.store')}}'
        var redirect_url=$("#teacher").val()
        var redirect_url_assi=$("#new-assisment").val()
    $.ajax({
            type: 'POST',
            url: url,
            data: data,
            datatype: "json",
            success: function (res) {
            console.log(res)
            // alert(last_part)

                if(last_part == 'teacher'){
                    window.location.href = redirect_url_assi;
                    return;
                }
                else{
                    window.location.href = redirect_url;
                    return;
                }

            },
            error: function(xhr, status, error) {
                res= JSON.parse(xhr.responseText)
               alert(res.errors.email[0]);
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

    function togglePasswordConfirmation() {
        var passwordField = document.getElementById("password-confirmation-field");
        var icon = document.getElementById("toggle-icon-confirmation");
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

    // Add password confirmation validation
    document.getElementById('password-confirmation-field').addEventListener('input', function() {
        var password = document.getElementById('password-field').value;
        var confirmation = this.value;
        if (password !== confirmation) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });

    document.getElementById('password-field').addEventListener('input', function() {
        var confirmation = document.getElementById('password-confirmation-field');
        if (confirmation.value) {
            confirmation.dispatchEvent(new Event('input'));
        }
    });
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
