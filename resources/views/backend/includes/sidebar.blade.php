@inject('request', 'Illuminate\Http\Request')
@push('after-styles')
<style>



</style>
@endpush

<div class="sidebar min-sidebar <?php echo $logged_in_user->isAdmin() ? 'adminactive' : ''; ?>" style="background-color:#fff"> 
    
    <nav class="sidebar-nav">
        <ul class="nav">
            <li class="nav-title">
                @lang('menus.backend.sidebar.general')
            </li>
            <li class="nav-item ">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} "
                    href="{{ route('admin.dashboard') }}">
                    <i class="nav-icon icon-speedometer"></i>
                    <span class="title"> @lang('menus.backend.sidebar.dashboard')</span>
                </a>
            </li>


            <!--=======================Custom menus===============================-->
            @can('order_access')

            @endcan

            @if (true)
            @if (null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2,3]))
            )

            @can('trainer_access')
            <!-- <li class="nav-item ">
                <a class="nav-link {{ $request->segment(2) == 'teachers' ? 'active' : '' }}"
                    href="{{ route('admin.teachers.index') }}">
                    <i class="nav-icon fa fa-user"></i>
                    <span class="title">@lang('menus.backend.sidebar.trainers')</span>
                </a>
            </li> -->
            @endcan
            @endif
            @if (null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2,3]))
            )
            @can('trainee_access')
            <!-- <li
                class="nav-item nav-dropdown   {{ active_class(Active::checkUriPattern(['user/employee*', 'user/external-employee*']), 'open') }}">
                <a class="nav-link nav-dropdown-toggle d-flex  {{ active_class(Active::checkUriPattern('admin/*')) }}"
                    href="#">
                    <div>
                        <i class="nav-icon fa fa-users"></i> <span class="title ">@lang('menus.backend.sidebar.trainees')</span>
                    </div>
                    <i class="arrow-icon-new fa fa-chevron-down ml-auto"></i>
                </a>
                <ul class="nav-dropdown-items">
                    @can('course_access')
                    @if (null == Session::get('setvaluesession') ||
                    (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2]))
                    )
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'employee' ? 'active' : '' }}"
                            href="{{ route('admin.employee.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.internal')</span>
                        </a>
                    </li>
                    @endif
                    @endcan
                    @can('lesson_access')
                    @if (null == Session::get('setvaluesession') ||
                    (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,3]))
                    )
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'external-employee' ? 'active' : '' }}"
                            href="{{ route('admin.employee.external_index') }}">
                            <span class="title">@lang('menus.backend.sidebar.external')</span>
                        </a>
                    </li>
                    @endif
                    @endcan
                </ul>
            </li> -->
            @endcan
            @endif

            @if (null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2]))
            )
            @can('feedback_access')
            <li
                class="nav-item nav-dropdown {{ active_class(Active::checkUriPattern(['user/employee*', 'user/external-employee*']), 'open') }}">
                <a class="nav-link nav-dropdown-toggle d-flex {{ active_class(Active::checkUriPattern('admin/*')) }}"
                    href="#">
                    <div>

                        <i class="nav-icon fas fa-comments"></i> <span class="title">@lang('menus.backend.sidebar.feedback')</span>
                    </div>
                    <i class="arrow-icon-new fa fa-chevron-down ml-auto"></i>
                </a>
                <ul class="nav-dropdown-items">
                    @can('course_access')
                    <li class="nav-item ">
                        <a class="nav-link {{ request()->routeIs('admin.feedback_question.index') ? 'active' : '' }}"
                            href="{{ route('admin.feedback_question.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.questions.title')</span>
                        </a>
                    </li>
                    @endcan
                    @can('lesson_access')
                    <li class="nav-item ">
                        <a class="nav-link {{ request()->routeIs('admin.feedback.create_course_feedback') ? 'active' : '' }}"
                            href="{{ route('admin.feedback.create_course_feedback') }}">
                            <span class="title">@lang('menus.backend.sidebar.course_questions')</span>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link {{ request()->routeIs('admin.course-feedback-questions.index') ? 'active' : '' }}"
                            href="{{ route('admin.course-feedback-questions.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.course_feedback_questions')</span>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link {{ request()->routeIs('admin.user-feedback-answers.index') ? 'active' : '' }}"
                            href="{{ route('admin.user-feedback-answers.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.user_feedback_answers')</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcan
            @endif
            @if (null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2])))
            @can('calender_access')
            <li class="nav-item ">
                <a class="nav-link {{ request()->routeIs('user.calender') ? 'active' : '' }}"
                    href="{{ route('user.calender') }}">
                    <i class="nav-icon fa fa-calendar-alt"></i>

                    <span class="title">@lang('menus.backend.sidebar.calendar')</span>
                </a>
            </li>
            @endcan
            @endif


            @endif

            @can('category_access')

            @if (null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2,3]))
            )
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'department' ? 'active' : '' }}"
                            href="{{ route('admin.department.index') }}">
                            <i class="nav-icon fas fa-building"></i>
                            <span class="title">@lang('menus.backend.sidebar.department')</span>
                        </a>
                    </li>
                    @if (null == Session::get('setvaluesession') ||
                    (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2])))
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'position' ? 'active' : '' }}"
                            href="{{ route('admin.position.index') }}">
                            <i class="nav-icon icon-folder-alt"></i>
                            <span class="title">@lang('menus.backend.sidebar.position')</span>
                        </a>
                    </li>
                    @endif
                    @if (null == Session::get('setvaluesession') ||
                    (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2,3]))
                    )
                    @can('trainer_access')
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'teachers' ? 'active' : '' }}"
                            href="{{ route('admin.teachers.index') }}">
                            <!-- <i class="nav-icon fa fa-user"></i> -->
                            <span class="title">@lang('menus.backend.sidebar.trainers')</span>
                        </a>
                    </li>
                    @endcan
                    @endif
                    @can('course_access')
                    @if (null == Session::get('setvaluesession') ||
                    (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2]))
                    )
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'employee' ? 'active' : '' }}"
                            href="{{ route('admin.employee.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.trainees')</span>
                        </a>
                    </li>
                    @endif
                    @endcan
                    @endif
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.ldap-user-listing') ? 'active' : '' }}"
                            href="{{ route('admin.ldap-user-listing') }}">
                            @lang('LDAP User List ')
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ $request->segment(2) == 'roles' ? 'active' : '' }}"
                            href="{{ route('admin.roles.index') }}">

                            <span class="title">@lang('menus.backend.sidebar.roles_mgt')</span>
                        </a>
                    </li>

                </ul>
            </li>
            @endcan
            @endif

            <!--==================================================================-->
            <li class="divider"></li>

            @if (null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && Session::get('setvaluesession') == 1))
            @can('settings_access')
            <li class="nav-item nav-dropdown {{ active_class(Active::checkUriPattern('admin/*'), 'open') }}">
                <a class="nav-link nav-dropdown-toggle d-flex align-items-center {{ active_class(Active::checkUriPattern('admin/settings*')) }}"
                    href="#">
                    <div>

                        <i class="nav-icon fas fa-cog"></i> 
                        <span style="margin-left: 3px;">
    
                            @lang('menus.backend.sidebar.settings.title')
                        </span>
                    </div>
                    <i class="arrow-icon-new fa fa-chevron-down ml-auto"></i>
                </a>

                <ul class="nav-dropdown-items">
                    <li class="nav-item">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('admin/settings')) }}"
                            href="{{ route('admin.general-settings') }}">
                            @lang('menus.backend.sidebar.settings.general')
                        </a>
                    </li>

                    <li class="nav-item ">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('admin/landing-page-setting')) }}"
                            href="{{ route('admin.landing-page-setting') }}">
                            <span class="title">@lang('menus.backend.sidebar.settings.landing_page_setting')</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('admin/settings/notifications*')) }}"
                            href="{{ route('admin.notification-settings') }}">
                            <span class="title">@lang('menus.backend.sidebar.notification-settings')</span>
                        </a>
                    </li>


                    <li class="nav-item">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('user/settings/smtp*')) }}"
                            href="{{ route('admin.smtp-settings') }}">
                            <span class="title">@lang('menus.backend.sidebar.settings.smtp')</span>
                        </a>
                    </li>

                    {{-- Show external module configurations only if enabled --}}
                    @php
                        $enabledApps = Cache::get('enabled_external_apps', []);
                    @endphp
                    @if (!empty($enabledApps['zoom']) && $enabledApps['zoom'])
                    <li class="nav-item">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('admin/external-apps/zoom/configure')) }}"
                            href="{{ route('admin.external-apps.edit-config', ['slug' => 'zoom']) }}">
                            <span class="title">Zoom Configuration</span>
                        </a>
                    </li>
                    @endif
                    @if (!empty($enabledApps['external-storage']) && $enabledApps['external-storage'])
                    <li class="nav-item">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('admin/s3-storage-settings*')) }}"
                            href="{{ route('admin.s3-storage-settings') }}">
                            <span class="title"><i class="fas fa-cloud mr-1"></i>S3 Storage Settings</span>
                        </a>
                    </li>
                    @endif

                    @if (!empty($enabledApps['teams']) && $enabledApps['teams'])
                    <li class="nav-item">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('admin/external-apps/teams/configure')) }}"
                            href="{{ route('admin.external-apps.edit-config', ['slug' => 'teams']) }}">
                            <span class="title">Microsoft Teams Configuration</span>
                        </a>
                    </li>
                    @endif

                    <li class="nav-item ">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('admin/ldap-setting')) }}"
                            href="{{ route('admin.ldap-setting') }}">
                            <span class="title">@lang('LDAP Setting')</span>
                        </a>
                    </li>

                    @if ($logged_in_user->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('user/settings/license*')) }}"
                            href="{{ route('admin.license-settings') }}">
                            <span class="title">@lang('menus.backend.sidebar.settings.license')</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('admin/external-apps*')) }}"
                            href="{{ route('admin.external-apps.index') }}">
                            <span class="title"><i class="fas fa-puzzle-piece mr-1"></i>External Apps</span>
                        </a>
                    </li>
                    @endif
                    {{-- <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'footer' ? 'active' : '' }}"
                            href="{{ route('admin.footer-settings') }}">
                            <span class="title">@lang('menus.backend.sidebar.footer.title')</span>
                        </a>
                    </li> --}}

                    {{-- <li class="nav-item">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('admin/menu-manager')) }}"
                            href="{{ route('admin.menu-manager') }}">
                            {{ __('menus.backend.sidebar.menu-manager.title') }}</a>
                    </li> --}}


                    {{-- <li class="nav-item ">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('admin/sliders*')) }}"
                            href="{{ route('admin.sliders.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.hero-slider.title')</span>
                        </a>
                    </li> --}}

                </ul>
            </li>
            
            @endcan
            @endif
            @if (true)
            @can('send_email_notification_access')
            <li class="nav-item ">
                <a class="d-flex nav-link {{ $request->segment(2) == 'send-email-notification' ? 'active' : '' }}"
                    href="{{ url('/user/send-email-notification') }}">
                    <i class="nav-icon fas fa-envelope min-icon" style="margin-top: 5px;"></i>
                    <div class="title ml-1 min-title">@lang('menus.backend.sidebar.Send-Email-Notification')</div>
                </a>
            </li>
            @endcan
            @endif

            @endif

            @if ($logged_in_user->hasRole('teacher'))

            @endif

        </ul>
    </nav>

    <button class="sidebar-minimizer brand-minimizer" type="button"></button>
