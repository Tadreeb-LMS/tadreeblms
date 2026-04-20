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

        <link rel="stylesheet" href="{{asset('assets-rtl/css/owl.carousel.css')}}">
        <link rel="stylesheet" href="{{asset('assets-rtl/css/fontawesome-all.css')}}">
        <link rel="stylesheet" href="{{asset('assets-rtl/css/flaticon.css')}}">
        <link rel="stylesheet" type="text/css" href="{{asset('assets-rtl/css/meanmenu.css')}}">
        <link rel="stylesheet" href="{{asset('assets-rtl/css/bootstrap.min.css')}}">
        <link rel="stylesheet" href="{{asset('assets-rtl/css/video.min.css')}}">
        <link rel="stylesheet" href="{{asset('assets-rtl/css/lightbox.css')}}">
        <link rel="stylesheet" href="{{asset('assets-rtl/css/progess.css')}}">
        <link rel="stylesheet" href="{{asset('assets-rtl/css/animate.min.css')}}">
        <link rel="stylesheet" href="{{asset('assets-rtl/css/slider.css')}}">



        {{--<link rel="stylesheet" href="{{ asset('css/frontend-rtl.css') }}">--}}
        <link rel="stylesheet" href="{{asset('assets-rtl/css/style.css')}}">
        <link rel="stylesheet" href="{{asset('assets-rtl/css/responsive.css')}}">

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

                function gtag() {
                    dataLayer.push(arguments);
                }

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
        <header>
            <div id="main-menu" class="main-menu-container">
                <div class="main-menu">
                    <div class="container">
                        <div class="navbar-default">
                            <div class="navbar-header float-left">
                                <a class="navbar-brand text-uppercase" href="{{url('/')}}"><img
                                            src={{asset("storage/logos/".config('logo_w_image'))}} alt="logo"></a>
                            </div><!-- /.navbar-header -->


                            <div class="cart-search float-right ul-li">
                                <ul>
                                    <li>
                                        <a href="{{route('cart.index')}}"><i class="fas fa-shopping-bag"></i>
                                            @if(auth()->check() && Cart::session(auth()->user()->id)->getTotalQuantity() != 0)
                                                <span class="badge badge-danger position-absolute">{{Cart::session(auth()->user()->id)->getTotalQuantity()}}</span>
                                            @endif
                                        </a>

                                    </li>
                                </ul>
                            </div>

                            <!-- Collect the nav links, forms, and other content for toggling -->
                            <nav class="navbar-menu float-right">
                                <div class="nav-menu ul-li">
                                    <ul>
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
                                                <li>
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
                                            <li class="log-in">
                                                @if(!auth()->check())
                                                    <a id="openLoginModal" data-target="#myModal"
                                                       href="#">@lang('navs.general.login')</a>
                                                    <!-- The Modal -->
                                                    {{--@include('frontend.layouts.modals.loginModal')--}}

                                                @endif
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

                            <div class="mobile-menu">
                                <div class="logo"><a href="{{url('/')}}"><img
                                                src={{asset("storage/logos/".config('logo_w_image'))}} alt="Logo"></a>
                                </div>
                                <nav>
                                    <ul>
                                        @if(count($custom_menus) > 0 )
                                            @foreach($custom_menus as $menu)
                                                @if($menu['id'] == $menu['parent'])
                                                    @if(count($menu->subs) == 0)
                                                        <li class="">
                                                            <a href="{{asset($menu->link)}}"
                                                               class="nav-link {{ active_class(Active::checkRoute('frontend.user.dashboard')) }}"
                                                               id="menu-{{$menu->id}}">{{trans('custom-menu.'.$menu_name.'.'.str_slug($menu->label))}}</a>
                                                        </li>
                                                    @else
                                                        <li class="">
                                                            <a href="#!">{{trans('custom-menu.'.$menu_name.'.'.str_slug($menu->label))}}</a>
                                                            <ul class="">
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
                                            <li class="">
                                                <a href="#!">{{ $logged_in_user->name }}</a>
                                                <ul class="">

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
                                        @else
                                            <li class="">
                                                <a id="openLoginModal" data-target="#myModal"
                                                   href="#">@lang('navs.general.login')</a>
                                                <!-- The Modal -->
                                            </li>
                                        @endif

                                        @if(count($locales) > 1)
                                            <!--li class="menu-item-has-children ul-li-block">
                                                <a href="#">
                                                    <span class="d-md-down-none">{{ trans('menus.language_picker.language', [], 'en') }}
                                                        ({{ strtoupper(app()->getLocale()) }})</span>
                                                </a>
                                                <ul class="">
                                                    @foreach($locales as $lang)
                                                        @if($lang != app()->getLocale())
                                                            <li>
                                                                <a href="{{ route('locale.swap', ['lang' => $lang]) }}"
                                                                   class=""> {{ trans('menus.language_picker.langs.' . $lang, [], 'en') }}</a>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </li-->
                                        @endif
                                    </ul>
                                </nav>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- Start of Header section
            ============================================= -->


        @yield('content')
        @if(view()->exists('cookieConsent::index'))
            @include('cookieConsent::index')
        @endif
        @if(!isset($no_footer))
            @include('frontend-rtl.layouts.partials.footer')
        @endif

    </div><!-- #app -->

    <!-- Scripts -->
    @stack('before-scripts')

    <!-- For Js Library -->
    <!-- For Js Library -->
    <script src="{{asset('assets-rtl/js/jquery-2.1.4.min.js')}}"></script>
    <script src="{{asset('assets-rtl/js/popper.min.js')}}"></script>
    <script src="{{asset('assets-rtl/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('assets-rtl/js/owl.carousel.min.js')}}"></script>
    <script src="{{asset('assets-rtl/js/jarallax.js')}}"></script>
    <script src="{{asset('assets-rtl/js/jquery.magnific-popup.min.js')}}"></script>
    <script src="{{asset('assets-rtl/js/lightbox.js')}}"></script>
    <script src="{{asset('assets-rtl/js/jquery.meanmenu.js')}}"></script>
    <script src="{{asset('assets-rtl/js/scrollreveal.min.js')}}"></script>
    <script src="{{asset('assets-rtl/js/jquery.counterup.min.js')}}"></script>
    <script src="{{asset('assets-rtl/js/waypoints.min.js')}}"></script>
    <script src="{{asset('assets-rtl/js/jquery-ui.js')}}"></script>
    <script src="{{asset('assets-rtl/js/gmap3.min.js')}}"></script>
        <script src="{{asset('assets/js/switch.js')}}"></script>

    <script>
        @if(request()->has('user')  && (request('user') == 'admin'))

        $('#myModal').modal('show');
        $('#loginForm').find('#email').val('admin@lms.com')
        $('#loginForm').find('#password').val('secret')

        @elseif(request()->has('user')  && (request('user') == 'student'))

        $('#myModal').modal('show');
        $('#loginForm').find('#email').val('student@lms.com')
        $('#loginForm').find('#password').val('secret')

        @elseif(request()->has('user')  && (request('user') == 'teacher'))

        $('#myModal').modal('show');
        $('#loginForm').find('#email').val('teacher@lms.com')
        $('#loginForm').find('#password').val('secret')

        @endif
    </script>
    <script src="{{asset('assets-rtl/js/script.js')}}"></script>

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
