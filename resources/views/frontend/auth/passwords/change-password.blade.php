@extends('frontend' . (session()->get('display_type') == 'rtl' ? '-rtl' : '') . '.layouts.app' . config('theme_layout'))

@section('title', app_name() . ' | ' . __('labels.frontend.passwords.reset_password_box_title'))

@section('content')
    <section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
        <div class="blakish-overlay"></div>
        <div class="container">
            <div class="page-breadcrumb-content text-center">
                <div class="page-breadcrumb-title">
                    <h2 class="breadcrumb-head black bold">{{ __('labels.frontend.passwords.reset_password_box_title') }}
                    </h2>
                </div>
            </div>
        </div>
    </section>
    <section id="about-page" class="about-page-section pb-0">
        <div class="row justify-content-center align-items-center">
            <div class="col col-md-4 align-self-center">
                <div class="card border-0">

                    <div class="card-body">
                        <form action="/change-password" method="post" class="ajax">
                            <input type="hidden" name="token" value="{{ request()->token }}">
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label class="label">{{ __('course_pages.registration.password') }}</label>
                                        <input type="password" class="form-control" name="password">
                                    </div><!--form-group-->
                                </div><!--col-->
                            </div><!--row-->
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label class="label">{{ __('course_pages.registration.confirm_password') }}</label>
                                        <input type="password" class="form-control" name="password_confirmation">
                                    </div><!--form-group-->
                                </div><!--col-->
                            </div><!--row-->

                            <div class="row">
                                <div class="col">
                                    <div class="form-group mb-0 clearfix">
                                        <div class="text-center  text-capitalize">
                                            <button type="submit" class="cpwd nws-button btn-info btn "
                                                value="{{ __('auth_pages.change_password.submit') }}">{{ __('auth_pages.change_password.submit') }}</button>
                                        </div>
                                    </div><!--form-group-->
                                </div><!--col-->
                            </div><!--row-->
                        </form>
                    </div><!-- card-body -->
                </div><!-- card -->
            </div><!-- col-6 -->
        </div><!-- row -->
    </section>
@endsection
@push('after-styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet" />
@endpush
@push('after-scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="/js/helpers/form-submit.js"></script>
@endpush
