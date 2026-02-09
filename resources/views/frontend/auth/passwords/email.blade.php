@extends('frontend'.(session()->get('display_type') == "rtl" ? "-rtl" : "").'.layouts.app'.config('theme_layout'))

@section('title', app_name() . ' | ' . __('labels.frontend.passwords.reset_password_box_title'))

<style>
    .breadcrumb-section {
        background-color: #c1902d4a;
        padding: 75px 0;
    }
</style>

@section('content')
<section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
    <div class="blakish-overlay"></div>
    <div class="container">
        <div class="page-breadcrumb-content text-center">
            <div class="page-breadcrumb-title">
                <h2 class="breadcrumb-head black bold">
                    {{ __('labels.frontend.passwords.reset_password_box_title') }}
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

                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Success Message --}}
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- Reset Password Form --}}
                    <form method="POST" action="{{ route('frontend.auth.password.email.post') }}">
                        @csrf

                        <div class="form-group">
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   placeholder="{{ __('validation.attributes.frontend.email') }}"
                                   maxlength="191"
                                   required
                                   autofocus>
                        </div>

                        <div class="form-group mb-0 text-center">
                            <button type="submit"
                                    class="cpwd nws-button btn btn-info">
                                {{ __('labels.frontend.passwords.send_password_reset_link_button') }}
                            </button>
                        </div>
                    </form>

                </div><!-- card-body -->
            </div><!-- card -->
        </div><!-- col -->
    </div><!-- row -->
</section>
@endsection
