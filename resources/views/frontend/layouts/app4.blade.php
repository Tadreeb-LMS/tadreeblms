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
        <title>@yield('title', app_name())</title>
        <meta name="description" content="@yield('meta_description', '')">
        <meta name="keywords" content="@yield('meta_keywords', '')">


    {{-- See https://laravel.com/docs/5.5/blade#stacks for usage --}}
    @stack('before-styles')

    <!-- Check if the language is set to RTL, so apply the RTL layouts -->
        <!-- Otherwise apply the normal LTR layouts -->

        <link rel="stylesheet" href="{{asset('assets/css/owl.carousel.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/fontawesome-all.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/flaticon.css')}}">
        <link rel="stylesheet" type="text/css" href="{{asset('assets/css/meanmenu.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/video.min.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/lightbox.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/progess.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/animate.min.css')}}">
        {{--<link rel="stylesheet" href="{{asset('assets/css/style.css')}}">--}}
        <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">

        <link rel="stylesheet" href="{{asset('assets/css/responsive.css')}}">

        <link rel="stylesheet" href="{{asset('assets/css/colors/switch.css')}}">
        <link href="{{asset('assets/css/colors/color-2.css')}}" rel="alternate stylesheet" type="text/css"
              title="color-2">
        <link href="{{asset('assets/css/colors/color-3.css')}}" rel="alternate stylesheet" type="text/css"
              title="color-3">
        <link href="{{asset('assets/css/colors/color-4.css')}}" rel="alternate stylesheet" type="text/css"
              title="color-4">
        <link href="{{asset('assets/css/colors/color-5.css')}}" rel="alternate stylesheet" type="text/css"
              title="color-5">
        <link href="{{asset('assets/css/colors/color-6.css')}}" rel="alternate stylesheet" type="text/css"
              title="color-6">
        <link href="{{asset('assets/css/colors/color-7.css')}}" rel="alternate stylesheet" type="text/css"
              title="color-7">
        <link href="{{asset('assets/css/colors/color-8.css')}}" rel="alternate stylesheet" type="text/css"
              title="color-8">
        <link href="{{asset('assets/css/colors/color-9.css')}}" rel="alternate stylesheet" type="text/css"
              title="color-9">

        <link href="{{asset('/vendor/unisharp/laravel-ckeditor/plugins/codesnippet/lib/highlight/styles/monokai.css') }}" rel="stylesheet">
        <script src="{{asset('/vendor/unisharp/laravel-ckeditor/plugins/codesnippet/lib/highlight/highlight.pack.js') }}"></script>
        <script>hljs.initHighlightingOnLoad();</script>

        @stack('after-styles')
        @yield('css')
        @if(config('onesignal_status') == 1)
            {!! config('onesignal_data') !!}
        @endif
    @if(config('google_analytics_id') != "")

        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{config('google_analytics_id')}}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', '{{config('google_analytics_id')}}');
        </script>
     @endif

        @if(!empty(config('custom_css')))
            <style>
                {!! config('custom_css')  !!}
            </style>
        @endif

    </head>
    <body class="{{config('layout_type')}}">

    <div id="app">
    {{--<div id="preloader"></div>--}}
    @include('frontend.layouts.modals.loginModal')


    <!-- Start of Header section
    ============================================= -->
        <header class="header_3 gradient-bg">
            <div class="container">
                <div class="navbar-default d-inline-block w-100">
                    <div class="navbar-header float-left">
                        <a class="navbar-brand text-uppercase" href="{{url('/')}}">
                         <img src="{{ asset('assets/img/logo.png') }}" alt="logo">    
                        <img
                                    src="{{asset("storage/logos/".config('logo_white_image'))}}" alt="logo"></a>
                    </div><!-- /.navbar-header -->
                    <div class="header-info ul-li float-right">
                        @php
                            $contact_data = contact_data(config('contact_data'));
                        @endphp
                        <ul>
                            @if($contact_data["primary_email"]["status"] == 1)
                                <li>
                                    <div class="mail-phone">
                                        <div class="info-icon">
                                            <i class="text-gradiant fas fa-envelope"></i>
                                        </div>
                                        <div class="info-content">
                                            <span class="info-id">{{$contact_data["primary_email"]["value"]}}</span>
                                            <span class="info-text">
                                                @lang('labels.frontend.layouts.partials.email_registration')
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @endif
                            @if($contact_data["primary_phone"]["status"] == 1)
                                <li>
                                    <div class="mail-phone">
                                        <div class="info-icon">
                                            <i class="text-gradiant fas fa-phone-square"></i>
                                        </div>
                                        <div class="info-content">
                                            <span class="info-id">{{$contact_data["primary_phone"]["value"]}}</span>
                                            <span class="info-text">
                                                @lang('labels.frontend.layouts.partials.call_us_registration')
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="nav-menu-4">
                        <div class="login-cart-lang float-right ul-li">
                            <ul class="search_cart">
                                <li>
                                    <div class="cart_search">
                                        <a href="{{route('cart.index')}}"><i class="fas fa-shopping-bag"></i>
                                            @if(auth()->check() && Cart::session(auth()->user()->id)->getTotalQuantity() != 0)
                                                <span class="badge badge-danger position-absolute">{{Cart::session(auth()->user()->id)->getTotalQuantity()}}</span>
                                            @endif
                                        </a>
                                    </div>
                                </li>

                            </ul>
                        </div>
                        <!-- Collect the nav links, forms, and other content for toggling -->
                        <nav class="navbar-menu float-left">
                            <div class="nav-menu ul-li">
                                <ul class="quick-menu">
                                    @if(count($custom_menus) > 0 )
                                        @foreach($custom_menus as $menu)
                                            @if($menu['id'] == $menu['parent'])
                                                @if(count($menu->subs) == 0)
                                                    <li class="nav-item">
                                                        <a href="{{asset($menu->link)}}"
                                                           class="nav-link {{ active_class(Active::checkRoute('frontend.user.dashboard')) }}"
                                                           id="menu-{{$menu->id}}">{{trans('custom-menu.'.$menu_name.'.'.str_slug($menu->label))}}</a>
                                                    </li>
                                                @else
                                                    <li class="menu-item-has-children ul-li-block">
                                                        <a href="#!">{{trans('custom-menu.'.$menu_name.'.'.str_slug($menu->label))}}</a>
                                                        <ul class="sub-menu">
                                                            @foreach($menu->subs as $item)
                                                                @include('frontend.layouts.partials.dropdown', $item)
                                                            @endforeach
                                                        </ul>
                                                    </li>
                                                @endif

                                            @endif
                                        @endforeach
                                    @endif

                                    @if(auth()->check())
                                        @if($logged_in_user->hasRole('student'))
                                            <li class="">
                                                <a href="{{ route('admin.dashboard') }}">@lang('navs.frontend.dashboard')</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('frontend.auth.logout') }}"><i class="fas fa-sign-out-alt"></i></a>
                                            </li>
                                        @else
                                        <li class="menu-item-has-children ul-li-block">
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
                                        <li>
                                            <div class="login">
                                                <a data-toggle="modal" data-target="#myModal"
                                                   href="#">@lang('navs.general.login')</a>
                                                {{--@include('frontend.layouts.modals.loginModal')--}}

                                            </div>
                                        </li>
                                    @endif

                                        @if(count($locales) > 1)
                                            <li class="menu-item-has-children ul-li-block">
                                                <a href="#">
                                                    <span class="d-md-down-none">{{ trans('menus.language_picker.language', [], 'en') }}
                                                        ({{ locale_flag_emoji(app()->getLocale()) }} {{ strtoupper(app()->getLocale()) }})</span>
                                                </a>
                                                <ul class="sub-menu">
                                                    @foreach($locales as $lang)
                                                        @if($lang != app()->getLocale())
                                                            <li>
                                                                <a href="{{ route('locale.swap', ['lang' => $lang]) }}"
                                                                   class=""> {{ locale_label($lang) }}</a>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </li>
                                        @endif
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </header>
        <div class="altranative-header ul-li-block">
            <div id="menu-container">

                <div class="menu-wrapper">
                    <div class="row">

                        <button type="button" class="alt-menu-btn float-left">
                            <span class="hamburger-menu"></span>
                        </button>

                        <div class="logo-area">
                            <a href="{{url('/')}}">
                                <img src="{{asset("storage/logos/".config('logo_w_image'))}}" alt="Logo_not_found">
                            </a>
                        </div>

                        <div class="cart-btn pulse  ul-li float-right">
                            <ul>
                                @if(!auth()->check())
                                    <li>
                                        <div class="login">
                                            <a data-toggle="modal" data-target="#myModal" href="#"><i
                                                        class="fa fa-user"></i></a>
                                        </div>
                                    </li>
                                @else
                                    <li class="menu-item-has-children ul-li-block">
                                        <a href="#!"><i class="fa fa-user"></i></a>
                                        <ul class="sub-menu" style="width: auto">
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
                                <li>
                                    <a href="{{route('cart.index')}}"><i class="fas fa-shopping-bag"></i>
                                        @if(auth()->check() && Cart::session(auth()->user()->id)->getTotalQuantity() != 0)
                                            <span class="badge badge-danger position-absolute">{{Cart::session(auth()->user()->id)->getTotalQuantity()}}</span>
                                        @endif
                                    </a>
                                </li>
                            </ul>

                        </div>

                    </div>
                </div>

                <ul class="menu-list accordion" style="left: -100%;">


                    @if(count($custom_menus) > 0 )
                        @foreach($custom_menus as $menu)
                            @if($menu['id'] == $menu['parent'])
                                @if(count($menu->subs) == 0)
                                    <li class="card">
                                        <a href="{{asset($menu->link)}}"
                                           class="menu-link {{ active_class(Active::checkRoute('frontend.user.dashboard')) }}"
                                           id="menu-{{$menu->id}}">{{trans('custom-menu.'.$menu_name.'.'.str_slug($menu->label))}}</a>
                                    </li>
                                @else
                                    <li class="card">
                                        <div class="card-header" id="heading{{$menu->id}}">
                                            <button class="menu-link" data-toggle="collapse"
                                                    data-target="#collapse{{$menu->id}}"
                                                    aria-expanded="true" aria-controls="collapse{{$menu->id}}">
                                                {{trans('custom-menu.'.$menu_name.'.'.str_slug($menu->label))}}
                                            </button>
                                        </div>
                                        <ul id="collapse{{$menu->id}}" class="submenu collapse "
                                            aria-labelledby="heading{{$menu->id}}"
                                            data-parent="#accordion" style="">
                                            @foreach($menu->subs as $item)
                                                @include('frontend.layouts.partials.dropdown2', $item)
                                            @endforeach
                                        </ul>
                                    </li>
                                @endif
                            @endif
                        @endforeach
                    @endif

                        @if(count($locales) > 1)


                            <li class="card">
                                <div class="card-header" id="headingLang">
                                    <button class="menu-link" data-toggle="collapse"
                                            data-target="#collapseLang"
                                            aria-expanded="true" aria-controls="collapseLang">
                                        {{ trans('menus.language_picker.language', [], 'en') }}
                                        ({{ locale_flag_emoji(app()->getLocale()) }} {{ strtoupper(app()->getLocale()) }})
                                    </button>
                                </div>
                                <ul id="collapseLang" class="submenu collapse "
                                    aria-labelledby="headingLang"
                                    data-parent="#accordion" style="">
                                    @foreach($locales as $lang)
                                        @if($lang != app()->getLocale())
                                            <li>
                                                <a href="{{ route('locale.swap', ['lang' => $lang]) }}"
                                                   class=""> {{ locale_label($lang) }}</a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>

                        @endif
                </ul>
            </div>
        </div>
        <!-- Start of Header section
            ============================================= -->


        @yield('content')
        @if(view()->exists('cookieConsent::index'))
            @include('cookieConsent::index')
        @endif
        @include('frontend.layouts.partials.footer')

    </div><!-- #app -->

    <!-- Scripts -->
    @stack('before-scripts')
    <!-- For Js Library -->
    <script src="{{asset('assets/js/jquery-2.1.4.min.js')}}"></script>
    <script src="{{asset('assets/js/popper.min.js')}}"></script>
    <script src="{{asset('assets/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/js/owl.carousel.min.js')}}"></script>
    <script src="{{asset('assets/js/jarallax.js')}}"></script>
    <script src="{{asset('assets/js/jquery.magnific-popup.min.js')}}"></script>
    <script src="{{asset('assets/js/lightbox.js')}}"></script>
    <script src="{{asset('assets/js/jquery.meanmenu.js')}}"></script>
    <script src="{{asset('assets/js/scrollreveal.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.counterup.min.js')}}"></script>
    <script src="{{asset('assets/js/waypoints.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery-ui.js')}}"></script>
    <script src="{{asset('assets/js/gmap3.min.js')}}"></script>
    <script src="{{asset('assets/js/switch.js')}}"></script>
    <script src="{{asset('assets/js/script.js')}}"></script>
    <script>
        @if(request()->has('user')  && (request('user') == 'admin'))

        $('#myModal').modal('show');
        $('#loginForm').find('#email').val('admin@lms.com')
        $('#loginForm').find('#password').val('secret')
        $('#loginForm').find('button').trigger('click');
        @elseif(request()->has('user')  && (request('user') == 'student'))

        $('#myModal').modal('show');
        $('#loginForm').find('#email').val('student@lms.com')
        $('#loginForm').find('#password').val('secret')
        $('#loginForm').find('button').trigger('click');

        @elseif(request()->has('user')  && (request('user') == 'teacher'))

        $('#myModal').modal('show');
        $('#loginForm').find('#email').val('teacher@lms.com')
        $('#loginForm').find('#password').val('secret')
        $('#loginForm').find('button').trigger('click');
        @endif
    </script>
    <script>
        @if((session()->has('show_login')) && (session('show_login') == true))
        $('#myModal').modal('show');
                @endif
        var font_color = "{{config('font_color')}}"
        setActiveStyleSheet(font_color);
    </script>
    @yield('js')
    @stack('after-scripts')

    @include('includes.partials.ga')

    @if(!empty(config('custom_js')))
        <script>
            {!! config('custom_js') !!}
        </script>
    @endif
    </body>
    </html>
