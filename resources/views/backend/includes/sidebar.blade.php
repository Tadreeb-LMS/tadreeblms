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
            <li class="nav-item ">
                <a class="nav-link {{ $request->segment(2) == 'teachers' ? 'active' : '' }}"
                    href="{{ route('admin.teachers.index') }}">
                    <i class="nav-icon fa fa-user"></i>
                    <span class="title">@lang('menus.backend.sidebar.trainers')</span>
                </a>
            </li>
            @endcan
            @endif
            @if (null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2,3]))
            )
            @can('trainee_access')
            <li
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
            </li>
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
                <a class="nav-link {{ $request->segment(2) == 'categories' ? 'active' : '' }}"
                    href="{{ route('admin.categories.index') }}">
                    <i class="nav-icon fas fa-tags"></i>
                    <span class="title">@lang('menus.backend.sidebar.categories.title')</span>
                </a>
            </li>
            @endif
            @if (null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2])))
            <li class="nav-item ">
                <a class="nav-link {{ $request->segment(2) == 'department' ? 'active' : '' }}"
                    href="{{ route('admin.department.index') }}">
                    <i class="nav-icon fas fa-building"></i>
                    <span class="title">@lang('menus.backend.sidebar.department')</span>
                </a>
            </li>
            <li class="nav-item ">
                <a class="nav-link {{ $request->segment(2) == 'position' ? 'active' : '' }}"
                    href="{{ route('admin.position.index') }}">
                    <i class="nav-icon icon-folder-alt"></i>
                    <span class="title">@lang('menus.backend.sidebar.position')</span>
                </a>
            </li>
            @endif
            @if (null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2,3])))
            <li class="nav-item ">
                <a class="nav-link {{ $request->segment(2) == 'manual-assessments' ? 'active' : '' }}"
                    href="{{ route('admin.manual-assessments.index') }}">
                    <i class="nav-icon fas fa-folder"></i>
                    <span class="title">@lang('menus.backend.sidebar.Manual-Assessment')</span>
                </a>
            </li>
            @endif
            @endcan
            @if (true)
            @if (null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2,3]))
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
                    @can('lesson_access')
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'lessons' ? 'active' : '' }}"
                            href="{{ route('admin.lessons.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.lessons.title')</span>
                        </a>
                    </li>
                    @endcan

                    @can('question_access')
                    <li class="nav-item">
                        <a class="nav-link {{ $request->segment(2) == 'test_questions' ? 'active' : '' }}"
                            href="{{ route('admin.test_questions.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.questions.title')</span>
                        </a>
                    </li>
                    @endcan
                    @can('test_access')
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'tests' ? 'active' : '' }}"
                            href="{{ route('admin.tests.index') }}">
                            <span class="title">@lang('Tests')</span>
                        </a>
                    </li>
                    @endcan
                    @can('live_lesson_access')

                    @endcan
                    @can('live_lesson_slot_access')

                    @endcan

                    @can('assesment_access')
                    <li class="nav-item " style="display: none">
                        <a class="nav-link {{ $request->segment(2) == 'assessment_accounts' ? 'active' : '' }}"
                            href="{{ route('admin.assessment_accounts.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.assessment_accounts')</span>
                        </a>
                    </li>
                    @endcan
                    @can('assesment_access')
                    <li class="nav-item " style="">
                        <a class="nav-link {{ request()->is('user/assignments') ? 'active' : '' }}" href="{{ url('user/assignments') }}">
                            <span class="title">@lang('menus.backend.sidebar.course_assessment')</span>
                        </a>
                    </li>
                    @endcan
                    @can('assesment_create')
                    <li class="nav-item " style="">
                        <a class="nav-link {{ request()->is('user/assignments/create') ? 'active' : '' }}" href="{{ url('user/assignments/create') }}">
                            <span class="title">@lang('Add Course Assessment')</span>
                        </a>
                    </li>
                    @endcan
                    <li class="nav-item " style="display: none">
                        <a class="nav-link {{ $request->segment(2) == 'assignments' ? 'active' : '' }}"
                            href="{{ route('admin.assessment_accounts.assignments') }}">
                            <span class="title">@lang('menus.backend.sidebar.user_assignments')</span>
                        </a>
                    </li>

                    @can('course_assignment_access')
                    <li class="nav-item ">
                        <a class="nav-link {{ Route::is('admin.assessment_accounts.course-assign-list') ? 'active' : '' }}"
                            href="{{ route('admin.assessment_accounts.course-assign-list') }}">
                            <span class="title">@lang('menus.backend.sidebar.courses_assignments')</span>
                        </a>
                    </li>
                    @endcan
                    @can('course_invitation_access')
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'course-invitation-list' ? 'active' : '' }}"
                            href="{{ route('admin.assessment_accounts.course-invitation-list') }}">
                            <span class="title">@lang('menus.backend.sidebar.Invitations')</span>
                        </a>
                    </li>
                    @endcan
                    @if (in_array(Session::get('setvaluesession'), [3]))

                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'course_assignments' ? 'active' : '' }}"
                            href="{{ url('user/course-requests') }}">
                            <span class="title">@lang('Course Requests')</span>
                        </a>
                    </li>
                    @endif

                </ul>
            </li>
            @endcan
            @endif

            @can('bundle_access')

            @endcan
            
            @endif
            @if (true)
            @can('learning_pathway_access')
            <li
                class="nav-item nav-dropdown {{ active_class(Active::checkUriPattern(['user/learning-pathways', 'user/pathway-assignments', 'user/pathway-assignments/create']), 'open') }}">
                <a class="d-flex align-items-center nav-link nav-dropdown-toggle {{ active_class(Active::checkUriPattern('admin/*')) }}"
                    href="#">
                    <div class="d-flex">

                        <i class="nav-icon fas fa-puzzle-piece" style="margin-top: 5px;"></i>
                        <div style="margin-left: 8px;">
    
                            @lang('menus.backend.sidebar.Learning-Pathways-Management')
                         </div>
                    </div>
                    <i class="arrow-icon-new fa fa-chevron-down"></i>
                </a>
                <ul class="nav-dropdown-items">
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'learning-pathways' ? 'active' : '' }}"
                            href="{{ url('/user/learning-pathways') }}">
                            <span class="title">@lang('menus.backend.sidebar.Learning-Pathways')</span>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'pathway-assignments' ? 'active' : '' }}"
                            href="{{ url('/user/pathway-assignments') }}">
                            <span class="title">@lang('menus.backend.sidebar.Pathway-Assignments')</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endcan
            @endif

            @if (null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1,2,3]))
            )

            @if (true)
            @can('contact_request_access')
            <li class="nav-item ">
                <a class="nav-link {{ $request->segment(2) == 'contact-requests' ? 'active' : '' }}"
                    href="{{ route('admin.contact-requests.index') }}">
                    <i class="nav-icon icon-puzzle"></i>
                    <span class="title">@lang('menus.backend.sidebar.Contact-Requests')</span>
                </a>
            </li>
            @endcan
            @can('employee_request_access')
            <li class="nav-item ">
                <a class="nav-link {{ $request->segment(2) == 'subscription' ? 'active' : '' }}"
                    href="{{ route('admin.subscription.index') }}">
                    <i class="nav-icon icon-puzzle"></i>
                    <span class="title">@lang('menus.backend.sidebar.Employee-Requests')</span>
                </a>
            </li>
            @endcan
            @can('reports_access')
            <li
                class="nav-item nav-dropdown {{ active_class(Active::checkUriPattern(['user/employee*', 'user/external-employee*']), 'open') }}">
                <a class="nav-link nav-dropdown-toggle d-flex align-items-center {{ active_class(Active::checkUriPattern('admin/*')) }}"
                    href="#">
                    <i class="nav-icon fas fa-chart-bar min-icon"></i> <span class="title min-title" style="margin-left: 5px;">
                        @lang('menus.backend.sidebar.all_reports')</span>
                    <i class="arrow-icon-new fa fa-chevron-down ml-auto"></i>
                </a>
                <ul class="nav-dropdown-items">

                    
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'internal_trainee_info' ? 'active' : '' }}"
                            href="{{ route('admin.employee.internal_trainee_info') }}">
                            <span class="title">@lang('menus.backend.sidebar.internal_trainee_info')</span>
                        </a>
                    </li>
                    <li class="nav-item " style="display:none">
                        <a class="nav-link {{ $request->segment(2) == 'reports' ? 'active' : '' }}"
                            href="{{ url('user/internal_reports') }}/82">
                            <span class="title">@lang('menus.backend.sidebar.final_reports')</span>
                        </a>
                    </li>
                   
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'internal-attendence-report' ? 'active' : '' }}"
                            href="{{ route('admin.employee.internal-attendence-report') }}">
                            <span class="title">@lang('menus.backend.sidebar.internal_attendance_reports')</span>
                        </a>
                    </li>
                    
                    
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'external_trainee_info' ? 'active' : '' }}"
                            href="{{ route('admin.employee.external_trainee_info') }}">
                            <span class="title">@lang('menus.backend.sidebar.external_trainee_info')</span>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'external-attendence-report' ? 'active' : '' }}"
                            href="{{ route('admin.employee.external-attendence-report') }}">
                            <span class="title">@lang('menus.backend.sidebar.external_attendance_report')</span>
                        </a>
                    </li>
                   

                </ul>
            </li>
            @endcan
            @endif
            @endif
            @if (null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && in_array(Session::get('setvaluesession'), [1])))

            @if (true)
            @can('site_management_access')
            <li
                class="nav-item nav-dropdown  {{ active_class(Active::checkUriPattern(['user/contact', 'user/sponsors*', 'user/testimonials*', 'user/faqs*', 'user/footer*', 'user/blogs', 'user/sitemap*']), 'open') }}">
                <a class="nav-link nav-dropdown-toggle  d-flex {{ active_class(Active::checkUriPattern('admin/*')) }}"
                    href="#">
                    <i class="nav-icon fas fa-folder mt-1 min-icon"></i> <span class="min-title" style="margin-left: 5px;">

                        @lang('menus.backend.sidebar.site-management.title')
                    </span>
                    <i class="arrow-icon-new fa fa-chevron-down ml-auto"></i>
                    
                </a>

                <ul class="nav-dropdown-items">

                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'subscription' ? 'active' : '' }}"
                            href="{{ route('admin.subscription.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.subscription.title')</span>
                        </a>
                    </li>
                    @can('page_access')
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'pages' ? 'active' : '' }}"
                            href="{{ route('admin.pages.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.pages.title')</span>
                        </a>
                    </li>
                    @endcan
                    @can('blog_access')
                    <li class="nav-item ">
                        <a class="nav-link {{ $request->segment(2) == 'blogs' ? 'active' : '' }}"
                            href="{{ route('admin.blogs.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.blogs.title')</span>
                        </a>
                    </li>
                    @endcan
                    @can('reason_access')
                    <li class="nav-item">
                        <a class="nav-link {{ $request->segment(2) == 'reasons' ? 'active' : '' }}"
                            href="{{ route('admin.reasons.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.reasons.title')</span>
                        </a>
                    </li>
                    @endcan
                    @if ($logged_in_user->isAdmin())

                    

                    <li class="nav-item">
                        <a class="nav-link {{ $request->segment(2) == 'news' ? 'active' : '' }}"
                            href="{{ route('admin.news.index') }}">

                            <span class="title">@lang('menus.backend.sidebar.news_n_update')</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ $request->segment(2) == 'events' ? 'active' : '' }}"
                            href="{{ route('admin.events.index') }}">

                            <span class="title">@lang('menus.backend.sidebar.latest_events')</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ $request->segment(2) == 'libraries' ? 'active' : '' }}"
                            href="{{ route('admin.libraries.index') }}">

                            <span class="title">@lang('menus.backend.sidebar.latest_libraries')</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ $request->segment(2) == 'announcement' ? 'active' : '' }}"
                            href="{{ route('admin.announcement.index') }}">

                            <span class="title">@lang('menus.backend.sidebar.announcement')</span>
                        </a>
                    </li>
                    @endif

                </ul>


            </li>
            @endcan
            @else
            @can('blog_access')
            <li class="nav-item nav-dropdown ">
                <a class="nav-link d-flex align-items-center {{ $request->segment(2) == 'blogs' ? 'active' : '' }}"
                    href="{{ route('admin.blogs.index') }}">
                    <div>

                        <i class="nav-icon icon-note"></i>
                        <span class="title" style="margin-left: 5px;">@lang('menus.backend.sidebar.blogs.title')</span>
                    </div>
                    <i class="arrow-icon-new fa fa-chevron-down ml-auto"></i>
                </a>
            </li>
            @endcan
            @can('reason_access')

            @endcan
            @endif

            @endif


            @if ($logged_in_user->hasRole('student'))

                <li class="nav-item ">
                    <a class="nav-link {{ $request->segment(2) == 'mycourses' ? 'active' : '' }}"
                        href="{{ route('user.mycourses') }}">
                      <i class="nav-icon fas fa-graduation-cap"></i> <span class="title">@lang('menus.backend.sidebar.My-Courses')</span>
                    </a>
                </li>

                {{-- <li class="nav-item ">
                    <a class="nav-link {{ $request->segment(2) == 'mypathwaycourses' ? 'active' : '' }}"
                        href="{{ route('user.mypathwaycourses') }}">
                      <i class="nav-icon fas fa-graduation-cap"></i> <span class="title">@lang('My Pathway Courses')</span>
                    </a>
                </li> --}}


            <li class="nav-item ">
                <a class="nav-link {{ $request->segment(1) == 'certificates' ? 'active' : '' }}"
                    href="{{ route('admin.certificates.index') }}">
                    <i class="nav-icon fas fa-trophy"></i> <span class="title">@lang('menus.backend.sidebar.certificates.title')</span>
                </a>
            </li>
            @endif
            @if (true)
            {{-- <li class="nav-item ">
                <a class="nav-link {{ $request->segment(1) == 'reviews' ? 'active' : '' }}"
                    href="{{ route('admin.reviews.index') }}">
                    <i class="nav-icon icon-speech"></i> <span class="title">@lang('menus.backend.sidebar.reviews.title')</span>
                </a>
            </li> --}}
            @endif

           

            @if ($logged_in_user->hasRole('student'))
          
                <li class="nav-item ">
                    <a class="nav-link {{ $request->segment(1) == 'user.myassignment' ? 'active' : '' }}"
                        href="{{ route('user.myassignment') }}">
                        <i class="nav-icon fas fa-folder"></i>
                        <span class="title">@lang('menus.backend.sidebar.My-Assignments')</span>
                    </a>
                </li>


                <li class="nav-item ">
                    <a class="nav-link {{ $request->segment(1) == 'user.calender' ? 'active' : '' }}"
                        href="{{ route('user.calender') }}">
                        <i class="nav-icon fa fa-calendar"></i>
                        <span class="title">@lang('menus.backend.sidebar.calendar')</span>
                    </a>
                </li>

                <li class="nav-item ">
                    <a class="nav-link {{ $request->segment(1) == 'user.subscriptions' ? 'active' : '' }}"
                        href="{{ route('user.subscriptions') }}">
                        <i class="nav-icon fas fa-credit-card"></i>
                        <span class="title">@lang('menus.backend.sidebar.subscription.title')</span>
                    </a>
                </li>

                <li class="nav-item ">
                    <a class="nav-link {{ $request->segment(1) == 'wishlist' ? 'active' : '' }}"
                        href="{{ route('admin.wishlist.index') }}">
                        <i class="nav-icon fas fa-heart"></i>
                        <span class="title">@lang('Wishlist')</span>
                    </a>
                </li>
            @endif
            @if (true)




            @if (null == Session::get('setvaluesession') ||
            (null !== Session::get('setvaluesession') && Session::get('setvaluesession') == 1))
            @can('access_management_access')
            <li
                class="nav-item nav-dropdown {{ active_class(Active::checkUriPattern('admin/auth*'), 'open') }}">
                <a class="nav-link nav-dropdown-toggle d-flex align-items-center {{ active_class(Active::checkUriPattern('admin/auth*')) }}"
                    href="#">
                    <div>

                        <i class="nav-icon fas fa-shield-alt"></i>
                        <span style="margin-left: 3px;">
    
                            @lang('menus.backend.access.title')
                        </span> 
                    </div>
                    <i class="arrow-icon-new fa fa-chevron-down ml-auto"></i>

                    @if ($pending_approval > 0)
                    <span class="badge badge-danger">{{ $pending_approval }}</span>
                    @endif
                </a>

                <ul class="nav-dropdown-items">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.auth.user.index') ? 'active' : '' }}"
                            href="{{ route('admin.auth.user.index') }}">
                            @lang('labels.backend.access.users.management')

                            @if ($pending_approval > 0)
                            <span class="badge badge-danger">{{ $pending_approval }}</span>
                            @endif
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
                        <a class="nav-link {{ $request->segment(2) == 'footer' ? 'active' : '' }}"
                            href="{{ route('admin.footer-settings') }}">
                            <span class="title">@lang('menus.backend.sidebar.footer.title')</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('admin/menu-manager')) }}"
                            href="{{ route('admin.menu-manager') }}">
                            {{ __('menus.backend.sidebar.menu-manager.title') }}</a>
                    </li>


                    <li class="nav-item ">
                        <a class="nav-link {{ active_class(Active::checkUriPattern('admin/sliders*')) }}"
                            href="{{ route('admin.sliders.index') }}">
                            <span class="title">@lang('menus.backend.sidebar.hero-slider.title')</span>
                        </a>
                    </li>

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