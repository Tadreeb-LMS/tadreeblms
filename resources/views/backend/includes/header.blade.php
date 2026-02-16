<header class="app-header navbar">
    <button class="navbar-toggler sidebar-toggler d-lg-none mr-auto" type="button" data-toggle="sidebar-show">
        <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
        <!-- <img class="navbar-brand-full" src="{{asset('storage/logos/'.config('logo_b_image'))}}"  height="25" alt="Square Logo"> -->
        <img class="navbar-brand-full adminlogo" src="{{ asset('assets/img/logo.png') }}"    alt="Square Logo">
        <img class="navbar-brand-minimized adminlogo" src="{{ asset('assets/img/logo.png') }}"  alt="Square Logo">
        <!-- <img class="navbar-brand-minimized" src="{{asset('storage/logos/'.config('logo_popup'))}}" height="30" alt="Square Logo"> -->
    </a>
    <button class="navbar-toggler sidebar-toggler d-md-down-none" type="button" data-toggle="sidebar-lg-show">
        <span class="navbar-toggler-icon"></span>
    </button>

    <ul class="nav navbar-nav d-md-down-none">
        <li class="nav-item px-3">
            <a class="nav-link" href="{{ route('frontend.index') }}"><i class="icon-home"></i></a>
        </li>

        <li class="nav-item px-3">
            <a class="nav-link" href="{{ route('admin.dashboard') }}">@lang('navs.frontend.dashboard')</a>
        </li>
        {{--@if(config('locale.status') && count(config('locale.languages')) > 1)--}}
            {{--<li class="nav-item px-3 dropdown">--}}
                {{--<a class="nav-link dropdown-toggle nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">--}}
                    {{--<span class="d-md-down-none">@lang('menus.language-picker.language') ({{ strtoupper(app()->getLocale()) }})</span>--}}
                {{--</a>--}}

                {{--@include('includes.partials.lang')--}}
            {{--</li>--}}
        {{--@endif--}}
        @if(config('locale.status') && count($locales) > 1)

            <li class="nav-item px-3 dropdown">
                <a class="nav-link dropdown-toggle nav-link " data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    <span class="d-md-down-none">@lang('menus.language-picker.language') ({{ strtoupper(app()->getLocale()) }})</span>
                </a>

                @include('includes.partials.lang')
            </li>
        @endif
@php           

//echo '<pre>';print_r(Auth::user());
@endphp
@if(Auth::user()->isAdmin() && ((string)Auth::user()->employee_type == ''))
<li class="nav-item px-3 dropdown">
<a class="nav-link dropdown-toggle nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
<span class="d-md-down-none">

@if(null !== (Session::get('setvaluesession')))
@if((Session::get('setvaluesession')) == 1)
@lang('menus.backend.sidebar.general')  
@elseif((Session::get('setvaluesession')) == 2)
@lang('menus.backend.sidebarr.internal')  
@elseif((Session::get('setvaluesession')) == 3)
@lang('menus.backend.sidebar.external') 
@endif
@else
@lang('menus.backend.sidebar.general')
@endif
</span>
</a>
<div class="dropdown-menu dropdown-menu-right add-dropmenu-position" aria-labelledby="navbarDropdownLanguageLink">
<small><a href="{{ route('admin.setvaluesession',1) }}" class="dropdown-item">@lang('menus.backend.sidebar.general')</a></small>
<small><a href="{{ route('admin.setvaluesession',2) }}" class="dropdown-item">@lang('menus.backend.sidebar.internal')</a></small>
<small><a href="{{ route('admin.setvaluesession',3) }}" class="dropdown-item">@lang('menus.backend.sidebar.external')</a></small>
</div>
</li>
@endif




    </ul>

    <ul class="nav navbar-nav ml-auto mr-4">
        <!-- Bell Notification Icon -->
        <li class="nav-item d-md-down-none dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <i class="icon-bell"></i>
                <span class="badge badge-pill d-none badge-danger unreadNotificationCounter"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right notification-dropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
                <div class="dropdown-header text-center d-flex justify-content-between align-items-center">
                    <strong>@lang('navs.general.notifications')</strong>
                    <a href="#" class="mark-all-read-btn text-primary small" style="font-size: 12px;">@lang('navs.general.mark_all_as_read')</a>
                </div>
                <div class="dropdown-divider"></div>
                <div class="unreadNotifications">
                   <p class="mb-0 text-center py-3 text-muted">@lang('navs.general.no_new_notifications')</p>
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-center py-2" href="{{ route('admin.notifications.index') }}">
                    <small>@lang('navs.general.view_all_notifications')</small>
                </a>
            </div>
        </li>

        <!-- Envelope Message Icon -->
        <li class="nav-item d-md-down-none">
            <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <i class="icon-envelope"></i>
                <span class="badge badge-pill d-none badge-success unreadMessageCounter"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-header text-center">
                    <strong>@lang('navs.general.messages')</strong>
                </div>
                <div class="unreadMessages">
                   <p class="mb-0 text-center py-2">@lang('navs.general.no_messages')</p>
                </div>


            </div>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
              @if( $logged_in_user->picture != null)
            <img src="{{ $logged_in_user->picture }}" class="img-avatar" alt="{{ $logged_in_user->email }}">
              @endif
              <span style="right: 0;left: inherit" class="badge d-md-none d-lg-none d-none mob-notification badge-success">!</span>
            <span class="d-md-down-none">{{ $logged_in_user->full_name }}</span>
          </a>
          <div class="dropdown-menu dropdown-menu-right">
            <div class="dropdown-header text-center">
              <strong>@lang('navs.general.account')</strong>
            </div>

            <a class="dropdown-item" href="{{route('admin.messages')}}">
              <i class="fa fa-envelope"></i> @lang('navs.general.messages')
              <span class="badge unreadMessageCounter d-none badge-success">5</span>
            </a>

            <a class="dropdown-item" href="{{ route('admin.account') }}">
              <i class="fa fa-user"></i> @lang('navs.general.profile')
            </a>

            <div class="divider"></div>
            <a class="dropdown-item" href="{{ route('frontend.auth.logout') }}">
                <i class="fas fa-lock"></i> @lang('navs.general.logout')
            </a>
          </div>
        </li>
    </ul>

    {{--<button class="navbar-toggler aside-menu-toggler d-md-down-none" type="button" data-toggle="aside-menu-lg-show">--}}
        {{--<span class="navbar-toggler-icon"></span>--}}
    {{--</button>--}}
    {{--<button class="navbar-toggler aside-menu-toggler d-lg-none" type="button" data-toggle="aside-menu-show">--}}
        {{--<span class="navbar-toggler-icon"></span>--}}
    {{--</button>--}}
</header>
