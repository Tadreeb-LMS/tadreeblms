<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        .box-shadow {
            box-shadow: 0 2px 15px rgb(0 0 0 / 20%);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <!-- <h5>Time Remaining :<b> <span id="time_remaining">0h 0m 0s</span></b></h5> -->
        </div>
        <div class="row">
            <div class="col-9 box-shadow m-auto p-4">
                <form class="" method="POST">
                    <input type="hidden" name="course_id" value="{{ $course_id }}" />
                    @foreach ($courses_feedbacks as $key => $this_data)
                        @php
                            // Use Eloquent relationship instead of raw DB queries for better performance and security
                            $feedbackQuestion = $this_data->feedback;
                            $value = $feedbackQuestion;
                            
                            // Get feedback options - check if question type supports options
                            $feedback_option = [];
                            if (in_array($value->question_type, [1, 2])) {
                                $feedback_option = $value->feedbackOptions ?? [];
                            }
                        @endphp


                        @if ($value->question_type == 1)
                            <div class="form-group mg_form border-bottom py-4 mb-0">
                                <h5 class="mb-3 mg_question_detail" data-question-id="{{ $value->id }}"
                                    data-question-type="{{ $value->question_type }}"><?= $value->question ?></h5>
                                @foreach ($feedback_option as $op_key => $op_value)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                            name="mg_options_{{ $value->id }}" id="mg_options_{{ $op_value->id }}"
                                            value="{{ $op_value->id }}" required="">
                                        <label class="form-check-label" for="mg_options_{{ $op_value->id }}">
                                            <?= $op_value->option_text ?>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @elseif ($value->question_type == 2)
                            <div class="form-group mg_form border-bottom py-4 mb-0">
                                <h5 class="mb-3 mg_question_detail" data-question-id="{{ $value->id }}"
                                    data-question-type="{{ $value->question_type }}"><?= $value->question ?></h5>
                                @foreach ($feedback_option as $op_key => $op_value)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            name="mg_options_{{ $value->id }}" id="mg_options_{{ $op_value->id }}"
                                            value="{{ $op_value->id }}" required="">
                                        <label class="form-check-label" for="mg_options_{{ $op_value->id }}">
                                            <?= $op_value->option_text ?>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @elseif ($value->question_type == 3)
                            <div class="form-group mg_form py-4">
                                <h5 class="mb-3 mg_question_detail" data-question-id="{{ $value->id }}"
                                    data-question-type="{{ $value->question_type }}"><?= $value->question ?></h5>
                                <textarea class="form-control" id="mg_options_{{ $value->id }}" rows="3"
                                    name="mg_options_{{ $value->id }}" required=""></textarea>
                            </div>
                        @endif
                    @endforeach
                    <button type="button" class="btn btn-primary feedback_submit">Submit</button>
                </form>
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
        var home_url = '{{ url('/online_assessment') }}' + window.location.search + '&feedback=1';
        //console.log('searchParams',home_url);
    </script>

    <script>
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
                    'is_answered': is_answered,
                };
            });
            return all_data;
        }

        var flag = [];
        $(document).on('click', '.mg_all_submit', function() {

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
                        if (window.confirm(response.message)) {
                            window.location = home_url;
                        } else {
                            window.location = home_url;
                        }
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
                        } else {
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
