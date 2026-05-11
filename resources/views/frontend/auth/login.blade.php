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
        padding: 120px 0 40px !important;
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
    align-items: center;
    gap: 12px; /* equal spacing between ALL elements */
    background: #f4f6f8;
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
}

    .captcha-container img {
        height: 70px;
        width: auto;
        border-radius: 4px;
    }
    
    
    .captcha-text {
        font-weight: bold;
        color: #333;
        font-family: monospace;
        font-size: 16px;
        letter-spacing: 2px;
    }

    .captcha-input {
        width: 200px !important;
        height: 34px !important;
        font-size: 13px;
        text-align: center;
        margin-left: auto;
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
                    {{ __('auth_pages.login.title') }}
                </h2>
            </div>
        </div>
    </div>
</section>
<div class="row justify-content-center align-items-center">
    <div class="col col-sm-5 align-self-center">
        <div class="card">

            <div class="card-header">
                <h2>{{ __('auth_pages.login.my_account') }}</h2>
                <p>{{ __('auth_pages.login.login_to_continue') }}</p>
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
                               value="{{ old('email') }}"
                               required>
                    </div>

                    
                    <div class="form-group">
                        
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control"
                               placeholder="{{ __('validation.attributes.frontend.password') }}"
                               value="{{ old('password') }}"
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
                            <canvas id="captchaCanvas" width="180" height="50"></canvas>
                            <button type="button" id="refreshCaptcha" style="border:none; background:none; cursor:pointer;">
                                🔄
                            </button>
                            <input type="hidden" id="captchaHidden" name="captcha_hidden">

                            <input type="text"
                                id="captcha-input"
                                name="captcha_input"
                                class="form-control captcha-input"
                                placeholder="Enter Captcha"
                                required
                            >
                            @if ($errors->has('captcha'))
                                <span class="text-danger">
                                    {{ $errors->first('captcha') }}
                                </span>
                            @endif
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

let generatedCaptcha = '';

function generateCaptchaText(length = 6) {

    const chars =
        'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';

    let captcha = '';

    for (let i = 0; i < length; i++) {

        captcha += chars.charAt(
            Math.floor(Math.random() * chars.length)
        );
    }

    return captcha;
}


function randomColor() {

    return `rgb(
        ${Math.floor(Math.random() * 150)},
        ${Math.floor(Math.random() * 150)},
        ${Math.floor(Math.random() * 150)}
    )`;
}


function drawNoise(ctx, width, height) {

    // Noise Lines
    for (let i = 0; i < 2; i++) {

        ctx.beginPath();

        ctx.moveTo(
            Math.random() * width,
            Math.random() * height
        );

        ctx.lineTo(
            Math.random() * width,
            Math.random() * height
        );

        ctx.strokeStyle = randomColor();
        ctx.lineWidth = 2;
        ctx.stroke();
    }

    // Noise Dots
    for (let i = 0; i < 25; i++) {

        ctx.beginPath();

        ctx.arc(
            Math.random() * width,
            Math.random() * height,
            1.5,
            0,
            Math.PI * 2
        );

        ctx.fillStyle = randomColor();
        ctx.fill();
    }
}

//  Generate Captcha

function generateNewCaptcha() {

    generatedCaptcha = generateCaptchaText();

    const canvas = document.getElementById('captchaCanvas');
    const ctx = canvas.getContext('2d');

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = '#f4f6f8';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.font = '28px Arial';
    ctx.textBaseline = 'middle';

    for (let i = 0; i < generatedCaptcha.length; i++) {

        const char = generatedCaptcha[i];

        const x = 15 + (i * 25);
        const y = canvas.height / 2;

        const angle = (Math.random() - 0.5) * 0.5;

        ctx.save();

        ctx.translate(x, y);

        ctx.rotate(angle);

        ctx.fillStyle = randomColor();

        ctx.fillText(char, 0, 0);

        ctx.restore();
    }

    drawNoise(ctx, canvas.width, canvas.height);

    $('#captcha-input').val('');
    $('#captcha-error').text('');
}


// Validate Captcha

function validateCaptcha() {

    const userCaptcha = $('#captcha-input')
        .val()
        .trim()
        .toLowerCase();

    const actualCaptcha = generatedCaptcha
        .trim()
        .toLowerCase();

    return userCaptcha === actualCaptcha;
}

/* Document Ready */
$(document).ready(function () {

    generateNewCaptcha();

    $('#refreshCaptcha').on('click', function () {
        generateNewCaptcha();
    });

    $('#loginPageForm').on('submit', function (e) {

        e.preventDefault();

        $('#captcha-error').text('');

        
        // Validate Captcha First
        
        if (!validateCaptcha()) {

            $('#captcha-error')
                .text('Invalid captcha');

            generateNewCaptcha();

            $('#captcha-input').focus();

            return false;
        }

        let $form = $(this);
        let $errorBox = $('#error-msg');
        let $btn = $('#loginBtn');

        $errorBox.hide().text('');

        $btn
            .prop('disabled', true)
            .text('Processing...');

       
        $.ajax({

            type: 'POST',

            url: $form.attr('action'),

            data: $form.serialize(),

            dataType: 'json',

            success: function (response) {

                if (
                    response.success === true &&
                    response.redirect
                ) {
                    window.location.href =
                        response.redirect;
                }
            },

            error: function (xhr) {

                let message =
                    @json(__('course_pages.admin_lessons_create.something_went_wrong'));

                if (
                    xhr.status === 422 &&
                    xhr.responseJSON?.errors
                ) {

                    const errors =
                        xhr.responseJSON.errors;

                    message =
                        Object.values(errors)[0][0];
                }

                $('#error-msg')
                    .text(message)
                    .show();

                generateNewCaptcha();
            },

            complete: function () {

                $('#loginBtn')
                    .prop('disabled', false)
                    .text(
                        @json(__('labels.frontend.auth.login_button'))
                    );
            }
        });
    });
});

</script>
@endpush

@endsection