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

.modal-body .contact_form textarea {
    background-color: #eeeeee;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 10px;
    width: 100%;
    border: none;
}

@media (max-width: 768px) {
    .modal-dialog {
        min-height: calc(100vh - 20px);
    }

    #myModal .modal-body {
        padding: 15px;
    }
}
</style>

@if(!auth()->check())

<!-- LOGIN MODAL -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Header -->
            <div class="backgroud-style">
                <div class="popup-logo">
                    <img src="{{ asset('storage/logos/' . config('logo_popup')) }}" alt="">
                </div>
                <div class="popup-text text-center">
                    <h2>@lang('labels.frontend.modal.my_account')</h2>
                    <p>@lang('Login to continue')</p>
                </div>
                <button type="button" class="close modal_close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <div class="tab-content">
                    <div class="tab-pane container active" id="login">

                        <span class="error-response text-danger"></span>
                        <span class="success-response text-success">{{ session()->get('flash_success') }}</span>

                        <form id="loginForm" class="contact_form"
                              action="{{ route('frontend.auth.login.post') }}"
                              method="POST">
                            @csrf
                            <input type="hidden" name="redirect_url" id="redirect_url">

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

                            @if(config('access.captcha.registration'))
                                <div class="contact-info mb-2 text-center">
                                    {!! no_captcha()->display() !!}
                                    <input type="hidden" name="captcha_status" value="true">
                                    <span id="login-captcha-error" class="text-danger"></span>
                                </div>
                            @endif

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

<!-- REGISTER MODAL -->
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
                    @if($default_admin_email->email == 'admin@seeder.com')
                        <p>@lang('Please register an user as administrator')</p>
                    @endif
                </div>
                <button type="button" class="close modal_close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <div class="tab-content">
                    <div class="tab-pane container active" id="register">

                        <form id="registerForm" class="contact_form" method="POST"
                              action="{{ route('frontend.auth.register.post') }}">
                            @csrf
                            @if($default_admin_email->email == 'admin@seeder.com')
                                <input type="hidden" name="default_admin" value="1" />
                            @endif

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
                            <div class="contact-info mb-2">
                                <label>Preferred Language</label><br>
                                <label class="radio-inline mr-3 mb-0">
                                    <input type="radio" name="fav_lang" value="english" checked> {{ __('English') }}
                                </label>
                                <label class="radio-inline mr-3 mb-0">
                                    <input type="radio" name="fav_lang" value="arabic"> {{ __('Arabic') }}
                                </label>
                            </div>

                            <!-- Dynamic fields -->
                            @if(config('registration_fields'))
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

                            @if(config('access.captcha.registration'))
                                <div class="contact-info mt-3 text-center">
                                    {!! no_captcha()->display() !!}
                                    <input type="hidden" id="captcha_status" name="captcha_status" value="true">
                                    <span id="captcha-error" class="text-danger"></span>
                                </div>
                            @endif

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
@if(session('openModel'))
<script>
    $('#myModal').modal('show');
</script>
@endif

@if(config('access.captcha.registration'))
    {{ no_captcha()->script() }}
@endif

<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    $(document).ready(function () {

        // Toggle between login/register
        $(document).on('click', '.go-login', function () {
            $('#myRegisterModal').modal('hide');
            $('#myModal').modal('show');
        });

        $(document).on('click', '.go-register', function () {
            $('#myModal').modal('hide');
            $('#myRegisterModal').modal('show');
        });

        // Login AJAX
        $('#loginForm').on('submit', function (e) {
            e.preventDefault();
            var $this = $(this);
            $('.success-response, .error-response, #login-email-error, #login-password-error, #login-captcha-error').empty();

            $.ajax({
                type: $this.attr('method'),
                url: $this.attr('action'),
                data: $this.serialize(),
                success: function (response) {
                    if (response.errors) {
                        if (response.errors.email) $('#login-email-error').html(response.errors.email[0]);
                        if (response.errors.password) $('#login-password-error').html(response.errors.password[0]);
                        if (response.errors['g-recaptcha-response']) $('#login-captcha-error').html(response.errors['g-recaptcha-response'][0]);
                    }
                    if (response.success) {
                        window.location.href = response.redirect;
                    }
                },
                error: function (jqXHR) {
                    var response = jqXHR.responseJSON;
                    if (response.message) $('#login').find('span.error-response').html(response.message);
                }
            });
        });

        // Register AJAX
        $('#registerForm').on('submit', function (e) {
            e.preventDefault();
            var $this = $(this);
            var $button = $this.find('#registerButton');
            $button.text("{{ __('alerts.processing') }}").prop('disabled', true);

            $.ajax({
                type: $this.attr('method'),
                url: $this.attr('action'),
                data: $this.serialize(),
                success: function (data) {
                    // Clear errors
                    $('#first-name-error, #last-name-error, #email-error, #password-error, #captcha-error').empty();

                    if (data.errors) {
                        if (data.errors.first_name) $('#first-name-error').html(data.errors.first_name[0]);
                        if (data.errors.last_name) $('#last-name-error').html(data.errors.last_name[0]);
                        if (data.errors.email) $('#email-error').html(data.errors.email[0]);
                        if (data.errors.password) $('#password-error').html(data.errors.password[0]);
                        if (data.errors['g-recaptcha-response']) $('#captcha-error').html(data.errors['g-recaptcha-response'][0]);
                    }

                    if (data.success) {
                        $('#registerForm')[0].reset();
                        const alertHtml = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                            Registration is done successfully!
                        </div>`;
                        $('#registerForm').prepend(alertHtml);
                        setTimeout(() => $('.alert').alert('close'), 3000);

                        if (data.redirect == 'back') {
                            location.reload();
                        } else {
                            window.location.href = data.redirect || "{{ route('admin.dashboard') }}";
                        }
                    }
                    $button.text("@lang('labels.frontend.modal.register_now')").prop('disabled', false);
                }
            });
        });

    });
});
</script>
@endpush
