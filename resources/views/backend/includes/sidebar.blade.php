@can('category_access')
    @if (
            null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1, 2, 3]))
        )
        <li class="nav-item ">
            <a class="nav-link {{ $request->segment(2) == 'categories' ? 'active' : '' }}"
                href="{{ route('admin.categories.index') }}">
                <i class="nav-icon fas fa-tags"></i>
                <span class="title">@lang('menus.backend.sidebar.categories.title')</span>
            </a>
        </li>
    @endif

    @if (
            null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1, 2]))
        )
        <li class="nav-item ">
            <a class="nav-link {{ $request->segment(2) == 'position' ? 'active' : '' }}"
                href="{{ route('admin.position.index') }}">
                <i class="nav-icon icon-folder-alt"></i>
                <span class="title">@lang('menus.backend.sidebar.position')</span>
            </a>
        </li>

        @if ($logged_in_user->isAdmin())
            <li class="nav-item nav-dropdown {{ in_array($request->segment(2), ['kpis', 'kpi-role-configs']) ? 'open' : '' }}">
                <a class="nav-link nav-dropdown-toggle d-flex align-items-center" href="#">
                    <div>
                        <i class="nav-icon fa fa-bullseye"></i>
                        <span class="title">KPI Management</span>
                    </div>
                    <i class="arrow-icon-new fa fa-chevron-down ml-auto"></i>
                </a>
                <ul class="nav-dropdown-items">
                    <li class="nav-item">
                        <a class="nav-link {{ $request->segment(2) == 'kpis' ? 'active' : '' }}"
                            href="{{ route('admin.kpis.index') }}">
                            <span class="title">All KPIs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $request->segment(2) == 'kpi-role-configs' ? 'active' : '' }}"
                            href="{{ route('admin.kpi-role-configs.index') }}">
                            <span class="title">Role Configurations</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif
    @endif

    @if (
            null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1, 2, 3]))
        )
        {{-- <li class="nav-item ">
            <a class="nav-link {{ $request->segment(2) == 'manual-assessments' ? 'active' : '' }}"
                href="{{ route('admin.manual-assessments.index') }}">
                <i class="nav-icon fas fa-folder"></i>
                <span class="title">@lang('menus.backend.sidebar.manual_assessment')</span>
            </a>
        </li> --}}
    @endif
@endcan

@if (true)
    @if (
            null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1, 2, 3]))
        )

        @can('course_access')
            <li
                class="nav-item nav-dropdown {{ active_class(Active::checkUriPattern(['user/courses*', 'user/lessons*', 'user/tests*', 'user/live-lessons*', 'user/live-lesson-slots*']), 'open') }}">
                <a class="nav-link nav-dropdown-toggle d-flex align-items-center {{ active_class(Active::checkUriPattern('admin/*')) }}"
                    href="#">
                    <div class="d-flex">
                        <i class="nav-icon fas fa-graduation-cap" style="margin-top: 4px;"></i>
                        <div style="margin-left: 5px;">
                            @lang('menus.backend.sidebar.courses.management')
                        </div>
                    </div>
                    <i class="arrow-icon-new fa fa-chevron-down ml-auto"></i>
                </a>
                <ul class="nav-dropdown-items">
                    @can('course_access')
                        <li class="nav-item ">
                            <a class="nav-link {{ $request->segment(2) == 'courses' ? 'active' : '' }}"
                                href="{{ route('admin.courses.index') }}">
                                <span class="title">@lang('menus.backend.sidebar.courses.title')</span>
                            </a>
                        </li>
                    @endcan