</div>
@push('after-scripts')
<script>
   
    $(document).ready(function() {
        $('.sidebar .nav-link').css({
            'color': '#333',
            // 'background-color': 'transparent',
            'font-weight': '500',
            // 'padding': '0.75rem 1rem',
            'transition': 'all 0.3s ease'
        });
        $('.sidebar .nav-dropdown-items').css({
            'background-color': '#fff',
            'padding-left': '10px',

        });
        $('.sidebar .nav-dropdown-items .nav-link.active').css({

            'padding-left': '17px',

        });

        $('.sidebar .nav-item .nav-link.active').css({
            'background-color': '#dde6f5 ',
            'color': '#3c4085',
            'font-weight': '500',
            'border-radius': '6px'
        });
        $('.sidebar .nav-link.active .nav-icon').css({

            'color': '#3c4085',
            'font-weight': '500',
            'border-radius': '6px'
        });
        $('.sidebar .nav-link').hover(
            function() {
                if (!$(this).hasClass('active')) {
                    $(this).addClass('hover-active');
                     $(this).find('.nav-icon').css('color', '#3c4085');
                     
                }
            },
            function() {
                if (!$(this).hasClass('active')) {
                    $(this).removeClass('hover-active');
                     $(this).find('.nav-icon').css('color', '');
                }
            }


        );
        // This is only needed if you want different styles when minimized
        // if ($('body').hasClass('sidebar-minimized')) {
        //     $('.sidebar-minimized .sidebar .nav-dropdown-items .nav-item .nav-link').css({
        //         'font-size': '14px',
                

        //     });

        //     $('.sidebar-minimized .sidebar .nav-dropdown-items .nav-item .nav-link.active').css({
        //         // 'color': 'red',
        //         // 'background-color': 'red'
        //     });
        // }
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.nav-dropdown-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const parent = this.closest('.nav-dropdown');

            // Close all other dropdowns
            document.querySelectorAll('.nav-dropdown.open').forEach(function (openItem) {
                if (openItem !== parent) openItem.classList.remove('open');
            });

            // Toggle the clicked one
            parent.classList.toggle('open');
        });
    });
});

</script>
@endpush
<!--sidebar-->