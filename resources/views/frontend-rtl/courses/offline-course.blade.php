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
                <div class="col-md-9">
                    <div class="offlinetext">
                    @if ($course->grant_certificate && $has_subscribtion == 1 && $course_is_ready == 1)
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
                                        {{-- Attendance is marked --}}
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

                                    @if($course->meeting_join_url)
                                        <a href="javascript:void(0);" class="btn btn-primary btn-block text-white mb-3 text-uppercase font-weight-bold"
                                            onclick="window.open('{{ $course->meeting_join_url }}', '_blank'); window.location.href='{{ route('courses.show', [$course->slug]) }}?joined=1';">
                                            <i class="fa fa-video"></i> @lang('Join')
                                        </a>
                                    @elseif($course->is_online == 'Offline' || $course->is_online == 'Live-Classroom')
                                            @if($is_course_started == true && $is_course_completed == true)    
                                                <a href="{{ route('recordAttendance', ['slug' => $course->slug]) }}"
                                                    class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font">

                                                    @lang('Attend Course')

                                                    <i class="fa fa-arow-right"></i></a> 

                                                    {{-- <span class="alert alert-success">@lang('Will start at :start_at',['start_at'=>$due_date_time])</span>
                                                    <span class="alert alert-success">@lang('Will end at :end_at',['end_at'=>$end_meeting_attend_time]) Now: {{ $now }}</span> --}}
                                            @elseif($is_course_started == false)
                                                <span class="alert alert-success text-sm">@lang('Start at :start_at',['start_at'=>$due_date_time]) <br />
                                                @lang('End at :end_at',['end_at'=>$end_meeting_attend_time]) <br /
                                                >Now: {{ $now }}</span>
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

                                    {{-- @if($course->is_online == 'Live-Classroom')
                                        <a href="{{ route('recordAttendance', ['slug' => $course->slug]) }}"
                                                    class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font">

                                                    @lang('Attend Course')

                                            <i class="fa fa-arow-right"></i>
                                        </a>          

                                    @endif --}}

                                @endif
                            @elseif($course_is_ready == 0)
                                <p class="text text-danger">@lang("course.this_cousse_is_not_ready")</p>
                            @endif

                            
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </section>
@endsection

@push('after-scripts')
@endpush
