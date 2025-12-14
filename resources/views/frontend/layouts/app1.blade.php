<!DOCTYPE html>


@langrtl
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
@else
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@endlangrtl

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (config('favicon_image') != '')
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('storage/logos/' . config('favicon_image')) }}" />
    @endif
    <title>@yield('title', app_name())</title>
    <meta name="description" content="@yield('meta_description', '')">
    <meta name="keywords" content="@yield('meta_keywords', '')">

    {{-- See https://laravel.com/docs/5.5/blade#stacks for usage --}}
    @stack('before-styles')

    <!-- Check if the language is set to RTL, so apply the RTL layouts -->
    <!-- Otherwise apply the normal LTR layouts -->

    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/flaticon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/meanmenu.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/video.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lightbox.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/progess.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.min.css') }}">
    {{-- <link rel="stylesheet" href="{{asset('assets/css/style.css')}}"> --}}
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome-all.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/colors/switch.css') }}">
    <link href="{{ asset('assets/css/colors/color-2.css') }}" rel="alternate stylesheet" type="text/css"
        title="color-2">
    <link href="{{ asset('assets/css/colors/color-3.css') }}" rel="alternate stylesheet" type="text/css"
        title="color-3">
    <link href="{{ asset('assets/css/colors/color-4.css') }}" rel="alternate stylesheet" type="text/css"
        title="color-4">
    <link href="{{ asset('assets/css/colors/color-5.css') }}" rel="alternate stylesheet" type="text/css"
        title="color-5">
    <link href="{{ asset('assets/css/colors/color-6.css') }}" rel="alternate stylesheet" type="text/css"
        title="color-6">
    <link href="{{ asset('assets/css/colors/color-7.css') }}" rel="alternate stylesheet" type="text/css"
        title="color-7">
    <link href="{{ asset('assets/css/colors/color-8.css') }}" rel="alternate stylesheet" type="text/css"
        title="color-8">
    <link href="{{ asset('assets/css/colors/color-9.css') }}" rel="alternate stylesheet" type="text/css"
        title="color-9">

    <link href="{{ asset('/vendor/unisharp/laravel-ckeditor/plugins/codesnippet/lib/highlight/styles/monokai.css') }}"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <script src="{{ asset('/vendor/unisharp/laravel-ckeditor/plugins/codesnippet/lib/highlight/highlight.pack.js') }}">
    </script>
    <script>
        hljs.initHighlightingOnLoad();
    </script>

    @yield('css')
    @stack('after-styles')

    @if (config('onesignal_status') == 1)
        {!! config('onesignal_data') !!}
    @endif

    @if (config('google_analytics_id') != '')
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('google_analytics_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());

            gtag('config', '{{ config('google_analytics_id') }}');
        </script>
    @endif
    @if (!empty(config('custom_css')))
        <style>
            {!! config('custom_css') !!}
        </style>
    @endif
    @if(config('app.locale') == 'ar')
    <style>
        .owl-nav {
            left: 0 !important;
            top: -90px;
            position: absolute;
        }
    </style>
    @endif
    <script src="/js/helpers/snap-pixel.js"></script>
</head>

<body class="{{ config('layout_type') }}"
    style="direction: {{ config('app.locale') == 'ar' ? 'rtl' : 'ltr' }}; text-align: {{ config('app.locale') == 'ar' ? 'right' : 'left' }}">
    @include('backend.includes.loader')
    <div id="google_translate_element"></div>
    <div id="app">
        {{-- <div id="preloader"></div> --}}
        @include('frontend.layouts.modals.loginModal', ['default_admin_email' => $default_admin_email])

        <!-- Start of Header section
        ============================================= -->
        <nav class="navbar navbar-expand-lg navbar-light">
           
                <div class="navbar-header float-left">
                    <a class="navbar-brand text-uppercase" href="{{ url('/') }}">
                        @if( isset($site_logo->value) )
                       <img src="{{ asset( $site_logo->value ) }}" alt="logo">
                       @else
                        <img src="{{ asset('assets/img/logo.png') }}" alt="logo" class="logoimg">
                       @endif
                    </a>
                </div>
          
            <button class="navbar-toggler ham-top-space" type="button" data-toggle="collapse"
                data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            @if (config('locale.status') && count($locales) > 1)
                <div class="nav-item px-3 dropdown">
                    <a class="nav-link dropdown-toggle nav-link" data-toggle="dropdown" href="{{ url('/') }}" role="button"
                        aria-haspopup="true" aria-expanded="false">
                        <span class="d-md-down-none">@lang('menus.language-picker.language') ({{ strtoupper(app()->getLocale()) }})</span>
                    </a>

                    @include('includes.partials.lang')
                </div>
            @endif
            
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                
                <ul class="navbar-nav ul-li ml-auto sm-rl-space">

                    <li class="px-lg-4 hamburger-top-space sm-tb-space">
                        <form action="/search" method="get" id="searchform">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text searchcourse" id="basic-addon1"><i
                                            class="bi bi-search" onclick="submit()"></i></span>
                                </div>
                                <input type="text" class="form-control" name="q"
                                    placeholder="@lang('Search for course')" aria-label="Username" required
                                    aria-describedby="basic-addon1">
                            </div>
                        </form>
                    </li>
                    
                    @if (count($custom_menus) > 0)
                        @foreach ($custom_menus as $menu)
                            @if ($menu['id'] == $menu['parent'])
                                @if (count($menu->subs) == 0)
                                    <li class="">
                                        <a href="{{ asset($menu->link) }}"
                                            class="nav-link {{ active_class(Active::checkRoute('frontend.user.dashboard')) }}"
                                            id="menu-{{ $menu->id }}">{{ trans('custom-menu.' . $menu_name . '.' . str_slug($menu->label)) }}</a>
                                    </li>
                                @else
                                    @if((isset(auth()->user()->employee_type) && auth()->user()->employee_type == 'external') || !auth()->check())
                                    <li class="menu-item-has-children ul-li-block sm-tb-space"
                                       >
                                        <a
                                            href="#!">{{ trans('custom-menu.' . $menu_name . '.' . str_slug($menu->label)) }}</a>
                                        <ul class="sub-menu">
                                            @foreach ($menu->subs as $item)
                                                @include('frontend.layouts.partials.dropdown', $item)
                                            @endforeach
                                        </ul>
                                    </li>
                                    @else
                                    <li class="">
                                        <a class="nav-link"
                                            href="{{ asset($menu->link) }}">{{ trans('custom-menu.' . $menu_name . '.' . str_slug($menu->label)) }}</a>
                                     </li>
                                    @endif
                                @endif
                            @endif
                        @endforeach
                    @endif

                    @if (auth()->check())
                        @if ($logged_in_user->hasRole('student'))
                            <li class="sm-tb-space">
                                <a href="{{ route('admin.dashboard') }}">@lang('navs.frontend.dashboard')</a>
                            </li>
                            <li class="sm-tb-space">
                                <a id="logout" href="{{ route('frontend.auth.logout') }}"><i class="fas fa-sign-out-alt"></i></a>
                            </li>
                        @else
                            <li class="menu-item-has-children ul-li-block px-1 sm-tb-space">
                                <a href="#!">{{ $logged_in_user->name }}</a>
                                <ul class="sub-menu">
                                    @can('view backend')
                                        <li>
                                            <a href="{{ route('admin.dashboard') }}">@lang('navs.frontend.dashboard')</a>
                                        </li>
                                    @endcan

                                    <li>
                                        <a href="{{ route('frontend.auth.logout') }}">@lang('navs.general.logout')</a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    @else
                        <li class="sm-tb-space">
                            <div class="log-in">
                                <a id="openLoginModal" data-target="#myModal" href="#">@lang('navs.general.login')</a>
                                {{-- @include('frontend.layouts.modals.loginModal') --}}

                            </div>
                        </li>
                        <li class="sm-tb-space">
                            <div class="log-in">
                                <a id="openRegisterModal" data-target="#myRegisterModal"
                                    href="#">@lang('SignUp')</a>
                                {{-- @include('frontend.layouts.modals.loginModal') --}}

                            </div>
                        </li>
                        <li class="sm-tb-space">
                            <div class="cart-search float-lg-right ul-li">
                                <ul class="lock-icon">
                                    <li>
                                        <a href="{{ route('cart.index') }}"><i class="fas fa-shopping-bag"></i>
                                            @if (auth()->check() && Cart::session(auth()->user()->id)->getTotalQuantity() != 0)
                                                <span
                                                    class="badge badge-danger position-absolute">{{ Cart::session(auth()->user()->id)->getTotalQuantity() }}</span>
                                            @endif
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                    @if (count($locales) > 1)
                        <!--li class="menu-item-has-children ul-li-block">
                                                <a href="#">
                                                    <span class="d-md-down-none">@lang('menus.language-picker.language')
                                                        ({{ strtoupper(app()->getLocale()) }})</span>
                                                </a>
                                                <ul class="sub-menu">
                                                    @foreach ($locales as $lang)
@if ($lang != app()->getLocale())
<li>
                                                                <a href="{{ '/lang/' . $lang }}"
                                                                   class=""> @lang('menus.language-picker.langs.' . $lang)</a>
                                                            </li>
@endif
@endforeach
                                                </ul>
                                            </li-->
                    @endif

                </ul>
                <!-- </div>
    </div> -->
                <!-- <form class="form-inline my-2 my-lg-0">
      <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
      <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
    </form> -->
            </div>
        </nav>

        <!-- Start of Header section
            ============================================= -->


        @yield('content')
        @include('cookieConsent::index')


        @include('frontend.layouts.partials.footer')

    </div><!-- #app -->

    <!-- Scripts -->

    @stack('before-scripts')

    <!-- For Js Library -->
    <script src="{{ asset('assets/js/jquery-2.1.4.min.js') }}"></script>
    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/js/jarallax.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/js/lightbox.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.meanmenu.js') }}"></script>
    <script src="{{ asset('assets/js/scrollreveal.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('assets/js/waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
    <script src="{{ asset('assets/js/gmap3.min.js') }}"></script>

    <script src="{{ asset('assets/js/switch.js') }}"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script>
        @if (request()->has('user') && request('user') == 'admin')

            $('#myModal').modal('show');
            $('#loginForm').find('#email').val('admin@lms.com')
            $('#loginForm').find('#password').val('secret')
            $('#loginForm').find('button').trigger('click');
        @elseif (request()->has('user') && request('user') == 'student')

            $('#myModal').modal('show');
            $('#loginForm').find('#email').val('student@lms.com')
            $('#loginForm').find('#password').val('secret')
            $('#loginForm').find('button').trigger('click');
        @elseif (request()->has('user') && request('user') == 'teacher')

            $('#myModal').modal('show');
            $('#loginForm').find('#email').val('teacher@lms.com')
            $('#loginForm').find('#password').val('secret')
            $('#loginForm').find('button').trigger('click');
        @endif
    </script>
    <script type="text/javascript">
        $('.main-slider').slick();
    </script>


    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script>
        @if (session()->has('show_login') && session('show_login') == true)
            $('#myModal').modal('show');
        @endif
        var font_color = "{{ config('font_color') }}"
        setActiveStyleSheet(font_color);
    </script>
    <script>
        function submit() {
            let form = document.getElementById("searchform");
            form.submit();
        }

        $('#logout').click(function (e) { 
            e.preventDefault();
            localStorage.removeItem('redirect_url');
            window.location.href = $(this).attr('href');
        });
    </script>

    @yield('js')

    @stack('after-scripts')

    @include('includes.partials.ga')

    @if (!empty(config('custom_js')))
        <script>
            {!! config('custom_js') !!}
        </script>
    @endif

</body>

</html>
