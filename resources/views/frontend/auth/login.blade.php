@extends('frontend.layouts.app'.config('theme_layout'))

@section('title', app_name().' | '.__('labels.frontend.auth.login_box_title'))

<style>
    /* Compact & Aesthetic Login Styles */
    
    .ftlogo {
        align-items: center !important;
        display: flex !important;
        justify-content: center !important;
    }

    /* Breadcrumb - Compact */
    .breadcrumb-section {
        background-color: #c1902d4a;
        padding: 20px 0 !important; /* Reduced from 75px */
    }

    /* Card Styling - Glassmorphic & Compact */
    .card {
        margin: 20px auto !important; /* Reduced from 35px */
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border-radius: 12px;
        background: #fff;
    }

    .card-header {
        text-align: center;
        padding: 15px 20px 5px !important; /* Reduced padding */
        background-color: transparent !important;
        border-bottom: 0 !important;
    }
    
    .card-header h2 {
        font-weight: 700;
        font-size: 24px;
        margin-top: 5px;
        margin-bottom: 5px;
        color: #333;
    }
    
    .card-header p {
        font-size: 14px;
        color: #777;
        margin-bottom: 0;
    }

    .card-body {
        padding: 15px 25px 25px; /* Compact body padding */
    }

    .error-block {
        margin-bottom: 12px;
        padding: 0 5px;
        font-size: 14px;
        text-align: center;
    }

    /* Form Controls */
    .form-group {
        margin-bottom: 12px; /* Tighter spacing */
    }
    
    .form-control {
        height: 42px;
        border-radius: 6px;
        font-size: 14px;
    }

    /* Button */
    .nws-button button {
        height: 45px !important; /* Slightly smaller */
        width: 100% !important; /* Full width */
        font-size: 16px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .nws-button button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .form-group.nws-button {
        text-align: center;
        margin-top: 15px;
        margin-bottom: 0;
    }

    /* Captcha Styling */
    .captcha-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f4f6f8;
        padding: 6px 12px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
    }
    
    .captcha-text {
        font-weight: bold;
        color: #333;
        font-family: monospace;
        font-size: 16px;
        letter-spacing: 2px;
    }

    .captcha-input {
        width: 100px !important;
        height: 34px !important;
        font-size: 13px;
        text-align: center;
    }

    /* Links */
    .forgot-password-link {
        font-size: 13px;
        color: #666;
        text-decoration: none;
    }
    
    .forgot-password-link:hover {
        text-decoration: underline;
        color: #333;
    }

    .demo-credentials {
        margin-top: 20px;
        padding: 4px;
        display: flex;
        justify-content: center;
    }

    .demo-credentials h3 {
        font-size: 16px;
        padding: 5px;
        font-weight: bold;
    }
    
    /* Mobile Optimization */
    @media (max-width: 768px) {
        .breadcrumb-section {
            display: none; /* Hide breadcrumb on mobile */
        }
        
        body {
            background-color: #fff; /* White background for mobile to blend with card */
        }
        
        .card {
            margin: 0 !important;
            box-shadow: none;
            border: none;
            height: 100vh; /* Full screen card */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .container {
            padding: 0 15px;
        }
        
        .col.col-sm-5 {
            flex: 0 0 100%;
            max-width: 100%;
        }
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

                    

                    <div class="d-flex justify-content-between align-items-center mb-3">
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
                        
                        <a href="{{ route('frontend.auth.password.reset') }}" class="forgot-password-link">
                            @lang('labels.frontend.passwords.forgot_password')
                        </a>
                    </div>

                    {{-- Captcha --}}
                    <div class="form-group">
                        <div class="captcha-container">
                            <span class="captcha-text">
                                Captcha: {{ $captha }}
                            </span>

                            <input type="text"
                                name="captcha"
                                class="form-control captcha-input"
                                placeholder="Code"
                                required>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="form-group nws-button">
                        <button type="submit" id="loginBtn" class="text-center white text-capitalize">
                            @lang('labels.frontend.auth.login_button')
                        </button>
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