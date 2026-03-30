@extends('frontend.layouts.app' . config('theme_layout'))
<?php
use App\Models\Lesson;
?>

@section('title', $course->meta_title ? $course->meta_title : app_name())
@section('meta_description', $course->meta_description)
@section('meta_keywords', $course->meta_keywords)
<?php
$subscribe_status = CustomHelper::courseStatus($course->id);
//dd($subscribe_status);
?>
@push('after-styles')
    <style>
        .leanth-course.go {
            right: 0;
        }

        .video-container iframe {
            max-width: 100%;
        }

        .modal-dialog {
            max-width: 50rem;
            margin: 1.75rem auto;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.5.3/plyr.css" />
@endpush

@section('content')

    <!-- Start of breadcrumb section
                            ============================================= -->
    <section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
        <div class="blakish-overlay"></div>
        <div class="container">
            <div class="page-breadcrumb-content text-center">
                <div class="page-breadcrumb-title">
                    <h2 class="breadcrumb-head black bold"><span>{{ $course->title }} </span></h2>
                </div>
            </div>
        </div>
    </section>
    <!-- End of breadcrumb section
                            ============================================= -->

    <!-- Start of course details section
                            ============================================= -->
    <section id="course-details" class="course-details-section">
        <div class="container">
            <div class="row">
                <div class="col-md-9">
                    <div class="course-details-item border-bottom-0 mb-0">
                        <div class="course-single-pic mb30">
                            @if ($course->course_image != '')
                                <img src="{{ asset('storage/uploads/' . $course->course_image) }}" alt="">
                            @endif
                        </div>
                        <div class="course-single-text">
                            <div class="course-title mt10 headline relative-position">
                                <h3><a href="{{ route('courses.show', [$course->slug]) }}"><b>{{ $course->title }}</b></a>
                                    @if ($course->trending == 1)
                                        <span class="trend-badge text-uppercase bold-font"><i class="fas fa-bolt"></i>
                                            @lang('labels.frontend.badges.trending')</span>
                                    @endif

                                </h3>
                            </div>
                            <div class="course-details-content">
                                <p>
                                    {!! $course->description !!}
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- /course-details -->

                </div>

                <div class="col-md-3">
                    <div class="side-bar">

                        @if(Auth::check() && $is_admin == true)

                            @if($course->is_online == 'Offline')
                             <p class="alert alert-success">@lang('Will start at :start_at',['start_at'=>$start_datetime])</p>

                             <p class="alert alert-success">@lang('Will end at :end_at',['end_at'=>$end_meeting_attend_time])</p>
                            @endif

                        

                        @elseif(Auth::check() && $is_admin == false)
                            @if($is_attended)

                                    @if (!@$isAssignmentTaken && $assessment_link)
                                        <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                            target="_blank" href="{{ htmlspecialchars_decode($assessment_link) }}">
                                            {{ trans('course.btn.start_assesment') }}
                                        </a>
                                    @endif
                                    @if (@$isAssignmentTaken)
                                        <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                            href="javascript:void(0)">{{ trans('course.btn.start_completed') }}</a>
                                    @endif
                                    @if ($course->grant_certificate)
                                        <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                            href="{{ route('admin.certificates.generate', ['course_id' => $course->id, 'user_id' => auth()->id()]) }}">
                                            {{ trans('course.btn.download_certificate') }}
                                        </a>
                                        <div class="alert alert-success">
                                            @lang('labels.frontend.course.certified')
                                        </div>
                                    @endif
                                    @if (
                                        @$isAssignmentTaken &&
                                            $course->courseAssignments->count() > 0 &&
                                            $course->assignmentStatus(auth()->id()) == 'Failed')
                                        <p class="text text-danger">@lang("Sorry! you didn't qualify the assignment. So certificate could not be issued.")</p>
                                        @if ($assessment_link)
                                            <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                                target="_blank" href="{{ htmlspecialchars_decode($assessment_link) }}">{{ trans('course.btn.re_attempt_assigment') }}</a>
                                        @endif
                                    @endif
                                    @if (@$courseFeedbackLink && $course->assignmentStatus(auth()->id()) != 'Failed')
                                        <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                            href="{{ $courseFeedbackLink }}">{{ trans('course.btn.give_feedback') }}</a>
                                    @endif

                                @else

                                    @if($course->is_online == 'Offline' || $course->is_online == 'Live-Classroom')
                                            @if($is_within_buffer && $is_after_endtime == false)    
                                                <a href="{{ route('recordAttendance', ['slug' => $course->slug]) }}"
                                                    class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font">

                                                    @lang('Attend Course')

                                                    <i class="fa fa-arow-right"></i></a> 
                                            @elseif($is_before)
                                                <span class="alert alert-success">@lang('Will start at :start_at',['start_at'=>$due_date_time])</span>
                                            @elseif($is_after_due)
                                                @if($first_lesson_slug)    
                                                    <a href="{{route('lessons.show',['course_id' => $course->id,'slug' => $first_lesson_slug])}}"
                                                        class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font">

                                                        @lang('labels.frontend.course.continue_course')

                                                        <i class="fa fa-arow-right"></i> 
                                                    </a> 
                                                @else
                                                    <span class="alert alert-success">@lang('No lessons yet')
                                                    </span>
                                                    <small class="sm-hint">*This course lesson was done by link, admin will add lessons soon</small>
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
                        @else
                            <a onclick="openLogin()" href="#" class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold">{{ __('auth_pages.login.login_to_continue') }}</a>            
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('after-scripts')
    <script>
        localStorage.setItem('redirect_url', window.location.href);

        function openLogin()
        {
            //alert(window.location.href)
            $("#redirect_url").val(window.location.href)
            $("#myModal").modal('show');
        }
    </script>
@endpush
