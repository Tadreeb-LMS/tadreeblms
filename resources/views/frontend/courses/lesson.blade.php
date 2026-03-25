@extends('frontend.layouts.app' . config('theme_layout'))

@push('after-styles')
    {{-- <link rel="stylesheet" href="{{asset('plugins/YouTube-iFrame-API-Wrapper/css/main.css')}}"> --}}
    <link rel="stylesheet" href="https://cdn.plyr.io/3.5.3/plyr.css" />
    <link href="{{ asset('plugins/touchpdf-master/jquery.touchPDF.css') }}" rel="stylesheet">

    <style>
        .test-form {
            color: #333333;
        }

        .course-details-category ul li {
            width: 100%;
        }

        .sidebar.is_stuck {
            top: 15% !important;
        }

        .course-timeline-list {
            max-height: 300px;
            overflow: scroll;
        }

        .options-list li {
            list-style-type: none;
        }

        .options-list li.correct {
            color: green;

        }

        .options-list li.incorrect {
            color: red;

        }

        .options-list li.correct:before {
            content: "\f058";
            /* FontAwesome Unicode */
            font-family: 'Font Awesome\ 5 Free';
            display: inline-block;
            color: green;
            margin-left: -1.3em;
            /* same as padding-left set on li */
            width: 1.3em;
            /* same as padding-left set on li */
        }

        .options-list li.incorrect:before {
            content: "\f057";
            /* FontAwesome Unicode */
            font-family: 'Font Awesome\ 5 Free';
            display: inline-block;
            color: red;
            margin-left: -1.3em;
            /* same as padding-left set on li */
            width: 1.3em;
            /* same as padding-left set on li */
        }

        .options-list li:before {
            content: "\f111";
            /* FontAwesome Unicode */
            font-family: 'Font Awesome\ 5 Free';
            display: inline-block;
            color: black;
            margin-left: -1.3em;
            /* same as padding-left set on li */
            width: 1.3em;
            /* same as padding-left set on li */
        }

        .touchPDF {
            border: 1px solid #e3e3e3;
        }

        .touchPDF>.pdf-outerdiv>.pdf-toolbar {
            height: 0;
            color: black;
            padding: 5px 0;
            text-align: right;
        }

        .pdf-tabs {
            width: 100% !important;
        }

        .pdf-outerdiv {
            width: 100% !important;
            left: 0 !important;
            padding: 0px !important;
            transform: scale(1) !important;
        }

        .pdf-viewer {
            left: 0px;
            width: 100% !important;
        }

        .pdf-drag {
            width: 100% !important;
        }

        .pdf-outerdiv {
            left: 0px !important;
        }

        .pdf-outerdiv {
            padding-left: 0px !important;
            left: 0px;
        }

        .pdf-toolbar {
            left: 0px !important;
            width: 99% !important;
            height: 30px;
        }

        .pdf-viewer {
            box-sizing: border-box;
            left: 0 !important;
            margin-top: 10px;
        }

        .pdf-title {
            display: none !important;
        }

        @media screen and (max-width: 768px) {}
    </style>
@endpush

