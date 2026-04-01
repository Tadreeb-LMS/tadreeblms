@extends('frontend.layouts.app' . config('theme_layout'))

@push('after-styles')
    <style>
        span.alert.alert-success.text-sm {
            font-size: 15px;
            text-align: left !important;
            padding: 14px !important;
        }
    </style>
@endpush

@section('content')
    <section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
        <div class="blakish-overlay"></div>
        <div class="container">
            <div class="page-breadcrumb-content text-center">
                <div class="page-breadcrumb-title">
                    <h2 class="breadcrumb-head black bold">
                        <span>{{ $course->title }}  </span>
                    </h2>
                </div>
            </div>
        </div>
    </section>
    <section id="course-details" class="course-details-section">
        <div class="container ">
            <div class="offlinecontent">
            <div class="row main-content">

                {{-- ============================================================ --}}
                {{-- SCHEDULED COURSE (daily / weekly / custom)                   --}}
                {{-- ============================================================ --}}
                @if(isset($isScheduledCourse) && $isScheduledCourse)

                @php
                    // Compute time window for today's session (15 min before start → session end)
                    $isWithinTimeWindow = false;
                    $sessionTimeInfo = '';
                    if (isset($todaySession) && $todaySession) {
                        $tsStart = \Carbon\Carbon::parse($todaySession->session_date->format('Y-m-d') . ' ' . $todaySession->session_time);
                        $tsEnd = $tsStart->copy()->addMinutes((int)($todaySession->duration ?? 60));
                        $tsWindowStart = $tsStart->copy()->subMinutes(15);
                        $tsNow = \Carbon\Carbon::now();
                        $isWithinTimeWindow = $tsNow->between($tsWindowStart, $tsEnd);
                        $sessionTimeInfo = $tsStart->format('h:i A') . ' - ' . $tsEnd->format('h:i A');
                    }
                @endphp

                <div class="col-md-9">
                    <div class="offlinetext">
                        @if ($course->grant_certificate && $has_subscribtion == 1 && $course_is_ready == 1 && isset($isGrantCertificate) && $isGrantCertificate)
                            <div>
                                <h5>{{ trans('course.welcome_title',['name'=>auth()->user()->full_name]) }}</h5>
                                <h4>{!! $course->description !!}<br>{{ trans('course.you_are_qualified_to_this_course') }}</h4>
                            </div>
                        @elseif($course_is_ready == 1)
                            <div>
                                <h5>{{ trans('course.welcome_title',['name'=>auth()->user()->full_name]) }},</h5>
                                <h4>
                                    @if($is_attended)
                                        {!! trans('course.your_attendance_taken',['course'=>$course->title]) !!}
                                    @else
                                        You are enrolled in <b>{{ $course->title }}</b>.
                                        This course has <b>{{ $totalSessionsCount }}</b> sessions.
                                        You have attended <b>{{ $attendedSessionsCount }}</b> so far.
                                    @endif
                                </h4>
                            </div>
                        @elseif($course_is_ready == 0)
                            <div>
                                <h5>{{ trans('course.welcome_title',['name'=>auth()->user()->full_name]) }}</h5>
                                <h4>{!! $course->description !!}<br>{{ trans('course.this_cousse_is_not_ready') }}</h4>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-3">
                    <div id="sidebar">
                        <div class="course-details-category ul-li">
                            @if($course_is_ready == 1)
                                @if($is_attended)
                                    {{-- Post-attendance flow: assessment / feedback / certificate --}}
                                    @if ($nextTasks['open_assesment'])
                                        <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                            target="_blank" href="{{ htmlspecialchars_decode($assessment_link) }}">
                                            {{ trans('course.btn.start_assesment') }}
                                        </a>
                                    @endif
                                    @if ($nextTasks['open_feedback'])
                                        <p class="text text-success">@lang("course.give_feedback_to_download_certificate")</p>
                                        <a class="btn btn-info btn-block text-white mb-3"
                                            href="{{ route('course-feedback',$course->id) }}">{{ trans('course.btn.give_feedback') }}</a>
                                    @endif
                                    @if ($nextTasks['download_certificate'])
                                        <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                            href="{{ route('admin.certificates.generate', ['course_id' => $course->id, 'user_id' => auth()->id()]) }}">
                                            {{ trans('course.btn.download_certificate') }}
                                        </a>
                                        <div class="alert alert-success">@lang('labels.frontend.course.certified')</div>
                                    @endif
                                    @if ($nextTasks['reattempt_assesment'])
                                        <p class="text text-danger">@lang("Sorry! you didn't qualify the assignment. So certificate could not be issued.")</p>
                                        @if ($assessment_link)
                                            <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                                target="_blank" href="{{ htmlspecialchars_decode($assessment_link) }}">{{ trans('course.btn.re_attempt_assigment') }}</a>
                                        @endif
                                    @endif
                                @else
                                    {{-- Not all sessions done yet --}}
                                    @if(isset($todaySession) && $todaySession && ($todaySession->meeting_link || $todaySession->host_url) && $isWithinTimeWindow)
                                        @php
                                            $topUrl = (isset($isHostRole) && $isHostRole)
                                                ? ($todaySession->host_url ?: $todaySession->meeting_link)
                                                : $todaySession->meeting_link;
                                        @endphp
                                        <a href="{{ $topUrl }}" target="_blank" class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                            onclick="setTimeout(function(){ window.location.href='{{ route('courses.show', [$course->slug]) }}?joined=1&session_id={{ $todaySession->id }}'; }, 500);">
                                            <i class="fa fa-video"></i> {{ (isset($isHostRole) && $isHostRole) ? __('Host Meeting') : __('Join Today\'s Session') }}
                                        </a>
                                    @elseif(isset($todaySession) && $todaySession && !$isWithinTimeWindow)
                                        <div class="alert alert-warning text-center mb-3">
                                            <strong>Today's Session</strong><br>
                                            {{ $sessionTimeInfo }}<br>
                                            <small class="text-muted">Join opens 15 min before start</small>
                                        </div>
                                    @elseif(isset($nextSession) && $nextSession)
                                        <div class="alert alert-info text-center">
                                            <strong>Next Session</strong><br>
                                            {{ \Carbon\Carbon::parse($nextSession->session_date)->format('D, d M Y') }}<br>
                                            {{ \Carbon\Carbon::parse($nextSession->session_time)->format('h:i A') }}
                                        </div>
                                    @endif
                                @endif
                            @elseif($course_is_ready == 0)
                                <p class="text text-danger">@lang("course.this_cousse_is_not_ready")</p>
                            @endif
                        </div>
                    </div>
                </div>

                </div>{{-- /row --}}

                {{-- Today's Session Banner --}}
                @if(!$is_attended && isset($todaySession) && $todaySession)
                    @php
                        $sessionUrl = (isset($isHostRole) && $isHostRole)
                            ? ($todaySession->host_url ?: $todaySession->meeting_link)
                            : $todaySession->meeting_link;
                        $sessionStart = \Carbon\Carbon::parse($todaySession->session_date->format('Y-m-d') . ' ' . $todaySession->session_time);
                        $sessionEnd = $sessionStart->copy()->addMinutes((int)($todaySession->duration ?? 60));
                    @endphp
                    @if($sessionUrl)
                        <div class="alert {{ $isWithinTimeWindow ? 'alert-info' : 'alert-secondary' }} d-flex justify-content-between align-items-center flex-wrap mt-3" style="border-radius: 8px;">
                            <div>
                                <strong><i class="fas fa-video"></i> Today's Live Session</strong>
                                <div class="text-muted mt-1">
                                    <i class="fas fa-clock"></i> {{ $sessionStart->format('h:i A') }} - {{ $sessionEnd->format('h:i A') }}
                                    @if($todaySession->duration)
                                        &nbsp;&nbsp;<i class="fas fa-hourglass-half"></i> {{ $todaySession->duration }} min
                                    @endif
                                    @if($todaySession->provider)
                                        &nbsp;&nbsp;<span class="badge badge-primary">{{ ucfirst($todaySession->provider) }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($isWithinTimeWindow)
                                <a href="{{ $sessionUrl }}" target="_blank" class="btn btn-success text-white font-weight-bold mt-2"
                                    onclick="setTimeout(function(){ window.location.href='{{ route('courses.show', [$course->slug]) }}?joined=1&session_id={{ $todaySession->id }}'; }, 500);">
                                    <i class="fas fa-sign-in-alt"></i> {{ (isset($isHostRole) && $isHostRole) ? 'Host Meeting' : 'Join Now' }}
                                </a>
                            @else
                                <span class="badge badge-warning mt-2" style="font-size: 13px;">Join opens 15 min before start</span>
                            @endif
                        </div>
                    @endif
                @endif

                {{-- All Sessions Table --}}
                @if(isset($allSessions) && $allSessions->count() > 0)
                    <div class="mt-4 mb-4">
                        <h5><i class="fas fa-calendar-alt"></i> Sessions Schedule ({{ $attendedSessionsCount }}/{{ $totalSessionsCount }} attended)</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mt-2">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allSessions as $idx => $session)
                                        @php
                                            $sStart = \Carbon\Carbon::parse($session->session_date->format('Y-m-d') . ' ' . $session->session_time);
                                            $sEnd = $sStart->copy()->addMinutes((int)($session->duration ?? 60));
                                            $sWindowStart = $sStart->copy()->subMinutes(15);
                                            $sUrl = (isset($isHostRole) && $isHostRole)
                                                ? ($session->host_url ?: $session->meeting_link)
                                                : $session->meeting_link;
                                            $isToday = $session->session_date->isToday();
                                            $isPast = $session->session_date->isPast() && !$isToday;
                                            $wasAttended = isset($attendedSessionIds) && in_array($session->id, $attendedSessionIds);
                                            $rowInWindow = $isToday && \Carbon\Carbon::now()->between($sWindowStart, $sEnd);
                                        @endphp
                                        <tr @if($isToday) style="background: #e8f5e9; font-weight: 600;" @elseif($isPast && !$wasAttended) style="background: #fce4ec;" @elseif($wasAttended) style="background: #f1f8e9;" @endif>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>{{ $sStart->format('D, d M Y') }} @if($isToday) <span class="badge badge-success">Today</span> @endif</td>
                                            <td>{{ $sStart->format('h:i A') }}</td>
                                            <td>{{ $session->duration ?? '-' }} min</td>
                                            <td>
                                                @if($wasAttended)
                                                    <span class="badge badge-success">Attended</span>
                                                @elseif($isPast)
                                                    <span class="badge badge-danger">Missed</span>
                                                @elseif($isToday)
                                                    <span class="badge badge-warning">Today</span>
                                                @else
                                                    <span class="badge badge-secondary">Upcoming</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($sUrl && $rowInWindow && !$is_attended && !$wasAttended)
                                                    <a href="{{ $sUrl }}" target="_blank" class="btn btn-sm btn-success text-white"
                                                        onclick="setTimeout(function(){ window.location.href='{{ route('courses.show', [$course->slug]) }}?joined=1&session_id={{ $session->id }}'; }, 500);">
                                                        <i class="fas fa-sign-in-alt"></i> {{ (isset($isHostRole) && $isHostRole) ? 'Host' : 'Join' }}
                                                    </a>
                                                @elseif($isToday && !$rowInWindow && !$wasAttended)
                                                    <span class="text-muted small">{{ $sStart->format('h:i A') }}</span>
                                                @elseif($wasAttended)
                                                    <i class="fas fa-check text-success"></i>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- ============================================================ --}}
                {{-- SINGLE MEETING COURSE (existing flow — no changes)           --}}
                {{-- ============================================================ --}}
                @else

                <div class="col-md-9">
                    <div class="offlinetext">
                    @if ($course->grant_certificate && $has_subscribtion == 1 && $course_is_ready == 1 && isset($isGrantCertificate) && $isGrantCertificate)
                        <div class="">
                            <h5>{{ trans('course.welcome_title',['name'=>auth()->user()->full_name]) }} </h5>
                            <h4>
                                {!! $course->description !!}
                                <br>
                                {{ trans('course.you_are_qualified_to_this_course') }}</h4>
                            </h4>
                        </div>
                    @elseif($course_is_ready == 1)
                        @if (
                            @$isAssignmentTaken &&
                                $course->courseAssignments->count() > 0 &&
                                $course->assignmentStatus(auth()->id()) == 'Failed' && !$assessment_link)
                            <h5>{{ trans('course.welcome_title',['name'=>auth()->user()->full_name]) }},</h5>
                            <h4>
                                {{ trans('course.sorry_you_failed_to_qualify') }}</h4>
                        @elseif (
                            @$isAssignmentTaken &&
                            $course->courseAssignments->count() > 0 &&
                            $course->assignmentStatus(auth()->id()) == 'Failed' && $assessment_link)
                            <h5>{{ trans('course.welcome_title',['name'=>auth()->user()->full_name]) }},</h5>
                            <h4>
                                {{ trans('course.sorry_you_failed_to_qualify_please_try_again') }}</h4>

                        @else
                            <div class="">
                                <h5>{{ trans('course.welcome_title',['name'=>auth()->user()->full_name]) }},</h5>
                                <h4>
                                    @if($is_attended)
                                        {!! trans('course.your_attendance_taken',['course'=>$course->title])  !!}
                                    @elseif($is_course_started == false)
                                        {!! trans('course.is_offline_course',['course'=>$course->title])  !!}
                                    @elseif($is_course_started == true && $is_course_completed == true)
                                        {!! trans('course.will_be_attendance_taken',['course'=>$course->title])  !!}
                                    @else
                                        {!! trans('course.is_offline_course',['course'=>$course->title])  !!}
                                    @endif
                                </h4>
                            </div>
                        @endif
                    @elseif($course_is_ready == 0)
                            <div class="">
                                <h5>{{ trans('course.welcome_title',['name'=>auth()->user()->full_name]) }} </h5>
                                <h4>
                                    {!! $course->description !!}
                                    <br>
                                    {{ trans('course.this_cousse_is_not_ready') }}</h4>
                                </h4>
                            </div>
                    @endif
                </div>
                </div>
                <div class="col-md-3">
                    <div id="sidebar">
                        <div class="course-details-category ul-li">

                            @if($course_is_ready == 1)
                                @if($is_attended)

                                    @if ($nextTasks['open_assesment'])
                                        <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                            target="_blank" href="{{ htmlspecialchars_decode($assessment_link) }}">
                                            {{ trans('course.btn.start_assesment') }}
                                        </a>
                                    @endif

                                    @if ($nextTasks['open_feedback'])
                                        <p class="text text-success">@lang("course.give_feedback_to_download_certificate")</p>
                                        <a class="btn btn-info btn-block text-white mb-3"
                                        href="{{ route('course-feedback',$course->id) }}">{{ trans('course.btn.give_feedback') }}</a>
                                    @endif
                                    @if ($nextTasks['download_certificate'])
                                        <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                            href="{{ route('admin.certificates.generate', ['course_id' => $course->id, 'user_id' => auth()->id()]) }}">
                                            {{ trans('course.btn.download_certificate') }}
                                        </a>
                                        <div class="alert alert-success">
                                            @lang('labels.frontend.course.certified')
                                        </div>
                                    @endif
                                    @if ($nextTasks['reattempt_assesment'])
                                        <p class="text text-danger">@lang("Sorry! you didn't qualify the assignment. So certificate could not be issued.")</p>
                                        @if ($assessment_link)
                                            <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                                target="_blank" href="{{ htmlspecialchars_decode($assessment_link) }}">{{ trans('course.btn.re_attempt_assigment') }}</a>
                                        @endif
                                    @endif

                                @else

                                    @php
                                        // For scheduled courses, use today's session link; for single-meeting, use course-level link
                                        $sidebarHostUrl = null;
                                        $sidebarJoinUrl = null;
                                        if (isset($todaySession) && $todaySession) {
                                            $sidebarHostUrl = $todaySession->host_url;
                                            $sidebarJoinUrl = $todaySession->meeting_link;
                                        }
                                        if (!$sidebarHostUrl) $sidebarHostUrl = $course->meeting_host_url;
                                        if (!$sidebarJoinUrl) $sidebarJoinUrl = $course->meeting_join_url;
                                    @endphp
                                    @if($sidebarHostUrl && (auth()->user()->isAdmin() || auth()->user()->hasRole('teacher')))
                                        <a href="{{ $sidebarHostUrl }}" target="_blank" class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold">
                                            <i class="fa fa-video"></i> @lang('Host Meeting')
                                        </a>
                                    @elseif($sidebarJoinUrl)
                                        <a href="{{ $sidebarJoinUrl }}" target="_blank" class="btn btn-primary btn-block text-white mb-3 text-uppercase font-weight-bold"
                                            onclick="setTimeout(function(){ window.location.href='{{ route('courses.show', [$course->slug]) }}?joined=1'; }, 500);">
                                            <i class="fa fa-video"></i> @lang('Join')
                                        </a>
                                    @elseif($course->is_online == 'Offline' || $course->is_online == 'Live-Classroom')
                                            @if($is_course_started == true && $is_course_completed == true)
                                                <a href="{{ route('recordAttendance', ['slug' => $course->slug]) }}"
                                                    class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font">
                                                    @lang('Attend Course')
                                                    <i class="fa fa-arow-right"></i></a>
                                            @elseif($is_course_started == false)
                                                <span class="alert alert-success text-sm">@lang('Start at :start_at',['start_at'=>$due_date_time]) <br />
                                                @lang('End at :end_at',['end_at'=>$end_meeting_attend_time]) <br />Now: {{ $now }}</span>
                                            @elseif($is_course_started == true && $is_course_completed == false)
                                                @if($first_lesson_slug)
                                                    <a href="{{route('lessons.show',['course_id' => $course->id,'slug' => $first_lesson_slug])}}"
                                                        class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font">
                                                        @lang('labels.frontend.course.continue_course')
                                                        <i class="fa fa-arow-right"></i>
                                                    </a>
                                                @else
                                                    <span class="alert alert-info">@lang('No lessons available for this course.')</span>
                                                @endif
                                            @endif
                                    @endif

                                @endif
                            @elseif($course_is_ready == 0)
                                <p class="text text-danger">@lang("course.this_cousse_is_not_ready")</p>
                            @endif

                        </div>
                    </div>
                </div>
            </div>{{-- /row --}}

            @endif
            </div>
        </div>
    </section>
@endsection

@push('after-scripts')
@endpush
