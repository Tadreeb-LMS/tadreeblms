@extends('backend.layouts.app')
@section('title', __('labels.backend.questions.title') . ' | ' . app_name())

@push('after-styles')
    <link rel="stylesheet" href="{{asset('assets/css/colors/switch.css')}}">
    
    <style>
 div.cb_question_setup div.child {
    margin-top: 15px !important;
 }
    </style>
@endpush
@section('content')
    {{-- {!! Form::open(['method' => 'POST', 'route' => ['admin.questions.store'], 'files' => true]) !!} --}}

    <div class="pb-3 d-flex justify-content-between align-items-center">
        <h4 class="">Feedback Question</h4>
        <div class="">
            <a href="{{ route('admin.feedback_question.index') }}" class="btn add-btn">View Feedback Questions</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div>Question Type</div>
                    <div class="mt-2 custom-select-wrapper">

                        <select class="form-control custom-select-box" name="question_type" id="question_type">
                            <option value="1" @if ($feedbackQuestion->question_type == 1) selected @endif> Single Choice </option>
                            <option value="2" @if ($feedbackQuestion->question_type == 2) selected @endif> Multiple Choice </option>
                            <option value="3" @if ($feedbackQuestion->question_type == 3) selected @endif> Short Answer </option>
                        </select>
                                 <span class="custom-select-icon">
        <i class="fa fa-chevron-down"></i>
    </span>
                    </div>
                </div>
            </div>
            <div class="cb_question_setup mt-2">
                <div class="row">
                    <div class="col-12 mb-3 child">
                        <label>Question</label>
                        <textarea class="form-control editor" rows="3" name="question" id="question" required="required"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6 child">
                        <label>Option</label>
                        <textarea class="form-control editor" rows="3" name="option" id="option" required="required"></textarea>

                        <button type="button" id="add_option" class="btn btn-primary pull-right mt-2">Add
                            Option</button>
                    </div>
                    <div class="col-12 col-md-6 child">
                        <div id="option-area" class="pt-4"></div>
                    </div>
                </div>
                <!-- <div class="row">
                                                                    <div class="col-12">
                                                                        <label>Solution</label>
                                                                        <textarea class="form-control textarea-col editor" rows="3" name="solution" id="solution"></textarea>
                                                                    </div>
                                                                </div> -->
            </div>
        </div>
        <div class="col-12 text-right">
            <button class="btn add-btn mb-4 form-group" id="save" type="button">{{ trans('strings.backend.general.app_save') }}</button>
        </div>
    </div>


    {{-- {!! Form::close() !!} --}}
    <script src="{{ asset('ckeditor/ckeditor.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        CKEDITOR.replace('question');
        CKEDITOR.replace('option');
        CKEDITOR.replace('solution');
        CKEDITOR.replace('comment')
    </script>
