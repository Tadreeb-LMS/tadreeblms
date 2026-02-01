@extends('frontend.layouts.app'.config('theme_layout'))

@section('title', app_name().' | '.__('labels.frontend.auth.login_box_title'))

<style>

    .ftlogo {
        align-items: center !important;
        display: flex !important;
        justify-content: center !important;
    }


    .card-header {
        text-align: center;
        padding: 25px;
        background-color: transparent !important;
        border-bottom: 0 !important;
    }

    .error-block {
        margin-bottom: 16px;
        padding: 0 10px;
        font-size: 15px;
    }
    h2, h3 {
        font-weight: 500;
        margin-top: 20px;
    }

    .nws-button button {
        height: 50px !important;
        width: auto !important;
        font-size: 15px;
    }

    .form-group.nws-button {
        text-align: center;
    }

    .card {
        /* padding: 20px; */
        margin: 35px;
    }

    .breadcrumb-section {
        background-color: #c1902d4a;
        padding: 75px 0;
    }

    .demo-credentials {
        margin-top: 45px;
        padding: 4px;
        display: flex;
        justify-content: center;
    }

    .demo-credentials h3 {
        font-size: 18px;
        padding: 10px;
        font-weight: bold;
    }
    
</style>

@section('content')


<section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
    <div class="blakish-overlay"></div>
    <div class="container">
        <div class="page-breadcrumb-content text-center">
            <div class="page-breadcrumb-title">
                <h2 class="breadcrumb-head black bold">
                    Login To Account
                </h2>
            </div>
        </div>
    </div>
</section>
<div class="row justify-content-center align-items-center">
    <div class="col col-sm-5 align-self-center">
        <div class="card">

            <div class="card-header">
                <h2>My Account</h2>
                <p>Login to continue</p>
            </div>

            <div class="card-body">
                <div class="error-block">
                    <span id="error-msg" class="error-response text-danger"></span>
                    <span class="success-response text-success">{{ session()->get('flash_success') }}</span>
                </div>
                <form method="POST" id="loginPageForm" action="{{ route('frontend.auth.login.post') }}">
                    @csrf

                    <div class="form-group">
                        
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control"
                               placeholder="{{ __('validation.attributes.frontend.email') }}"
                               maxlength="191"
                               required>
                    </div>

                    
                    <div class="form-group">
                        
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control"
                               placeholder="{{ __('validation.attributes.frontend.password') }}"
                               required>
                    </div>

                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox"
                                   class="form-check-input"
                                   name="remember"
                                   id="remember"
                                   value="1"
                                   checked>
                            <label class="form-check-label" for="remember">
                                @lang('labels.frontend.auth.remember_me')
                            </label>
                        </div>
                    </div>

                    {{-- Captcha --}}
                    <div class="form-group">
                        <div class="d-flex align-items-center">
                            <span class="font-weight-bold mr-2">
                                Captcha: {{ $captha }}
                            </span>

                            <input type="text"
                                name="captcha"
                                class="form-control"
                                style="width:120px"
                                required>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="form-group nws-button">
                        <button type="submit" id="loginBtn" class="text-center white text-capitalize">
                            @lang('labels.frontend.auth.login_button')
                        </button>
                    </div>

                    {{-- Forgot password --}}
                    <div class="form-group text-right">
                        <a href="{{ route('frontend.auth.password.reset') }}">
                            @lang('labels.frontend.passwords.forgot_password')
                        </a>
                    </div>
                </form>

                {{-- Social login --}}
                @if(!empty($socialiteLinks))
                    <div class="text-center mt-3">
                        {!! $socialiteLinks !!}
                    </div>
                @endif

                {{-- <div class="demo-credentials">
                    <h3>Demo credentials</h3>
                    <p>Email: demo@tadreeblms.com <br/> Password: demo12345</p>
                </div> --}}

            </div>

            
        </div>
    </div>
</div>
{{-- KEEP SCRIPT INSIDE THE SECTION --}}
@push('after-scripts')

<script>
$(document).ready(function () {

    $('#loginPageForm').on('submit', function (e) {
        e.preventDefault();

        let $form = $(this);
        let $errorBox = $('#error-msg');

        $errorBox.hide().text('');
        let $btn  = $('#loginBtn');
        $btn.prop('disabled', true).text('Processing...');

        $.ajax({
            type: 'POST',
            url: $form.attr('action'),
            data: $form.serialize(),
            dataType: 'json',

            success: function (response) {

                if (response.success === true && response.redirect) {
                    window.location.href = response.redirect;
                    return;
                }

                // Backend returned success=false
                if (response.message) {
                    $errorBox.text(response.message).show();
                    location.reload();
                }
            },

            error: function (xhr) {

                let message = 'Something went wrong. Please try again.';

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    // Validation error – show first message
                    const errors = xhr.responseJSON.errors;
                    message = Object.values(errors)[0][0];
                }
                else if (xhr.status === 401 || xhr.status === 403) {
                    message = xhr.responseJSON?.message ?? 'Invalid login credentials.';
                }
                else if (xhr.status === 419) {
                    message = 'Session expired. Please refresh the page.';
                }
                else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                $errorBox.text(message).show();
                location.reload();
            }
        });
    });

});
</script>
@endpush

@endsection