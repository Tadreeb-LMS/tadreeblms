<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $assignment->title ?? 'Final Assessment' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        body {
            min-height: 100vh;
            background: #f4f7fb;
            color: #253047;
        }

        .assessment-page {
            padding: 36px 0;
        }

        .assessment-shell {
            max-width: 1040px;
            margin: 0 auto;
        }

        .assessment-header {
            background: #233e74;
            color: #fff;
            border-radius: 8px 8px 0 0;
            padding: 24px 28px;
            display: flex;
            justify-content: space-between;
            gap: 24px;
            align-items: flex-start;
        }

        .assessment-title {
            margin: 0;
            font-weight: 700;
            line-height: 1.25;
        }

        .assessment-subtitle {
            margin: 8px 0 0;
            color: #d8e2f2;
        }

        .assessment-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .assessment-pill {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .assessment-card {
            background: #fff;
            border: 1px solid #dfe7f3;
            border-top: 0;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 10px 28px rgba(35, 62, 116, 0.08);
            padding: 28px;
        }

        .assessment-progress {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
            padding-bottom: 18px;
            border-bottom: 1px solid #edf1f7;
        }

        .assessment-progress strong {
            color: #233e74;
        }

        .assessment-progress-bar {
            flex: 1;
            height: 8px;
            border-radius: 20px;
            background: #e8eef9;
            overflow: hidden;
        }

        .assessment-progress-fill {
            display: block;
            height: 100%;
            width: 0;
            background: #29a36a;
            transition: width 0.2s ease;
        }

        .mg_form {
            border: 1px solid #e3eaf5;
            border-radius: 8px;
            padding: 22px;
            margin-bottom: 18px;
            background: #fff;
        }

        .mg_form.is-answered {
            border-color: #bde7d6;
            background: #fbfffd;
        }

        .question-kicker {
            color: #6f7c91;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .mg_question_detail {
            color: #253047;
            font-size: 18px;
            line-height: 1.45;
            margin-bottom: 16px;
        }

        .assessment-option {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border: 1px solid #e3eaf5;
            border-radius: 8px;
            padding: 13px 14px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: border-color 0.15s ease, background 0.15s ease;
        }

        .assessment-option:hover,
        .assessment-option:has(input:checked) {
            border-color: #233e74;
            background: #f7faff;
        }

        .assessment-option input {
            margin-top: 4px;
        }

        .assessment-option label {
            margin: 0;
            cursor: pointer;
            width: 100%;
        }

        .short-answer-field {
            min-height: 140px;
            border-radius: 8px;
            border-color: #d8e2f2;
            padding: 14px;
        }

        .assessment-actions {
            position: sticky;
            bottom: 0;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 16px;
            margin: 24px -28px -28px;
            padding: 18px 28px;
            background: rgba(255, 255, 255, 0.96);
            border-top: 1px solid #edf1f7;
            border-radius: 0 0 8px 8px;
        }

        .assessment-submit {
            min-width: 180px;
            border: 0;
            border-radius: 6px;
            background: #233e74;
            padding: 12px 22px;
            font-weight: 700;
        }

        .assessment-submit:hover {
            background: #1b315d;
        }

        @media (max-width: 767px) {
            .assessment-page {
                padding: 0;
            }

            .assessment-header,
            .assessment-card {
                border-radius: 0;
            }

            .assessment-header,
            .assessment-progress {
                flex-direction: column;
            }

            .assessment-meta {
                justify-content: flex-start;
            }

            .assessment-card {
                padding: 18px;
            }

            .assessment-actions {
                margin: 20px -18px -18px;
                padding: 14px 18px;
            }
        }
    </style>
</head>
@php
    $course_id = 0;

@endphp

<body>
    <div class="assessment-page">
        <div class="container">
            <div class="assessment-shell">
                <div class="assessment-header">
                    <div>
                        <h1 class="h3 assessment-title">{{ $assignment->title ?? 'Final Assessment' }}</h1>
                        <p class="assessment-subtitle">Answer every question before submitting your assessment.</p>
                    </div>
                    <div class="assessment-meta">
                        <span class="assessment-pill">{{ count($test_questions) }} Questions</span>
                        @if(!empty($assignment->duration))
                            <span class="assessment-pill">{{ $assignment->duration }} Minutes</span>
                        @endif
                    </div>
                </div>
                <div class="assessment-card">


                @if (!isset($_GET['feedback']))


                    <form class="" method="POST">
                        <input type="hidden" name="id" value="{{ @$assessment_account->id }}" />
                        @if (count($test_questions) > 0)
                            <div class="assessment-progress">
                                <span><strong id="answered-count">0</strong> of {{ count($test_questions) }} answered</span>
                                <div class="assessment-progress-bar" aria-hidden="true">
                                    <span class="assessment-progress-fill" id="assessment-progress-fill"></span>
                                </div>
                            </div>
                        @endif
                        @foreach ($test_questions as $key => $value)
                            @if ($value->question_type == 1)
                                <div class="form-group mg_form">
                                    <div class="question-kicker">Question {{ $key + 1 }} · Single choice</div>
                                    <h5 class="mg_question_detail" data-question-id="{{ $value->id }}"
                                        data-question-type="{{ $value->question_type }}"><?= $value->question_text ?>
                                    </h5>
                                    @foreach ($value->options as $op_key => $op_value)
                                        <div class="form-check assessment-option">
                                            <input class="form-check-input" type="radio"
                                                name="mg_options_{{ $value->id }}"
                                                id="mg_options_{{ $op_value->id }}" value="{{ $op_value->id }}"
                                                required="">
                                            <label class="form-check-label" for="mg_options_{{ $op_value->id }}">
                                                <?= $op_value->option_text ?>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif ($value->question_type == 2)
                                <div class="form-group mg_form">
                                    <div class="question-kicker">Question {{ $key + 1 }} · Multiple choice</div>
                                    <h5 class="mg_question_detail" data-question-id="{{ $value->id }}"
                                        data-question-type="{{ $value->question_type }}"><?= $value->question_text ?>
                                    </h5>
                                    @foreach ($value->options as $op_key => $op_value)
                                        <div class="form-check assessment-option">
                                            <input class="form-check-input" type="checkbox"
                                                name="mg_options_{{ $value->id }}"
                                                id="mg_options_{{ $op_value->id }}" value="{{ $op_value->id }}"
                                                required="">
                                            <label class="form-check-label" for="mg_options_{{ $op_value->id }}">
                                                <?= $op_value->option_text ?>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif ($value->question_type == 3)
                                <div class="form-group mg_form">
                                    <div class="question-kicker">Question {{ $key + 1 }} · Short answer</div>
                                    <h5 class="mg_question_detail" data-question-id="{{ $value->id }}"
                                        data-question-type="{{ $value->question_type }}"><?= $value->question_text ?>
                                    </h5>
                                    <textarea class="form-control short-answer-field" id="mg_options_{{ $value->id }}" rows="5"
                                        placeholder="Write your answer here"
                                        name="mg_options_{{ $value->id }}" required=""></textarea>
                                </div>
                            @endif
                        @endforeach

                        @if (count($test_questions) > 0)
                            <div class="assessment-actions">
                                <span class="text-muted">Your answers are saved when you submit.</span>
                                <button type="button" class="btn btn-primary assessment-submit mg_all_submit">Submit Assessment</button>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                No questions are available for this assessment yet. Please contact admin.
                            </div>
                        @endif
                    </form>
                @endif

                @if (isset($_GET['feedback']) && $_GET['feedback'] == 1)




                    @php

                        if (isset($assessment_account->course_id)) {
                            $course_id = $assessment_account->course_id;
                        }
                        if (isset($_GET['course_id'])) {
                            $course_id = $_GET['course_id'];
                        }

                        $courses_feedbacks = DB::table('courses_feedbacks')->where('course_id', $course_id)->get();

                        //echo '<pre>';print_r($courses_feedbacks);die;

                    @endphp


                    @if ($course_id > 0)
                        <form class="" method="POST">
                            <input type="hidden" name="course_id" value="{{ $course_id }}" />
                            @foreach ($courses_feedbacks as $key => $this_data)
                                @php
                                    $value = DB::table('feedback_questions')
                                        ->where('id', $this_data->feedback_question_id)
                                        ->first();

                                    $feedback_option = DB::table('feedback_option')
                                        ->where('question_id', $this_data->feedback_question_id)
                                        ->get();
                                    //echo '<pre>';print_r($courses_feedbacks);die;
                                @endphp


                                @if ($value->question_type == 1)
                                    <div class="form-group mg_form border-bottom py-4 mb-0">
                                        <h5 class="mb-3 mg_question_detail" data-question-id="{{ $value->id }}"
                                            data-question-type="{{ $value->question_type }}"><?= $value->question ?>
                                        </h5>
                                        @foreach ($feedback_option as $op_key => $op_value)
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="mg_options_{{ $value->id }}"
                                                    id="mg_options_{{ $op_value->id }}" value="{{ $op_value->id }}"
                                                    required="">
                                                <label class="form-check-label" for="mg_options_{{ $op_value->id }}">
                                                    <?= $op_value->option_text ?>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif ($value->question_type == 2)
                                    <div class="form-group mg_form border-bottom py-4 mb-0">
                                        <h5 class="mb-3 mg_question_detail" data-question-id="{{ $value->id }}"
                                            data-question-type="{{ $value->question_type }}"><?= $value->question ?>
                                        </h5>
                                        @foreach ($feedback_option as $op_key => $op_value)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    name="mg_options_{{ $value->id }}"
                                                    id="mg_options_{{ $op_value->id }}" value="{{ $op_value->id }}"
                                                    required="">
                                                <label class="form-check-label" for="mg_options_{{ $op_value->id }}">
                                                    <?= $op_value->option_text ?>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif ($value->question_type == 3)
                                    <div class="form-group mg_form py-4">
                                        <h5 class="mb-3 mg_question_detail" data-question-id="{{ $value->id }}"
                                            data-question-type="{{ $value->question_type }}"><?= $value->question ?>
                                        </h5>
                                        <textarea class="form-control" id="mg_options_{{ $value->id }}" rows="3"
                                            name="mg_options_{{ $value->id }}" required=""></textarea>
                                    </div>
                                @endif
                            @endforeach
                            <div class="assessment-actions">
                                <span class="text-muted">Submit your feedback when complete.</span>
                                <button type="button" class="btn btn-primary assessment-submit feedback_submit">Submit Feedback</button>
                            </div>
                        </form>

                    @endif

                @endif




                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/bootstrap.min.js') }}"></script>


    <script>
        var csrf_token = "{{ csrf_token() }}";
        @if (isset($assessment_account->due_date))
            var elapsed_time = '{{ $assessment_account->due_date }}';
        @endif
        var elapsed_url = "{{ route('online_assessment.assignment_test_elapsed_time') }}";
        var submit_url = "{{ route('online_assessment.answer_submit') }}";
        //var home_url = "{{ URL::to('/') }}";
        //var home_url = '{{ url('/online_assessment') }}' + window.location.search + '&feedback=1';
        var home_url = '{{ url('/online_assessment') }}' + window.location.search;
        //console.log('searchParams',home_url);
    </script>

    <script>
        function updateAssessmentProgress() {
            var answered = 0;
            var total = $('.mg_form').length;

            $('.mg_form').each(function() {
                var question_type = $(this).find('.mg_question_detail').attr('data-question-type');
                var isAnswered = false;

                if (question_type == 1 || question_type == 2) {
                    isAnswered = $(this).find('input[name^=mg_options]:checked').length > 0;
                } else if (question_type == 3) {
                    isAnswered = $.trim($(this).find('textarea[name^=mg_options]').val()) !== '';
                }

                $(this).toggleClass('is-answered', isAnswered);
                if (isAnswered) {
                    answered++;
                }
            });

            $('#answered-count').text(answered);
            $('#assessment-progress-fill').css('width', total > 0 ? ((answered / total) * 100) + '%' : '0');
        }

        $(document).on('change keyup', '.mg_form input, .mg_form textarea', updateAssessmentProgress);
        $(document).ready(updateAssessmentProgress);

        function dataCollection() {
            var all_data = $(".mg_form").map(function() {
                var question_id = $(this).first().find(".mg_question_detail").attr('data-question-id');
                var question_type = $(this).first().find(".mg_question_detail").attr('data-question-type');
                if (question_type == 1) {
                    if ($(this).first().find('input[name^=mg_options]:checked').length > 0) {
                        var answer = $(this).first().find('input[name^=mg_options]:checked').val();
                        var is_answered = 1;
                    } else {
                        var answer = "";
                        flag.push(0);
                        var is_answered = 0;
                    }
                } else if (question_type == 2) {
                    var answer = [];
                    $(this).first().find('input[name^=mg_options]:checked').each(function() {
                        answer.push($(this).val());
                    });
                    if (answer.length <= 0) {
                        flag.push(0);
                    }
                    var is_answered = (answer.length > 0 ? 1 : 0);
                } else if (question_type == 3) {
                    var answer = $(this).first().find('textarea[name^=mg_options]').val();
                    if (answer == "") {
                        flag.push(0);
                    }
                    var is_answered = (answer != "" ? 1 : 0);
                }
                return {
                    'question_id': question_id,
                    'question_type': question_type,
                    'answer': question_type == 2 ? JSON.stringify(answer) : answer,
                    'is_answered': is_answered
                };
            });
            return all_data;
        }

        var flag = [];
        $(document).on('click', '.mg_all_submit', function(e) {
            e.preventDefault(); 
            const confirmationText = "Thank you for attending this assessment. We will get back to you with the result soon.\n\nAre you sure you want to submit?";
    
            if (!window.confirm(confirmationText)) {
                return false; 
            }

            $('.mg_all_submit').prop('disabled', true);

            all_data = dataCollection();
            // console.log(all_data)
            $.ajax({
                url: submit_url,
                type: 'post',
                data: {
                    _token: "{{ csrf_token() }}",
                    all_data: JSON.stringify(all_data.get())
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.status == 200) {
                        window.alert(response.message);
                        window.location = response.return_url;
                    }
                },
            });
        });







        $(document).on('click', '.feedback_submit', function() {

            $('.feedback_submit').prop('disabled', true);

            all_data = dataCollection();
            // console.log(all_data)
            $.ajax({
                url: "{{ route('online_assessment.feedback_submit') }}",
                type: 'post',
                data: {
                    _token: "{{ csrf_token() }}",
                    all_data: JSON.stringify(all_data.get()),
                    course_id: "{{ $course_id }}"
                },
                success: function(response) {
                    //console.log('response',response);
                    response = JSON.parse(response);
                    if (response.status == 200) {
                        if (window.confirm(response.message)) {
                            window.location = response.url;
                        }
                    }
                },
            });
        });
    </script>
    <script type="text/javascript" src="{{ asset('assets/assessment/assessment.js') }}"></script>
</body>

</html>
