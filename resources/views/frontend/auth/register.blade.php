@extends('frontend.layouts.app'.config('theme_layout'))

@section('title', app_name() . ' | ' . __('labels.frontend.auth.register_box_title'))

@section('content')
    <div class="row justify-content-center align-items-center">
        <div class="col col-sm-8 align-self-center">
            <div class="card">
                <div class="card-header">
                    <strong>
                        @lang('labels.frontend.auth.register_box_title')
                    </strong>
                </div><!--card-header-->

                <div class="card-body">
                    {{ html()->form('POST', route('frontend.auth.register.post'))->id('registerForm')->open() }}
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    {{ html()->label(__('validation.attributes.frontend.first_name'))->for('first_name') }}

                                    {{ html()->text('first_name')
                                        ->class('form-control')
                                        ->placeholder(__('validation.attributes.frontend.first_name'))
                                        ->attribute('maxlength', 191) }}
                                </div><!--col-->
                            </div><!--row-->

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    {{ html()->label(__('validation.attributes.frontend.last_name'))->for('last_name') }}

                                    {{ html()->text('last_name')
                                        ->class('form-control')
                                        ->placeholder(__('validation.attributes.frontend.last_name'))
                                        ->attribute('maxlength', 191) }}
                                </div><!--form-group-->
                            </div><!--col-->
                        </div><!--row-->

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    {{ html()->label(__('validation.attributes.frontend.email'))->for('email') }}

                                    {{ html()->email('email')
                                        ->class('form-control')
                                        ->placeholder(__('validation.attributes.frontend.email'))
                                        ->attribute('maxlength', 191)
                                        ->required() }}
                                </div><!--form-group-->
                            </div><!--col-->
                        </div><!--row-->

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    {{ html()->label(__('validation.attributes.frontend.password'))->for('password') }}

                                    {{ html()->password('password')
                                        ->class('form-control')
                                        ->placeholder(__('validation.attributes.frontend.password'))
                                        ->required() }}
                                </div><!--form-group-->
                            </div><!--col-->
                        </div><!--row-->

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    {{ html()->label(__('validation.attributes.frontend.password_confirmation'))->for('password_confirmation') }}

                                    {{ html()->password('password_confirmation')
                                        ->class('form-control')
                                        ->placeholder(__('validation.attributes.frontend.password_confirmation'))
                                        ->required() }}
                                </div><!--form-group-->
                            </div><!--col-->
                        </div><!--row-->

                        <div class="row">
                            <div class="col">
                                <div class="form-group mb-4 mt-2">
                                    <label class="font-weight-bold d-block mb-2">{{ __('auth_pages.login.captcha') }}</label>
                                    <div class="row align-items-center">
                                        <div class="col-6 d-flex align-items-center" style="gap: 15px;">
                                            <div style="border: 1px solid #ced4da; border-radius: 4px; padding: 2px; background: #fff;">
                                                <img id="register-captcha-image" src="{{ session('captcha_image') }}" alt="Captcha" style="border-radius: 3px; height: 60px; width: 180px; object-fit: cover;">
                                            </div>
                                            <button type="button" id="register-captcha-refresh" class="btn btn-outline-secondary btn-sm" title="Refresh Captcha" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M21 2v6h-6"></path>
                                                    <path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path>
                                                    <path d="M3 22v-6h6"></path>
                                                    <path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <input type="text" name="captcha" class="form-control form-control-lg" placeholder="Enter captcha" required style="font-size: 1.1rem; padding: 10px 15px;">
                                        </div>
                                    </div>
                                </div><!--form-group-->
                            </div><!--col-->
                        </div><!--row-->


                        <div class="row">
                            <div class="col">
                                <div class="form-group mb-0 clearfix">
                                    {{ form_submit(__('labels.frontend.auth.register_button')) }}
                                </div><!--form-group-->
                            </div><!--col-->
                        </div><!--row-->
                    {{ html()->form()->close() }}

                    <div class="row">
                        <div class="col">
                            <div class="text-center">
                                {!! $socialiteLinks !!}
                            </div>
                        </div><!--/ .col -->
                    </div><!-- / .row -->

                </div><!-- card-body -->
            </div><!-- card -->
        </div><!-- col-md-8 -->
    </div><!-- row -->
@endsection

@push('after-styles')
<style>
    .captcha-container {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .captcha-image {
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #f9f9f9;
    }

    .captcha-refresh-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
        color: #7ba91f;
        font-size: 18px;
        transition: transform 0.3s ease;
    }

    .captcha-refresh-btn:hover {
        transform: rotate(180deg);
        color: #5a8a0f;
    }

    .captcha-refresh-btn:active {
        transform: rotate(360deg);
    }
</style>
@endpush

@push('after-scripts')
<script>
    const refreshCaptchaUrl = "{{ route('refresh.captcha') }}";

    function refreshCaptcha() {
        fetch(refreshCaptchaUrl + '?t=' + new Date().getTime())
            .then(res => res.json())
            .then(data => {
                document.getElementById('register-captcha-image').src = data.captcha_image;
            })
            .catch(error => {
                console.error('Error refreshing captcha:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const refreshBtn = document.getElementById('register-captcha-refresh');
        
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                refreshCaptcha();
            });
        }

        $('#registerForm').on('submit', function (e) {
            e.preventDefault();

            let $form = $(this);
            let $btn = $form.find('input[type=submit], button[type=submit]');
            let originalText = $btn.val() || $btn.text();
            
            // Hide previous errors
            $form.find('.text-danger').remove();

            $btn.prop('disabled', true);
            if ($btn.is('input')) {
                $btn.val('Processing...');
            } else {
                $btn.text('Processing...');
            }

            $.ajax({
                type: 'POST',
                url: $form.attr('action'),
                data: $form.serialize(),
                dataType: 'json',

                success: function (data) {
                    if (data.errors) {
                        // Handle validation errors from Validator
                        $.each(data.errors, function(key, val) {
                            let input = $form.find('[name="'+key+'"]');
                            if (input.length) {
                                input.after('<span class="text-danger d-block mt-1">' + val[0] + '</span>');
                            }
                        });
                        if (data.errors.captcha || data.errors['g-recaptcha-response']) {
                            refreshCaptcha();
                            $form.find('[name="captcha"]').val('').focus();
                        }
                    } else if (data.success === false && data.error_type === 'captcha') {
                        // Custom captcha error
                        let input = $form.find('[name="captcha"]');
                        input.after('<span class="text-danger d-block mt-1">' + data.message + '</span>');
                        refreshCaptcha();
                        input.val('').focus();
                    } else if (data.success === true && data.redirect) {
                        if (data.redirect === 'back') {
                            location.reload();
                        } else {
                            window.location.href = data.redirect;
                        }
                    }
                },
                error: function (xhr) {
                    console.error("Registration error", xhr);
                    alert("Something went wrong. Please try again.");
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    if ($btn.is('input')) {
                        $btn.val(originalText);
                    } else {
                        $btn.text(originalText);
                    }
                }
            });
        });
    });
</script>
    @if(config('access.captcha.registration'))
        {!! Captcha::script() !!}
    @endif
@endpush