@stop
@push('after-scripts')
    <script type="text/javascript">
        setTimeout(() => {
            $('#question_type').trigger('change');
        }, 2000);
        var options = [];
        if ('{{ $feedbackQuestion->option_json }}') {
            options = @json(json_decode($feedbackQuestion->option_json));
        }
        var flag = 0;

        function removeOptions(pos) {
            options.splice(pos, 1);
            showOptions();
        }

        function markAsCorrectOption(pos, show_remove_options = true) {
            for (var i = 0; i < options.length; ++i) {
                if ($('#question_type').val() == 1) {
                    if (i === pos) {
                        options[i][1] = 1;
                    } else {
                        options[i][1] = 0;
                    }
                } else {
                    if (i === pos) {
                        if (options[i][1] == 1) {
                            options[i][1] = 0;
                        } else {
                            options[i][1] = 1;
                        }
                    } else {
                        options[i][1] = options[i][1];
                    }
                }
            }
            showOptions(show_remove_options);
        }

        function showOptions(show_remove_options = true) {
            if (show_remove_options == true) {
                var option_text = '<table class="table table-bordered table-striped"><tbody><tr><th>Option</th>';
                var drag_drop_question_type = $('#question_type').val();
                option_text += '<th style="display:none">Is Right</th></tr>';
                for (var i = 0; i < options.length; ++i) {
                    option = options[i];
                    option_text += '<tr>';
                    option_text += '<td>' + option[0] + '</td>';
                    if (parseInt($('#question_type').val()) == 1) {
                        option_text += '<td style="display:none"><input type="radio" ';
                    } else {
                        option_text += '<td style="display:none"><input type="checkbox" class="cb_checkbox_mark" ';
                    }
                    if (option[1] === 1) {
                        option_text += 'checked="checked"';
                    }
                    option_text += ' onclick="markAsCorrectOption(' + i + ')" style="display:none"></td>';
                    option_text += '<td><a href="javascript:void(0);"  onclick="removeOptions(' + i +
                        ')" class="btn btn-danger remove"><i class="la la-trash"></i>Remove</a>';
                    option_text += '</tr>'
                }
                option_text += '</tbody></table>';
                $('#option-area').html(option_text);
            } else {
                var option_text = '<table class="table table-bordered table-striped"><tbody><tr><th>Option</th></tr>';
                for (var i = 0; i < options.length; ++i) {
                    option = options[i];
                    option_text += '<tr>';
                    option_text += '<td>' + option[0] + '</td>';
                    option_text += '<td style="display:none"><input type="radio" ';
                    if (option[1] === 1) {
                        option_text += 'checked="checked"';
                    }
                    option_text += ' onclick="markAsCorrectOption(' + i + ',false)"></td>';
                    option_text += '</tr>'
                }
                option_text += '</tbody></table>';
                document.getElementById('option-area').innerHTML = option_text;
            }
            addImgClass();
        }

        function addOptions() {
            var option = CKEDITOR.instances["option"].getData();
            options_length = (options != null && options != undefined) ? options.length : 0;
            options.push([option.trim(), 0]);
            CKEDITOR.instances["option"].setData('');
            console.log('options', options);
        }

        $(document).on('click', "#add_option", function() {
            if (CKEDITOR.instances["option"].getData() != "") {
                // if ((options.length + 1) <= 4) {
                addOptions();
                // } else {
                //     alert('You can use only 4 Options.');
                // }
            }
            showOptions();
        });

        function addImgClass() {
            $('#option-area').each(function() {
                $(this).find('img').addClass('img-fluid');
            });
        }

        function dataCollection() {
            var test_id = $("#test_id").val();
            var question_type = $("#question_type").val();
            var question = CKEDITOR.instances["question"].getData();

            var solution = CKEDITOR?.instances["solution"]?.getData();
            // var comment = CKEDITOR.instances["comment"].getData();
            // var marks = $("#marks").val();
            return {
                test_id,
                question_type,
                question,
                options: JSON.stringify(options),
                solution,
                // comment,
                // marks
            }
        }

        $(document).on('click', "#save", function() {
            flag = 0;
            if (CKEDITOR.instances["question"].getData() != "" && $('#marks').val() != "" && $('#test_id').val() !=
                "") {

                sendData();
            }
        });

        var question_submit_url = "{{ route('admin.feedback_question.update', ['id' => request()->id]) }}";

        function sendData(data) {
            var data = dataCollection();
            data['_token'] = "{{ csrf_token() }}";
            $.ajax({
                url: question_submit_url,
                type: 'post',
                data: data,
                success: function(response) {
                    alert('Feedback question updated successfully');
                    window.location.href = '/user/feedback-questions';
                },
            });
        }

        $(document).on('change', '#question_type', function() {

            var question_type = $(this).val();
            $.ajax({
                url: "{{ route('admin.test_questions.question_setup_feedback') }}",
                type: 'post',
                data: ({
                    id: '{{ request()->id }}',
                    question_type: question_type,
                    _token: "{{ csrf_token() }}"
                }),
                success: function(response) {
                    $('.cb_question_setup').html(response);

        
                    showOptions();
                },
            });
        });
    </script>
@endpush
