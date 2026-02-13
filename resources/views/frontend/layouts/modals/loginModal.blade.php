<style>
    .modal-dialog {
        margin: 1.75em auto;
        min-height: calc(100vh - 60px);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

  .modal_close {
    position: absolute;
    top: -13px;
    right: -4px;
    color: #fff;
    background: linear-gradient(to right, #7ba91f 0%, #a1bf62 51%, #9dc15d 100%) !important;
    font-size: 28px;
    line-height: 27px;
    font-weight: 100;
    opacity: 1;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: 4px solid #fff;
    box-shadow: 0px 0px 6px 1px rgb(0 0 0 / 18%);
    padding: inherit !important;
}

    .g-recaptcha div {
        margin: auto;
    }

    .modal-body .contact_form input[type='radio'] {
        width: auto;
        height: auto;
    }
    .modal-body .contact_form textarea{
        background-color: #eeeeee;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 10px;
        width: 100%;
        border: none
    }

    input.captcha {
        width: 100px !important;
        height: 30px !important;
    }

    @media (max-width: 768px) {
        .modal-dialog {
            min-height: calc(100vh - 20px);
        }

        #myModal .modal-body {
            padding: 15px;
        }
    }
    .row.justify-content-center.align-items-center {
        background: #f9f9f4;
    }

</style>
<?php
//$fields = json_decode(config('registration_fields'));
//$inputs = ['text','number','date','gender'];
//dd($fields);
?>
@if(!auth()->check())

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="backgroud-style">
                <div class="popup-logo">
                    <img src="{{ asset('storage/logos/' . config('logo_popup')) }}" alt="">
                </div>
                <div class="popup-text text-center">
                    <h2>@lang('labels.frontend.modal.my_account')</h2>
                    <p>@lang('Login to continue')</p>
                </div>
                <button type="button" class="close modal_close" aria-hidden="true">×</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div class="tab-content">
                    <div class="tab-pane container active" id="login">

                        <span class="error-response text-danger"></span>
                        <span class="success-response text-success">{{ session()->get('flash_success') }}</span>

                        <form id="loginForm" class="contact_form"
                              action="{{ route('frontend.auth.login.post') }}"
                              method="POST" enctype="multipart/form-data">

                            @csrf

                            <input type="hidden" name="redirect_url" id="redirect_url">
                            <input type="hidden" name="active_page" class="active_page" value="{{ Route::currentRouteName() }}">

                            <div class="contact-info mb-2">
                                <input type="email" name="email" class="form-control mb-0"
                                       maxlength="191"
                                       placeholder="{{ __('validation.attributes.frontend.email') }}">
                                <span id="login-email-error" class="text-danger"></span>
                            </div>

                            <div class="contact-info mb-2">
                                <input type="password" name="password" class="form-control mb-0"
                                       placeholder="{{ __('validation.attributes.frontend.password') }}">
                                <span id="login-password-error" class="text-danger"></span>

                                <a class="text-info p-0 d-block text-right my-2"
                                   href="{{ route('frontend.auth.password.reset') }}">
                                    @lang('labels.frontend.passwords.forgot_password')
                                </a>
                            </div>

                            <div class="contact-info mb-2 catcha-block">
                                <label>Captcha: <span id="login-captcha-question"></span></label>
                                <input type="text" name="captcha" class="captcha" required>
                                <span id="login-captcha-error" class="text-danger"></span>
                            </div>

                            <div class="nws-button text-center white text-capitalize">
                                <button type="submit">@lang('labels.frontend.modal.login_now')</button>
                            </div>
                        </form>

                        <div id="socialLinks" class="text-center"></div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


    <div class="modal fade" id="myRegisterModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header1 backgroud-style">
                <div class="popup-logo">
                    <img src="{{ asset('storage/logos/' . config('logo_popup')) }}" alt="">
                </div>
                <div class="popup-text text-center">
                    <h2>@lang('Register')</h2>
                    <p>@lang('Please register yourself')</p>
                    {{-- {{ $default_admin_email }} --}}
                    @if($default_admin_email->email == env('DEMO_EMAIL', 'admin@seeder.com'))
                    <p>@lang('Please register an user as administrator')</p>
                    @endif
                </div>
                <button type="button" class="close modal_close" aria-hidden="true">×</button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <div class="tab-content">
                    <div class="tab-pane container active" id="register">

                        <span id="register-captcha-error" class="captcha-error text-danger"></span>
                        <span class="success-response text-success">{{ session()->get('flash_success') }}</span>
                        <form id="registerForm" class="contact_form" method="POST" action="#">
                            @csrf
                            @if($default_admin_email->email == 'admin@seeder.com')
                            <input type="hidden" name="default_admin" value="1" />
                            @endif

                            <input type="hidden" name="active_page" class="active_page" value="{{ Route::currentRouteName() }}">

                            <div class="contact-info mb-2">
                                <input type="text" name="first_name" class="form-control mb-0"
                                       maxlength="191"
                                       placeholder="{{ __('validation.attributes.frontend.first_name') }}">
                                <span id="first-name-error" class="text-danger"></span>
                            </div>

                            <div class="contact-info mb-2">
                                <input type="text" name="last_name" class="form-control mb-0"
                                       maxlength="191"
                                       placeholder="{{ __('validation.attributes.frontend.last_name') }}">
                                <span id="last-name-error" class="text-danger"></span>
                            </div>

                            <div class="contact-info mb-2">
                                <input type="email" name="email" class="form-control mb-0"
                                       maxlength="191"
                                       placeholder="{{ __('validation.attributes.frontend.email') }}">
                                <span id="email-error" class="text-danger"></span>
                            </div>

                            <div class="contact-info mb-2">
                                <input type="password" name="password" class="form-control mb-0"
                                       placeholder="{{ __('validation.attributes.frontend.password') }}">
                            </div>

                            <div class="contact-info mb-2">
                                <input type="password" name="password_confirmation" class="form-control mb-0"
                                       placeholder="{{ __('validation.attributes.frontend.password_confirmation') }}">
                                <span id="password-error" class="text-danger"></span>
                            </div>

                            <!-- Language Select -->
                            <div class="contact-info mb-2 plang">
                                <label>Preferred Language</label><br>

                                <label class="radio-inline mr-3 mb-0">
                                    <input type="radio" name="fav_lang" value="english" checked> {{ __('English') }}
                                </label>

                                <label class="radio-inline mr-3 mb-0">
                                    <input type="radio" name="fav_lang" value="arabic"> {{ __('Arabic') }}
                                </label>
                            </div>

                            <!-- Dynamic fields -->
                            @if(config('registration_fields') != NULL)
                                @php
                                    $fields = json_decode(config('registration_fields'));
                                    $inputs = ['text','number','date'];
                                @endphp

                                @foreach($fields as $item)
                                    @if(in_array($item->type, $inputs))
                                        <div class="contact-info mb-2">
                                            <input type="{{ $item->type }}"
                                                   class="form-control mb-0"
                                                   name="{{ $item->name }}"
                                                   value="{{ old($item->name) }}"
                                                   placeholder="{{ __('labels.backend.general_settings.user_registration_settings.fields.' . $item->name) }}">
                                        </div>

                                    @elseif($item->type == 'radio')
                                        <div class="contact-info mb-2">
                                            <label class="radio-inline mr-3 mb-0">
                                                <input type="radio" name="{{ $item->name }}" value="male">
                                                {{ __('validation.attributes.frontend.male') }}
                                            </label>

                                            <label class="radio-inline mr-3 mb-0">
                                                <input type="radio" name="{{ $item->name }}" value="female">
                                                {{ __('validation.attributes.frontend.female') }}
                                            </label>

                                            <label class="radio-inline mr-3 mb-0">
                                                <input type="radio" name="{{ $item->name }}" value="other">
                                                {{ __('validation.attributes.frontend.other') }}
                                            </label>
                                        </div>

                                    @elseif($item->type == 'textarea')
                                        <div class="contact-info mb-2">
                                            <textarea class="form-control mb-0"
                                                      name="{{ $item->name }}"
                                                      placeholder="{{ __('labels.backend.general_settings.user_registration_settings.fields.' . $item->name) }}">{{ old($item->name) }}</textarea>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            <div class="contact-info mb-2 catcha-block">
                                <label>Captcha: <span id="register-captcha-question"></span></label>
                                <input type="text" name="captcha" class="captcha" required>
                                <span id="captcha-error" class="text-danger"></span>
                            </div>

                            <div class="nws-button text-center white text-capitalize">
                                <button id="registerButton" type="submit">@lang('labels.frontend.modal.register_now')</button>
                            </div>

                            <a href="#" class="go-login float-right text-info pr-0">
                                @lang('labels.frontend.modal.already_user_note')
                            </a>

                            <a href="{{ route('frontend.auth.teacher.register') }}"
                               class="fgo-register float-left text-info mt-2">
                                @lang('labels.teacher.teacher_register')
                            </a>

                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endif

@push('after-scripts')
    @if (session('openModel'))
        <script>
            $('#myModal').modal('show');
        </script>
    @endif

        

        <script>


            function reloadIfDifferent(targetRoute) {
                const currentRoute = $('.active_page').val();

                if (currentRoute == targetRoute) {
                    location.reload();
                }
            }

            $(document).on('click', '.modal_close', function () {
                $(this).closest('.modal').modal('hide');
                reloadIfDifferent('frontend.auth.login');
            });



             hrefurl=$(location).attr("href");
                last_part=hrefurl.substr(hrefurl.lastIndexOf('/') + 1)
        // console.log(last_part);
        if(last_part == '?openModal'){
            $('#myModal').modal('show');
        }
        </script>



    
    <script>

        const refreshCaptchaUrl = "{{ route('refresh_captcha') }}";

        function loadCaptcha(mode) {
            fetch(refreshCaptchaUrl)
                .then(res => res.json())
                .then(data => {
                    if(mode == 'login') {
                        document.getElementById('login-captcha-question').innerText =
                        data.captcha_question;
                    }
                    if(mode == 'register') {
                        //alert("hi")
                        document.getElementById('register-captcha-question').innerText =
                        data.captcha_question;
                    }
                });
        }

        

        $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).ready(function () {
                $(document).on('click', '.go-login', function () {

                     //loadCaptcha('login');   
                     $('#myRegisterModal').modal('hide');
                     $('#myModal').modal('show');
                    // $('#register').removeClass('active').addClass('fade')
                    // $('#login').addClass('active').removeClass('fade')

                });
                $(document).on('click', '.go-register', function () {
                    //loadCaptcha('register');
                    $('#login').removeClass('active').addClass('fade')
                    $('#register').addClass('active').removeClass('fade')
                });

                $(document).on('click', '#openLoginModal', function (e) {
                    
                    

                    $.ajax({
                        type: "GET",
                        url: "{{route('frontend.auth.login')}}",
                        success: function (response) {

                            loadCaptcha('login');

                            $('#login-captcha-question').html(response.captcha_question)
                            $('#socialLinks').html(response.socialLinks)
                            const $modal = $('#myModal');

                            
                            const form = $modal.find('form')[0];
                            if (form) form.reset();
                            $modal.find('.text-danger').text('');

                            
                            $modal.find('input[type=radio], input[type=checkbox]').prop('checked', false);

                            
                            $modal.modal('show');
                        },
                    });
                });

                $(document).on('click', '#openRegisterModal', function (e) {
                    //alert("hi")
                    $.ajax({
                        type: "GET",
                        url: "{{route('frontend.auth.register')}}",
                        success: function (response) {
                            $('#socialLinks').html(response.socialLinks);
                            loadCaptcha('register');
                             $('#register-captcha-question').html(response.captcha_question);
                            let form = $('#myRegisterModal').find('form')[0];
                            if (form) form.reset();

                            $('#myRegisterModal').find('.text-danger').text('');
                            $('#myRegisterModal').modal('show');
                            //alert("jo")
                        },
                    });
                });



                $('#loginForm').on('submit', function (e) {
                    e.preventDefault();

                    const redirect_url = localStorage.getItem('redirect_url');
                    localStorage.removeItem('redirect_url');

                    var $this = $(this);
                    $('.success-response').empty();
                    $('.error-response').empty();

                    $.ajax({
                        type: $this.attr('method'),
                        url: $this.attr('action'),
                        data: $this.serializeArray(),
                        dataType: $this.data('type'),
                        success: function (response) {                            
                            $('#login-email-error').empty();
                            $('#login-password-error').empty();
                            $('#login-captcha-error').empty();

                            if (response.errors) {
                                if (response.errors.email) {
                                    $('#login-email-error').html(response.errors.email[0]);
                                }
                                if (response.errors.password) {
                                    $('#login-password-error').html(response.errors.password[0]);
                                }

                                var captcha = "g-recaptcha-response";
                                if (response.errors[captcha]) {
                                    $('#login-captcha-error').html(response.errors[captcha][0]);
                                }
                            }

                            if (response.success) {
                                window.location.href = response.redirect;

                                //location.reload();

                                // $('#loginForm')[0].reset();
                                // if (response.redirect == 'back') {
                                //     if (redirect_url) {
                                //         window.location.href = redirect_url;
                                //         return;
                                //     }else{
                                //         location.reload();
                                //     }
                                // } else {
                                //     window.location.href = "{{route('admin.dashboard')}}"
                                // }
                            }
                        },
                        error: function (jqXHR) {
                            var response = $.parseJSON(jqXHR.responseText);
                            console.log(jqXHR)
                            if (response.message) {
                                $('#login').find('span.error-response').html(response.message)
                            }
                        }
                    });
                });

                $(document).on('submit','#registerForm', function (e) {
                    e.preventDefault();
                    
                    var $this = $(this);
                    //var $form = $(this);
                    var $button = $this.find('#registerButton');
                    $button.text("{{ __('alerts.processing') }}").prop('disabled', true);

                    $.ajax({
                        type: $this.attr('method'),
                        url: "{{  route('frontend.auth.register.post')}}",
                        data: $this.serializeArray(),
                        dataType: $this.data('type'),
                        success: function (data) {
                            //alert(data.redirect)
                            $('#first-name-error').empty()
                            $('#last-name-error').empty()
                            $('#email-error').empty()
                            $('#password-error').empty()
                            $('#register-captcha-error').empty()
                            if (data.errors) {
                                if (data.errors.first_name) {
                                    $('#first-name-error').html(data.errors.first_name[0]);
                                }
                                if (data.errors.last_name) {
                                    $('#last-name-error').html(data.errors.last_name[0]);
                                }
                                if (data.errors.email) {
                                    $('#email-error').html(data.errors.email[0]);
                                }
                                if (data.errors.password) {
                                    $('#password-error').html(data.errors.password[0]);
                                }

                                var captcha = "g-recaptcha-response";
                                if (data.errors[captcha]) {
                                    $('#register-captcha-error').html(data.errors[captcha][0]);
                                }
                            }

                            console.log(data)

                            if(data.success == false && data.error_type == 'captcha') {
                                $('#register-captcha-error').html(data.message);
                                $button.text("{{ __('labels.frontend.modal.register_now') }}").prop('disabled', false);
                            }

                            if (data.success) {
                                
                                $('#registerForm')[0].reset();

                                
                                const alertHtml = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                                    ${ 'Registration is done successfully !' }
                                </div>`;

                                
                                $('#registerForm').prepend(alertHtml);

                                
                                setTimeout(() => {
                                    $('.alert').alert('close');
                                }, 3000);

                                
                                if (data.redirect == 'back') {
                                    location.reload();
                                } else {
                                    window.location.href = "{{ route('admin.dashboard') }}";
                                }
                            }

                        }
                    });
                });
            });

        });
    </script>
@endpush