@section('content')
    
    <!-- Start of breadcrumb section
                                                                                                    ============================================= -->
    <section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
        <div class="blakish-overlay"></div>
        <div class="container">
            <div class="page-breadcrumb-content text-center">
                <div class="page-breadcrumb-title">
                    <h2 class="breadcrumb-head black bold">
                        <span>{{ $lesson->course->title }}</span><br> {{ $lesson->title }} 
                    </h2>
                </div>
            </div>
        </div>
    </section>
    <!-- End of breadcrumb section
                                                                                                    ============================================= -->


    <!-- Start of course details section
                                                                                                    ============================================= -->
    <section id="course-details" class="course-details-section">
        <div class="container ">
            <div class="row main-content">
                <div class="col-md-9">
                    @if (session()->has('success'))
                        <div class="alert alert-dismissable alert-success fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif
                    @include('includes.partials.messages')

                    <div class="course-details-item border-bottom-0 mb-0">
                        @if ($lesson->lesson_image != '')
                            <div class="course-single-pic mb30">
                                <img src="{{ asset('storage/uploads/' . $lesson->lesson_image) }}" alt="">
                            </div>
                        @endif


                        @if ($test_exists)
                            <div class="course-single-text">
                                <div class="course-title mt10 headline relative-position">
                                    <h3>
                                        <b>@lang('labels.frontend.course.test')
                                            : {{ $lesson->title }}</b>
                                    </h3>
                                </div>
                                <div class="course-details-content">
                                    <p> {!! $lesson->full_text !!} </p>
                                </div>
                            </div>
                            <hr />
                            @if (!is_null($test_result))
                                <div class="alert alert-info">@lang('labels.frontend.course.your_test_score')
                                    : {{ $test_result->test_result }}
                                    <br>
                                    @lang('labels.frontend.course.your_score') : {{ number_format($percentage, 2) }}% <br>
                                    @lang('labels.frontend.course.your_result') : {{ $test_pass }}
                                </div>
                                @if (config('retest'))
                                    <form action="{{ route('lessons.retest', [$test_result->test->slug]) }}" method="post">
                                        @csrf
                                        <input type="hidden" name="result_id" value="{{ $test_result->id }}">
                                        <button type="submit" class="btn gradient-bg font-weight-bold text-white"
                                            href="">
                                            @lang('labels.frontend.course.give_test_again')
                                        </button>
                                    </form>
                                @endif

                                @if (count($lesson->questions) > 0)
                                    <hr>

                                    @foreach ($lesson->questions as $question)
                                        <h4 class="mb-0">{{ $loop->iteration }}
                                            . {!! $question->question !!} @if (!$question->isAttempted($test_result->id))
                                                <small class="badge badge-danger"> @lang('labels.frontend.course.not_attempted')</small>
                                            @endif
                                        </h4>
                                        <br />
                                        <ul class="options-list pl-4">
                                            @foreach ($question->options as $option)
                                                <li
                                                    class="@if (
                                                        ($option->answered($test_result->id) != null && $option->answered($test_result->id) == 1) ||
                                                            $option->correct == true) correct @elseif($option->answered($test_result->id) != null && $option->answered($test_result->id) == 2) incorrect @endif">
                                                    {{ $option->option_text }}

                                                    @if ($option->correct == 1 && $option->explanation != null)
                                                        <p class="text-dark">
                                                            <b>@lang('labels.frontend.course.explanation')</b><br>
                                                            {{ $option->explanation }}
                                                        </p>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                        <br />
                                    @endforeach
                                @else
                                    <h3>@lang('labels.general.no_data_available')</h3>
                                @endif
                            @else
                                {{-- {{dd($lesson->questions)}}         --}}
                                <div class="test-form">
                                    @if (count($lesson->questions) > 0)
                                        <form action="{{ route('lessons.test', [$lesson->slug]) }}" method="post">
                                            {{ csrf_field() }}
                                            @foreach ($lesson->questions as $question)
                                                <h4 class="mb-0">{{ $loop->iteration }}. {!! $question->question !!} </h4>
                                                <br />
                                                @foreach ($question->options as $option)
                                                    <div class="radio">
                                                        <label>
                                                            <input type="radio" name="questions[{{ $question->id }}]"
                                                                value="{{ $option->id }}" />
                                                            <span class="cr"><i class="cr-icon fa fa-circle"></i></span>
                                                            {{ $option->option_text }}<br />
                                                        </label>
                                                    </div>
                                                @endforeach
                                                <br />
                                            @endforeach
                                            <input class="btn gradient-bg text-white font-weight-bold" type="submit"
                                                value=" @lang('labels.frontend.course.submit_results') " />
                                        </form>
                                    @else
                                        <h3>@lang('labels.general.no_data_available')</h3>
                                    @endif
                                </div>
                            @endif
                            <hr />
                        @else
                            <div class="course-single-text">
                                <div class="course-title mt10 headline relative-position">
                                    <h3>
                                        <b>{{ $lesson->title }}</b>
                                    </h3>
                                </div>
                                <div class="course-details-content">
                                    @if ($lesson->live_lesson)
                                        <p>{{ $lesson->short_text }}</p>
                                    @else
                                        <p>{!! $lesson->full_text !!}</p>
                                    @endif
                                </div>

                                @if ($lesson->live_lesson)
                                    <h4 class="my-4">@lang('labels.frontend.course.available_slots')</h4>
                                    <div class="affiliate-market-guide mb65">
                                        <div class="affiliate-market-accordion">
                                            <div id="accordion" class="panel-group">
                                                @php $count = 0; @endphp
                                                @foreach ($lesson->liveLessonSlots as $lessonSlot)
                                                    @php $count++ @endphp
                                                    <div class="panel position-relative">
                                                        <div class="panel-title" id="headingOne">
                                                            <div class="ac-head">
                                                                <button class="btn btn-link collapsed"
                                                                    data-toggle="collapse"
                                                                    data-target="#collapse{{ $count }}"
                                                                    aria-expanded="false"
                                                                    aria-controls="collapse{{ $count }}">
                                                                    <span>{{ sprintf('%02d', $count) }}</span>
                                                                    {{ $lessonSlot->topic }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div id="collapse{{ $count }}" class="collapse"
                                                            aria-labelledby="headingOne" data-parent="#accordion">
                                                            <div class="panel-body">
                                                                {!! $lessonSlot->description !!}
                                                                <p class="my-auto"><span
                                                                        class="font-weight-bold">@lang('labels.frontend.course.live_lesson_meeting_date')</span> :
                                                                    {{ $lessonSlot->start_at->format('d-m-Y h:i A') }}
                                                                    <strong>({{ config('zoom.timezone') }})</strong>
                                                                </p>
                                                                <p class="my-auto"><span
                                                                        class="font-weight-bold">@lang('labels.frontend.course.live_lesson_meeting_duration')</span> :
                                                                    {{ $lessonSlot->duration }}</p>
                                                                @if ($lesson->lessonSlotBooking && $lesson->lessonSlotBooking->where('user_id', auth()->user()->id)->count())
                                                                    @if (auth()->user()->lessonSlotBookings()->where('live_lesson_slot_id', $lessonSlot->id)->first())
                                                                        @if ($lessonSlot->start_at->timezone(config('zoom.timezone'))->gt(\Carbon\Carbon::now(new DateTimeZone(config('zoom.timezone')))))
                                                                            <p class="my-auto"><span
                                                                                    class="font-weight-bold">@lang('labels.frontend.course.live_lesson_meeting_id')</span>
                                                                                : {{ $lessonSlot->meeting_id }}</p>
                                                                            <p class="my-auto"><span
                                                                                    class="font-weight-bold">@lang('labels.frontend.course.live_lesson_meeting_password')</span>
                                                                                : {{ $lessonSlot->password }}</p>

                                                                            <a class="btn btn-info mt-3"
                                                                                href="{{ $lessonSlot->join_url }}"
                                                                                target="_blank"
                                                                                
                                                                                >
                                                                                <span
                                                                                    class="text-white font-weight-bold ">@lang('labels.frontend.course.live_lesson_join_url')</span>
                                                                            </a>
                                                                        @endif
                                                                    @endif
                                                                @else
                                                                    @if ($lessonSlot->lessonSlotBookings->count() >= $lessonSlot->student_limit)
                                                                        <span class="btn btn-danger mt-3">
                                                                            <span
                                                                                class="text-white font-weight-bold ">@lang('labels.frontend.course.full_slot')</span>
                                                                        </span>
                                                                    @else
                                                                        <form method="post"
                                                                            action="{{ route('lessons.course.book-slot') }}">
                                                                            @csrf
                                                                            <input type="hidden"
                                                                                value="{{ $lessonSlot->id }}"
                                                                                name="live_lesson_slot_id">
                                                                            <input type="hidden"
                                                                                value="{{ $lesson->id }}"
                                                                                name="lesson_id">
                                                                            <button
                                                                                class="btn btn-info mt-3">@lang('labels.frontend.course.book_slot')</button>
                                                                        </form>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        

                        @if ($lesson->media)
                            <div class="course-single-text mb-5">
                                {{-- <iframe src="{{asset('storage/uploads/'.$lesson->mediaPDF->name)}}" width="100%" --}}
                                {{-- height="500px"> --}}
                                {{-- </iframe> --}}
                                
                                @foreach($lesson->media as $media)
                                       @if($media->type === 'lesson_pdf')
                                            <div id="myPDF-{{ $media->id }}">
                                                <iframe src="{{ $media->url }}" width="100%" height="600px"></iframe>
                                            </div>
                                        @endif
                                @endforeach

                            </div>
                        @endif


                        @if ($lesson->mediaVideo)
                            <div class="course-single-text">
                                @if ($lesson->mediavideo != '')
                                    <div class="course-details-content mt-3">
                                        <div class="video-container mb-5" data-id="{{ $lesson->mediavideo->id }}">
                                            @if ($lesson->mediavideo->type == 'youtube')
                                                <div id="player" onclick="videoPer(this);" class="js-player"
                                                    data-plyr-provider="youtube"
                                                    data-plyr-embed-id="{{ $lesson->mediavideo->url }}"></div>
                                            @elseif($lesson->mediavideo->type == 'vimeo')
                                                <div id="player" class="js-player" data-plyr-provider="vimeo"
                                                    data-plyr-embed-id="{{ $lesson->mediavideo->url }}"></div>
                                            @elseif($lesson->mediavideo->type == 'upload')
                                                <video poster="" id="player" class="js-player" playsinline
                                                    controls>
                                                    <source src="{{ $lesson->mediavideo->url }}" type="video/mp4" />
                                                </video>
                                            @elseif($lesson->mediavideo->type == 'embed')
                                                {!! $lesson->mediavideo->url !!}
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($lesson->media)
                            <div class="course-single-text mb-5">
                                
                                @foreach($lesson->media as $media)
                                       @if($media->type === 'lesson_audio')
                                            <audio id="audioPlayer" controls>
                                                <source src="{{ $media->url }}" type="audio/mp3" />
                                            </audio>
                                        @endif
                                @endforeach
                            </div>
                        @endif
                        


                        @if ($lesson->media != '' && $lesson->media->count() > 0)
                            <div class="course-single-text mt-4 px-3 py-1 gradient-bg text-white">
                                <div class="course-title mt10 headline relative-position">
                                    <h4 class="text-white">
                                        @lang('labels.frontend.course.download_files')
                                    </h4>
                                </div>

                                @foreach ($lesson->media as $media)
                                    @if($media->type === 'download_file')
                                        <div class="course-details-content text-white">
                                            <p class="form-group">
                                                <a href="{{ $media->url }}"
                                                    target="_blank" download="download" class="text-white font-weight-bold"><i
                                                        class="fa fa-download"></i>


                                                    {{ $media->file_name }}
                                                    ({{ number_format((float) $media->size / 1024, 2, '.', '') }}
                                                    @lang('labels.frontend.course.mb'))
                                                </a>
                                            </p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        @if(!$test_exists && isset($lesson_quiz) && $lesson_quiz)
                            @if($lesson->isCompleted())
                                <div class="alert alert-info mt-4 mb-0">
                                    This lesson has a quiz in the next section.
                                    @if($lesson_quiz_pass === 'Pass')
                                        <span class="ml-2 text-success font-weight-bold">Quiz passed</span>
                                    @elseif($lesson_quiz_pass === 'Failed')
                                        <span class="ml-2 text-danger font-weight-bold">Quiz not passed yet</span>
                                    @endif
                                    @if(!empty($lesson_quiz_url))
                                        <a class="btn btn-sm btn-info text-white ml-2" href="{{ $lesson_quiz_url }}">
                                            Open Lesson Quiz
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-warning mt-4 mb-0">
                                    Complete this lesson first to unlock its quiz section.
                                </div>
                            @endif
                        @endif

                    </div>
                    <!-- /course-details -->

                    <!-- /market guide -->

                    <!-- /review overview -->
                </div>

                <div class="col-md-3">
                    <div id="sidebar">
                        <div class="course-details-category ul-li">

                            @php
                                $currentPosition = isset($lesson->position) ? (int) $lesson->position : null;

                                $previousLessonByPosition = null;
                                if (!is_null($currentPosition)) {
                                    $previousLessonByPosition = collect($course_lessons_arr ?? [])
                                        ->filter(function ($item) use ($currentPosition) {
                                            return (int) ($item->published ?? 0) === 1
                                                && !is_null($item->position)
                                                && (int) $item->position < $currentPosition;
                                        })
                                        ->sortByDesc('position')
                                        ->first();
                                }

                                $effectivePreviousLesson = null;
                                if (!empty($previous_lesson)) {
                                    $timelinePreviousPosition = isset($previous_lesson->model->position)
                                        ? (int) $previous_lesson->model->position
                                        : null;

                                    // Guard against reversed timeline records (e.g. lesson 1 incorrectly getting lesson 2 as previous).
                                    if (!is_null($currentPosition) && !is_null($timelinePreviousPosition)) {
                                        if ($timelinePreviousPosition < $currentPosition) {
                                            $effectivePreviousLesson = $previous_lesson->model;
                                        } elseif (!empty($previousLessonByPosition)) {
                                            $effectivePreviousLesson = $previousLessonByPosition;
                                        }
                                    } else {
                                        $effectivePreviousLesson = $previous_lesson->model;
                                    }
                                } elseif (!empty($previousLessonByPosition)) {
                                    $effectivePreviousLesson = $previousLessonByPosition;
                                }
                            @endphp
                            <p><a class="btn btn-block gradient-bg font-weight-bold text-white"
                                  href="{{ $effectivePreviousLesson ? route('lessons.show', [$lesson->course->id, $effectivePreviousLesson->slug]) : route('courses.show', [$lesson->course->slug]) }}">
                                    @lang('labels.frontend.course.prev')
                                    <i class="fa fa-angle-double-left"></i>
                                </a></p>

                            <p id="nextButton">
                                @if($next_lesson && empty($nextTasks['open_assesment']) && empty($nextTasks['reattempt_assesment']))
                                    @if(!empty($requires_lesson_quiz_pass_for_next) && empty($can_access_next_lesson))
                                        <a class="btn btn-block bg-danger font-weight-bold text-white"
                                           href="javascript:void(0)">
                                            Complete and pass the lesson quiz to unlock the next lesson
                                        </a>
                                        @if($lesson->isCompleted() && !empty($lesson_quiz_url))
                                            <a class="btn btn-block btn-info font-weight-bold text-white mt-2"
                                               href="{{ $lesson_quiz_url }}">
                                                Open Lesson Quiz
                                            </a>
                                        @elseif(!$lesson->isCompleted())
                                            <a class="btn btn-block btn-warning font-weight-bold text-white mt-2" href="javascript:void(0)">
                                                Complete this lesson first to unlock its quiz section
                                            </a>
                                        @endif
                                    @else
                                        @if((int)config('lesson_timer') == 1 && $lesson->isCompleted() )
                                            <a class="btn btn-block gradient-bg font-weight-bold text-white"
                                               href="{{ route('lessons.show', [$next_lesson->course_id, $next_lesson->model->slug]) }}">
                                                <i class='fa fa-angle-double-right'></i>@lang('labels.frontend.course.next') </a>
                                        @else
                                            <a class="btn btn-block gradient-bg font-weight-bold text-white"
                                               href="{{ route('lessons.show', [$next_lesson->course_id, $next_lesson->model->slug]) }}">
                                                <i class='fa fa-angle-double-right'></i>@lang('labels.frontend.course.next') </a>

                                        @endif
                                    @endif
                                @elseif($lesson->isCompleted() && !empty($lesson_quiz_url))
                                    <a class="btn btn-block btn-info font-weight-bold text-white"
                                       href="{{ $lesson_quiz_url }}">
                                        Open Lesson Quiz
                                    </a>
                                @endif

                            </p>
                                    
                            @if ($nextTasks['open_assesment'])
                                <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                    target="_blank" href="{{ htmlspecialchars_decode($assessment_link) }}">@lang('labels.frontend.course.start_assesment')</a>
                            @endif

                            @if ($nextTasks['reattempt_assesment'])
                                <p class="text text-danger">@lang("Sorry! you didn't qualify the assignment. So certificate could not be issued.")</p>
                                <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                    target="_blank" href="{{ htmlspecialchars_decode($assessment_link) }}">@lang('labels.frontend.course.re_attempt_assesment')</a>
                            @endif

                            @if($nextTasks['failed_in_assesment_all_attempts'])
                                <p class="text text-danger">@lang("Sorry! you didn't qualify the assignment. So certificate could not be issued.")</p>
                                <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                    href="javascript:void(0)">@lang('labels.frontend.course.assesment_completed')</a>
                            @endif

                            @if ($nextTasks['completed_assesment'])
                                <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                    href="javascript:void(0)">@lang('labels.frontend.course.assesment_completed')</a>
                            @endif

                            @if($nextTasks['open_feedback'] && (empty($lesson_quiz) || $lesson_quiz_pass === 'Pass'))
                                <a class="btn btn-info btn-block text-white mb-3"
                                href="{{ route('course-feedback',$lesson->course->id) }}">@lang('labels.frontend.course.give_feedback')</a>
                            @endif
                                    
                               
                            
                            @if($nextTasks['download_certificate'] ) 
                                <a class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                            href="{{ route('admin.certificates.generate', ['course_id' => request()->course_id, 'user_id' => auth()->id()]) }}">@lang('labels.frontend.course.download_certificate')</a>
                                        <div class="alert alert-success">
                                            @lang('labels.frontend.course.certified')
                                        </div>        
                            @endif

                            <span class="float-none"></span>
                            <ul class="course-timeline-list">
                                 {{-- {{ dd($completed_lessons) }}  --}}
                                @foreach ($course_lessons_arr as $key => $item)
                                    @if ($item->published == 1)
                                        {{-- @php $key++; @endphp --}}
                                        <li class="@if ($lesson->id == $item->id) active @endif ">

                                            <a
                                                href="{{ route('lessons.show', ['course_id' => $lesson->course->id, 'slug' => $item->slug]) }}">
                                                {{ $item->title }}
                                                {{-- @if ($item->model_type == 'App\Models\Test')
                                                    <p class="mb-0 text-primary">
                                                        - @lang('labels.frontend.course.test')</p>
                                                @endif --}}
                                                @if (in_array($item->id, $completed_lessons))
                                                    <i class="fa text-success float-right fa-check-square"></i>
                                                @endif
                                            </a>


                                            <a @if (in_array($item->id, $completed_lessons)) href="{{ route('lessons.show', ['course_id' => $lesson->course->id, 'slug' => $item->slug]) }}" @endif
                                                style="display:none">
                                                {{ $item->title }}
                                                {{-- @if ($item->model_type == 'App\Models\Test')
                                                    <p class="mb-0 text-primary">
                                                        - @lang('labels.frontend.course.test')</p>
                                                @endif --}}
                                                @if (in_array($item->id, $completed_lessons))
                                                    <i class="fa text-success float-right fa-check-square"></i>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        <div class="couse-feature ul-li-block">
                            <ul>
                                <li> <em> @lang('labels.frontend.course.chapters')</em>
                                    <span style="display: none;"> {{ $lesson->course->chapterCount() }} </span>
                                    <span> {{ $lessonCount }} </span>
                                </li>
                                <li> <em>@lang('labels.frontend.course.category') </em><span><a
                                            href="{{ route('courses.category', ['category' => $lesson->course->category->slug]) }}"
                                            target="_blank">{{ $lesson->course->category->name }}</a> </span></li>
                                <li><em>@lang('labels.frontend.course.author')</em> <span>

                                        @foreach ($lesson->course->teachers as $key => $teacher)
                                            @php $key++ @endphp
                                            <a href="{{ route('teachers.show', ['id' => $teacher->id]) }}"
                                                target="_blank">
                                                {{ $teacher->full_name }}@if ($key < count($lesson->course->teachers))
                                                    ,
                                                @endif
                                            </a>
                                        @endforeach
                                    </span>
                                </li>
                                {{-- <li>@lang('labels.frontend.course.progress') <span> <b> {{ CustomHelper::progress($lesson->course->id)  }}
                                            % @lang('labels.frontend.course.completed')</b></span>
                                </li> --}}
                            </ul>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End of course details section
                                                                                                ============================================= -->

@endsection

@push('after-scripts')
    {{-- <script src="//www.youtube.com/iframe_api"></script> --}}
    <script src="{{ asset('plugins/sticky-kit/sticky-kit.js') }}"></script>
    <script src="https://cdn.plyr.io/3.5.3/plyr.polyfilled.js"></script>
    <script src="{{ asset('plugins/touchpdf-master/pdf.compatibility.js') }}"></script>
    <script src="{{ asset('plugins/touchpdf-master/pdf.js') }}"></script>
    <script src="{{ asset('plugins/touchpdf-master/jquery.touchSwipe.js') }}"></script>
    <script src="{{ asset('plugins/touchpdf-master/jquery.touchPDF.js') }}"></script>
    <script src="{{ asset('plugins/touchpdf-master/jquery.panzoom.js') }}"></script>
    <script src="{{ asset('plugins/touchpdf-master/jquery.mousewheel.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>


    <script>
        @if ($lesson->mediaPDF)
            $(function() {
                $("#myPDF").pdf({
                    source: "{{ asset('storage/uploads/' . $lesson->mediaPDF->name) }}",
                    loadingHeight: 800,
                    loadingWidth: 800,
                    loadingHTML: ""
                });

            });
        @endif

        var storedDuration = 0;
        var storedLesson;
        storedDuration = Cookies.get("duration_" + "{{ auth()->user()->id }}" + "_" + "{{ $lesson->id }}" + "_" +
            "{{ $lesson->course->id }}");
        storedLesson = Cookies.get("lesson" + "{{ auth()->user()->id }}" + "_" + "{{ $lesson->id }}" + "_" +
            "{{ $lesson->course->id }}");
        var user_lesson;

        if (parseInt(storedLesson) != parseInt("{{ $lesson->id }}")) {
            Cookies.set('lesson', parseInt('{{ $lesson->id }}'));
        }


        @if ($lesson->mediaVideo && $lesson->mediaVideo->type != 'embed')
            var current_progress = 0;


            @if ($lesson->mediaVideo->getProgress(auth()->user()->id) != '')
                current_progress = "{{ $lesson->mediaVideo->getProgress(auth()->user()->id)->progress }}";
            @endif



            const player2 = new Plyr('#audioPlayer');

            const player = new Plyr('#player', {
                youtube: {
                    noCookie: true
                }
            });

            duration = 10;
            var progress = 0;
            var video_id = $('#player').parents('.video-container').data('id');
            player.on('ready', event => {
                player.currentTime = parseInt(current_progress);
                duration = event.detail.plyr.duration;


                if (!storedDuration || (parseInt(storedDuration) === 0)) {
                    Cookies.set("duration_" + "{{ auth()->user()->id }}" + "_" + "{{ $lesson->id }}" + "_" +
                        "{{ $lesson->course->id }}", duration);
                }

            });

            {{-- if (!storedDuration || (parseInt(storedDuration) === 0)) { --}}
            {{-- Cookies.set("duration_" + "{{auth()->user()->id}}" + "_" + "{{$lesson->id}}" + "_" + "{{$lesson->course->id}}", player.duration); --}}
            {{-- } --}}


            // setInterval(function() {
            //     player.on('timeupdate', event => {
            //         if ((parseInt(current_progress) > 0) && (parseInt(current_progress) < parseInt(event
            //                 .detail.plyr.currentTime))) {
            //             progress = current_progress;
            //         } else {
            //             progress = parseInt(event.detail.plyr.currentTime);
            //         }
            //     });
            //     if (duration !== 0 || parseInt(progress) !== 0) {
            //         saveProgress(video_id, duration, parseInt(progress));
            //     }
            // }, 1500);


            // function saveProgress(id, duration, progress) {
            //     // alert(progress)
            //     $.ajax({
            //         url: "{{ route('update.videos.progress') }}",
            //         method: "POST",
            //         data: {
            //             "_token": "{{ csrf_token() }}",
            //             'video': parseInt(id),
            //             'duration': parseInt(duration),
            //             'progress': parseInt(progress)
            //         },
            //         success: function(result) {
            //             if (progress === duration) {
            //                 location.reload();
            //             }
            //         }
            //     });
            // }


            $('#notice').on('hidden.bs.modal', function() {
                //location.reload();
            });
        @endif

        $("#sidebar").stick_in_parent();


        @if ((int) config('lesson_timer') != 0)
            //Next Button enables/disable according to time

            var readTime, totalQuestions, testTime;
            user_lesson = Cookies.get("user_lesson_" + "{{ auth()->user()->id }}" + "_" + "{{ $lesson->id }}" + "_" +
                "{{ $lesson->course->id }}");

            @if ($test_exists)
                totalQuestions = '{{ count($lesson->questions) }}'
                readTime = parseInt(totalQuestions) * 30;
            @else
                readTime = parseInt("{{ $lesson->readTime() }}") * 60;
            @endif

            @if (!$lesson->isCompleted())
                storedDuration = Cookies.get("duration_" + "{{ auth()->user()->id }}" + "_" + "{{ $lesson->id }}" +
                    "_" + "{{ $lesson->course->id }}");

                storedLesson = Cookies.get("lesson" + "{{ auth()->user()->id }}" + "_" + "{{ $lesson->id }}" + "_" +
                    "{{ $lesson->course->id }}");

                if (storedDuration > 0) {
                    var totalLessonTime = parseInt(storedDuration) ? parseInt(storedDuration) : 0;
                } else {
                    var totalLessonTime = readTime + (parseInt(storedDuration) ? parseInt(storedDuration) : 0);
                }


                var storedCounter = (Cookies.get("storedCounter_" + "{{ auth()->user()->id }}" + "_" +
                        "{{ $lesson->id }}" + "_" + "{{ $lesson->course->id }}")) ? Cookies.get("storedCounter_" +
                        "{{ auth()->user()->id }}" + "_" + "{{ $lesson->id }}" + "_" + "{{ $lesson->course->id }}"
                    ) :
                    0;
                var counter;
                if (user_lesson) {
                    if (user_lesson === 'true') {
                        counter = 1;
                    }
                } else {
                    if ((storedCounter != 0) && storedCounter < totalLessonTime) {
                        counter = storedCounter;
                    } else {
                        counter = totalLessonTime;
                    }
                }
                var interval = setInterval(function() {
                    counter--;
                    // Display 'counter' wherever you want to display it.
                    if (counter >= 0) {
                        alert(counter)
                        // Display a next button box
                        $('#nextButton').html(
                            "<a class='btn btn-block bg-danger font-weight-bold text-white' href='#'>@lang('labels.frontend.course.next') (in " +
                            counter + " seconds)</a>")
                        Cookies.set("duration_" + "{{ auth()->user()->id }}" + "_" + "{{ $lesson->id }}" +
                            "_" + "{{ $lesson->course->id }}", counter);

                    }
                    if (counter === 0) {
                        Cookies.set("user_lesson_" + "{{ auth()->user()->id }}" + "_" + "{{ $lesson->id }}" +
                            "_" + "{{ $lesson->course->id }}", 'true');
                        Cookies.remove('duration');

                        @if ($test_exists && is_null($test_result))
                            $('#nextButton').html(
                                "<a class='btn btn-block bg-danger font-weight-bold text-white' href='#'>@lang('labels.frontend.course.complete_test')</a>"
                            )
                        @else
                            @if ($next_lesson && empty($nextTasks['open_assesment']) && empty($nextTasks['reattempt_assesment']))
                                @if(!empty($requires_lesson_quiz_pass_for_next) && empty($can_access_next_lesson))
                                    $('#nextButton').html(
                                        "<a class='btn btn-block bg-danger font-weight-bold text-white' href='javascript:void(0)'>Complete and pass the lesson quiz to unlock the next lesson</a>" +
                                        "@if(!empty($lesson_quiz_url))<a class='btn btn-block btn-info font-weight-bold text-white mt-2' href='{{ $lesson_quiz_url }}'>Open Lesson Quiz</a>@endif"
                                    );
                                @else
                                    $('#nextButton').html(
                                        "<a class='btn btn-block gradient-bg font-weight-bold text-white'" +
                                        " href='{{ route('lessons.show', [$next_lesson->course_id, $next_lesson->model->slug]) }}'>@lang('labels.frontend.course.next')<i class='fa fa-angle-double-right'></i> </a>"
                                    );
                                @endif
                            @else
                                $('#nextButton').html(
                                    "<form method='post' action='{{ route('admin.certificates.generate') }}'>" +
                                    "<input type='hidden' name='_token' id='csrf-token' value='{{ Session::token() }}' />" +
                                    "<input type='hidden' value='{{ $lesson->course->id }}' name='course_id'> " +
                                    "<button class='btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold' id='finish'>@lang('labels.frontend.course.finish_course')</button></form>"
                                );
                            @endif

                            @if (!$lesson->isCompleted())
                                courseCompleted("{{ $lesson->id }}", "{{ get_class($lesson) }}");
                            @endif
                        @endif
                        clearInterval(counter);
                    }
                }, 1000);
            @endif
        @endif

        function courseCompleted(id, type) {
            $.ajax({
                url: "{{ route('update.course.progress') }}",
                method: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'model_id': parseInt(id),
                    'model_type': type,
                },
            });
        }
    </script>

    <script>
        // $("#player").bind("timeupdate", function() {
        //     var currentTime = this.currentTime;
        //     var watchPoint = Math.floor((currentTime / this.duration) * 100);
        //     if ((parseInt(current_progress) > 0) && (parseInt(current_progress) < parseInt(event
        //             .detail.plyr.currentTime))) {
        //         progress = current_progress;
        //     } else {
        //         progress = parseInt(event.detail.plyr.currentTime);
        //     }
        //     time(watchPoint, progress)
        // });
        let playedDuration = 0;
        let lastRecordedTime = current_progress ?? 0;
        let watchDuration = 0;
        let lastCalledTime = 0;

        player.on('timeupdate', () => {
            const currentTime = player.currentTime;
            const playbackRate = player.media.playbackRate;
            var watchPoint = Math.floor((currentTime / player.duration) * 100);

            // Check if the user is watching continuously (no skipping)
            if (Math.abs(currentTime - lastRecordedTime) <= 1) {
                // Adjust the watched duration by the playback rate
                watchDuration += (currentTime - lastRecordedTime) * playbackRate;
            }

            // Update lastRecordedTime for the next timeupdate event
            lastRecordedTime = currentTime;

            // Check if 2 seconds have passed since the last progress update
            if (currentTime - lastCalledTime >= 2) {
                time(watchPoint, watchDuration, player.duration)

                // Update lastCalledTime to the current time
                lastCalledTime = currentTime;
            }
        });

        var lessonAlreadyCompleted = false;

        function time(watchPoint, progress, videoDuration) {
            //alert("hi")
            var id = "{{ $lesson->id }}";
            var video = $('#player').parents('.video-container').data('id');
            $.ajax({
                url: "{{ route('video.progress.update') }}",
                method: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'media_id': parseInt(id),
                    'vedio_id': parseInt(video),
                    'watchPoint': watchPoint,
                    'duration': parseInt(videoDuration),
                    'progress': parseInt(progress)
                },
                success: function(response) {
                    if (response.lesson_completed && !lessonAlreadyCompleted) {
                        lessonAlreadyCompleted = true;
                        window.location.reload();
                    }
                },
            });
        }
    </script>
@endpush
