@extends('frontend.layouts.app' . config('theme_layout'))

@push('after-styles')
    <style>
        .quiz-options li {
            list-style-type: none;
            margin-bottom: 8px;
        }

        .quiz-options li.correct {
            color: #198754;
            font-weight: 600;
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
                        <span>{{ $lesson->course->title }}</span><br> {{ $lesson->title }} - Quiz
                    </h2>
                </div>
            </div>
        </div>
    </section>

    <section id="course-details" class="course-details-section">
        <div class="container">
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
                        <div class="course-single-text">
                            <div class="course-title mt10 headline relative-position">
                                <h3>
                                    <b><i class="fa fa-question-circle mr-1"></i> Lesson Quiz: {{ $lesson->title }}</b>
                                </h3>
                            </div>
                            <div class="course-details-content">
                                <p>Complete this quiz to unlock the next lesson.</p>
                            </div>
                        </div>

                        <hr>

                        @if($lesson_quiz_result)
                            <div class="alert alert-info">
                                Your score: {{ number_format($lesson_quiz_percentage, 0) }}%
                                ({{ $lesson_quiz_result->test_result }} / {{ $lesson_quiz_questions->count() }})
                                <br>
                                Result:
                                <strong class="{{ $lesson_quiz_pass === 'Pass' ? 'text-success' : 'text-danger' }}">
                                    {{ $lesson_quiz_pass }}
                                </strong>
                            </div>

                            @if($next_lesson && !$can_access_next_lesson)
                                <div class="alert alert-warning">
                                    You must pass this quiz to continue to the next lesson.
                                </div>
                            @endif

                            @foreach($lesson_quiz_questions as $question)
                                @php $questionOptions = $question->options ?? \DB::table('test_question_options')->where('question_id', $question->id)->get(); @endphp
                                <h4 class="mb-1">{{ $loop->iteration }}. {!! $question->question_text !!}</h4>
                                <ul class="quiz-options pl-4">
                                    @foreach($questionOptions as $option)
                                        <li class="{{ $option->is_right ? 'correct' : '' }}">{!! $option->option_text !!}</li>
                                    @endforeach
                                </ul>
                                <br>
                            @endforeach

                            <form action="{{ route('lessons.lesson_quiz', $lesson->id) }}" method="post">
                                @csrf
                                <input type="hidden" name="retest" value="1">
                                <button type="submit" class="btn gradient-bg font-weight-bold text-white">
                                    @lang('labels.frontend.course.give_test_again')
                                </button>
                            </form>
                        @else
                            <form action="{{ route('lessons.lesson_quiz', $lesson->id) }}" method="post" id="lesson-quiz-form">
                                @csrf
                                @foreach($lesson_quiz_questions as $question)
                                    @php $questionOptions = $question->options ?? \DB::table('test_question_options')->where('question_id', $question->id)->get(); @endphp
                                    <h4 class="mb-1">{{ $loop->iteration }}. {!! $question->question_text !!}</h4>
                                    @foreach($questionOptions as $option)
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="lesson_quiz_questions[{{ $question->id }}]" value="{{ $option->id }}" required />
                                                <span class="cr"><i class="cr-icon fa fa-circle"></i></span>
                                                {!! $option->option_text !!}
                                            </label>
                                        </div>
                                    @endforeach
                                    <br>
                                @endforeach

                                <input class="btn gradient-bg text-white font-weight-bold" type="submit"
                                       value="@lang('labels.frontend.course.submit_results')" />
                            </form>
                        @endif
                    </div>
                </div>

                <div class="col-md-3">
                    <div id="sidebar">
                        <div class="course-details-category ul-li">
                            <p>
                                <a class="btn btn-block gradient-bg font-weight-bold text-white"
                                   href="{{ route('lessons.show', [$course_id, $lesson->slug]) }}">
                                    <i class="fa fa-angle-double-left"></i> Back To Lesson
                                </a>
                            </p>

                            @if($next_lesson)
                                @if($can_access_next_lesson)
                                    <p>
                                        <a class="btn btn-block gradient-bg font-weight-bold text-white"
                                           href="{{ route('lessons.show', [$next_lesson->course_id, $next_lesson->model->slug]) }}">
                                            Next Lesson <i class="fa fa-angle-double-right"></i>
                                        </a>
                                    </p>
                                @else
                                    <p>
                                        <a class="btn btn-block bg-danger font-weight-bold text-white" href="javascript:void(0)">
                                            Pass this quiz to unlock the next lesson
                                        </a>
                                    </p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
