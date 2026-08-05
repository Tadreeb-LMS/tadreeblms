@extends('backend.layouts.app')
@section('title', __('labels.backend.questions.title').' | '.app_name())

@section('content')
{{-- {!! Form::open(['method' => 'POST', 'route' => ['admin.questions.store'], 'files' => true,]) !!} --}}

@push('after-styles')
<link rel="stylesheet" href="{{asset('assets/css/colors/switch.css')}}">
<style>
    .card{
        border-radius:12px;
    }
    label{
        font-weight:600;
        margin-bottom:8px;
    }
    #option-area table{
        margin-top:20px;
    }
    #option-area th{
        background:#f8f9fa;
        font-weight:600;
    }
    #option-area td{
        vertical-align:middle;
    }
    .input-group .btn{
        min-width:140px;
    }
    .btn-outline-danger{
        border-radius:6px;
    }
    .option-delete-btn
    {
        width:40px;
        height:40px;
        display:flex;
        align-items:center;
        justify-content:center;
        border:1px solid #ff5c5c;
        color:#ff5c5c;
        background:#fff;
        border-radius:8px;
        transition:.2s;
    }
    .option-delete-btn:hover
    {
        background:#ff5c5c;
        color:#fff;
    }
    .question-info-box
    {
        background:#f5f9ff;
        border:1px solid #b8d4ff;
        border-left:4px solid #3b82f6;
        border-radius:10px;
        padding:18px 20px;
        min-height:90px;
        display:flex;
        align-items:center;
    }
    .info-icon
    {
        width:38px;
        height:38px;
        border-radius:50%;
        background:#e8f2ff;
        color:#2563eb;
        display:flex;
        align-items:center;
        justify-content:center;
        margin-right:15px;
        font-size:18px;
    }
    .info-title
    {
        font-weight:600;
        color:#1f2937;
        margin-bottom:4px;
    }
    .info-text
    {
        color:#6b7280;
        font-size:14px;
    }
</style>
@endpush

<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4>
        Feedback Question
    </h4>

    <div class="">
        <a href="{{ route('admin.feedback_question.index') }}" class="btn add-btn">View Feedback Questions</a>
    </div>

</div>
<div class="card">

    <div class="">
        <!-- <div class="card-header">
        <h3 class="page-title float-left mb-0">Feedback Question</h3>
        <div class="float-right">
            <a href="{{ route('admin.feedback_question.index') }}" class="btn btn-success">View Feedback Questions</a>
        </div>
    </div> -->
        <div class="card-body">
            @if(isset($course->id))
            <div class="row">
                <div class="col-12">
                    <label>Course Name </label>
                    <input type="text" value="{{ $course->title }}" class="form-control">
                    <input type="hidden" id="course_id" name="course_id" value="{{ $course->id }}">

                </div>
            </div>
            @endif
            <div class="row align-items-stretch mb-4">
                <!-- Left Side -->

                <div class="col-lg-5">
                    <label class="font-weight-bold">
                        Question Type
                        <span class="text-danger">*</span>
                    </label>

                    <div class="custom-select-wrapper mt-2">
                        <select class="form-control custom-select-box"
                                name="question_type"
                                id="question_type">
                            <option value="1">Single Choice</option>
                            <option value="2">Multiple Choice</option>
                            <option value="3">Short Answer</option>
                        </select>
                        <span class="custom-select-icon">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="col-lg-7">
                    <div class="question-info-box">
                        <div class="d-flex">
                            <div class="info-icon">
                                <i class="fa fa-info-circle"></i>
                            </div>

                            <div>
                                <div class="info-title">
                                    Select the type of question you want to create.
                                </div>
                                <div class="info-text">
                                    The available fields will adjust based on your selection.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cb_question_setup mt-2">
                <div class="row">
                    <div class="col-12 mt-2">
                        <label>Question</label>
                        <textarea class="form-control editor" rows="3" name="question" id="question" required="required"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 mt-3">
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="font-weight-bold">
                                    Options <span class="text-danger">*</span>
                                </label>

                                <div class="input-group mt-2">
                                    <input
                                        type="text"
                                        id="option"
                                        class="form-control"
                                        placeholder="Enter option text">
                                    <div class="input-group-append">
                                        <button
                                            type="button"
                                            id="add_option"
                                            class="btn btn-primary">
                                            <i class="fa fa-plus"></i>
                                            Add Option
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div id="option-area"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
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
    </div>

    <div class="col-12 text-right">
        <button class="btn add-btn mb-4 form-group" id="save" type="button">{{ trans('strings.backend.general.app_save') }}</button>
    </div>

    {{-- {!! Form::close() !!} --}}
    <script src="{{asset('ckeditor/ckeditor.js')}}" type="text/javascript"></script>
    <script type="text/javascript">
        CKEDITOR.replace('question');
        // CKEDITOR.replace('question', {
        //     toolbar: [{
        //             name: 'clipboard',
        //             groups: ['clipboard', 'undo'],
        //             items: ['PasteFromWord', '-', 'Undo', 'Redo']
        //         },
        //         {
        //             name: 'editing',
        //             groups: ['find', 'selection', 'spellchecker'],
        //             items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt']
        //         },
        //         {
        //             name: 'forms',
        //             items: ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField']
        //         },
        //         {
        //             name: 'basicstyles',
        //             groups: ['basicstyles', 'cleanup'],
        //             items: ['Bold', 'Italic', 'Subscript', 'Superscript', '-', 'RemoveFormat']
        //         },
        //         {
        //             name: 'paragraph',
        //             groups: ['list', 'indent', 'blocks', 'bidi'],
        //             items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'BidiLtr', 'BidiRtl', 'Language']
        //         },
        //         {
        //             name: 'links',
        //             items: ['Link', 'Unlink']
        //         },
        //         {
        //             name: 'insert',
        //             items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'Iframe']
        //         },
        //         {
        //             name: 'colors',
        //             items: ['TextColor', 'BGColor']
        //         },
        //         {
        //             name: 'tools',
        //             items: ['Maximize', 'ShowBlocks']
        //         },
        //         {
        //             name: 'others',
        //             items: ['-']
        //         },
        //         {
        //             name: 'about',
        //             items: ['About']
        //         }
        //     ]
        // });
        // CKEDITOR.replace('option', {
        //     toolbar: [{
        //             name: 'clipboard',
        //             groups: ['clipboard', 'undo'],
        //             items: ['PasteFromWord', '-', 'Undo', 'Redo']
        //         },
        //         {
        //             name: 'editing',
        //             groups: ['find', 'selection', 'spellchecker'],
        //             items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt']
        //         },
        //         {
        //             name: 'forms',
        //             items: ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField']
        //         },
        //         {
        //             name: 'basicstyles',
        //             groups: ['basicstyles', 'cleanup'],
        //             items: ['Bold', 'Italic', 'Subscript', 'Superscript', '-', 'RemoveFormat']
        //         },
        //         {
        //             name: 'paragraph',
        //             groups: ['list', 'indent', 'blocks', 'bidi'],
        //             items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'BidiLtr', 'BidiRtl', 'Language']
        //         },
        //         {
        //             name: 'links',
        //             items: ['Link', 'Unlink']
        //         },
        //         {
        //             name: 'insert',
        //             items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'Iframe']
        //         },
        //         {
        //             name: 'colors',
        //             items: ['TextColor', 'BGColor']
        //         },
        //         {
        //             name: 'tools',
        //             items: ['Maximize', 'ShowBlocks']
        //         },
        //         {
        //             name: 'others',
        //             items: ['-']
        //         },
        //         {
        //             name: 'about',
        //             items: ['About']
        //         }
        //     ]
        // });
        CKEDITOR.replace('solution');
        // CKEDITOR.replace('solution', {
        //     toolbar: [{
        //             name: 'clipboard',
        //             groups: ['clipboard', 'undo'],
        //             items: ['PasteFromWord', '-', 'Undo', 'Redo']
        //         },
        //         {
        //             name: 'editing',
        //             groups: ['find', 'selection', 'spellchecker'],
        //             items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt']
        //         },
        //         {
        //             name: 'forms',
        //             items: ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField']
        //         },
        //         {
        //             name: 'basicstyles',
        //             groups: ['basicstyles', 'cleanup'],
        //             items: ['Bold', 'Italic', 'Subscript', 'Superscript', '-', 'RemoveFormat']
        //         },
        //         {
        //             name: 'paragraph',
        //             groups: ['list', 'indent', 'blocks', 'bidi'],
        //             items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'BidiLtr', 'BidiRtl', 'Language']
        //         },
        //         {
        //             name: 'links',
        //             items: ['Link', 'Unlink']
        //         },
        //         {
        //             name: 'insert',
        //             items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'Iframe']
        //         },
        //         {
        //             name: 'colors',
        //             items: ['TextColor', 'BGColor']
        //         },
        //         {
        //             name: 'tools',
        //             items: ['Maximize', 'ShowBlocks']
        //         },
        //         {
        //             name: 'others',
        //             items: ['-']
        //         },
        //         {
        //             name: 'about',
        //             items: ['About']
        //         }
        //     ]
        // });
        CKEDITOR.replace('comment')
        // CKEDITOR.replace('comment', {
        //     toolbar: [{
        //             name: 'clipboard',
        //             groups: ['clipboard', 'undo'],
        //             items: ['PasteFromWord', '-', 'Undo', 'Redo']
        //         },
        //         {
        //             name: 'editing',
        //             groups: ['find', 'selection', 'spellchecker'],
        //             items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt']
        //         },
        //         {
        //             name: 'forms',
        //             items: ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField']
        //         },
        //         {
        //             name: 'basicstyles',
        //             groups: ['basicstyles', 'cleanup'],
        //             items: ['Bold', 'Italic', 'Subscript', 'Superscript', '-', 'RemoveFormat']
        //         },
        //         {
        //             name: 'paragraph',
        //             groups: ['list', 'indent', 'blocks', 'bidi'],
        //             items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'BidiLtr', 'BidiRtl', 'Language']
        //         },
        //         {
        //             name: 'links',
        //             items: ['Link', 'Unlink']
        //         },
        //         {
        //             name: 'insert',
        //             items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'Iframe']
        //         },
        //         {
        //             name: 'colors',
        //             items: ['TextColor', 'BGColor']
        //         },
        //         {
        //             name: 'tools',
        //             items: ['Maximize', 'ShowBlocks']
        //         },
        //         {
        //             name: 'others',
        //             items: ['-']
        //         },
        //         {
        //             name: 'about',
        //             items: ['About']
        //         }
        //     ]
        // });
    </script>
    @stop
    @push('after-scripts')
    <script type="text/javascript">
        var options = [];
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

            var option_text = `
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>Option Text</th>
                            <th width="120" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            for (var i = 0; i < options.length; i++) {
                option_text += `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${options[i][0]}</td>
                        <td class="text-center">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger"
                                onclick="removeOptions(${i})">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }

            option_text += `
                    </tbody>
                </table>
            `;

            $('#option-area').html(option_text);
            addImgClass();
        }

        function addOptions() {
            var option = $('#option').val().trim();
            if(option == '')
                return;

            options.push([option,0]);
            $('#option').val('');
        }

        $(document).on('click', "#add_option", function() {
            if($('#option').val().trim() != '')
            {
                addOptions();
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
            var course_id = $("#course_id").val();

            var solution = CKEDITOR?.instances["solution"]?.getData();
            // var comment = CKEDITOR.instances["comment"].getData();
            // var marks = $("#marks").val();
            return {
                test_id,
                question_type,
                course_id,
                question,
                options: JSON.stringify(options),
                solution,
                // comment,
                // marks
            }
        }

        $(document).on('click', "#save", function() {
            flag = 0;
            if (CKEDITOR.instances["question"].getData() != "" && $('#marks').val() != "" && $('#test_id').val() != "") {

                sendData();
            }
        });

        var question_submit_url = "{{route('admin.feedback.feedback-question-multiple-store')}}";

        function sendData(data) {
            var data = dataCollection();
            data['_token'] = "{{ csrf_token() }}";
            $.ajax({
                url: question_submit_url,
                type: 'post',
                data: data,
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.code == 200) {

                        //window.location.replace("{{ URL::to('user/feedback-questions')}}/" + response.course_id);
                        window.location.replace("{{ URL::to('user/course-feedback-create')}}?course_id={{(isset($course->id) ? $course->id:0)}}");
                    } else {
                        alert(response.message);
                    }
                },
            });
        }

        $(document).on('change', '#question_type', function() {

            var question_type = $(this).val();
            $.ajax({
                url: "{{route('admin.test_questions.question_setup_feedback')}}",
                type: 'post',
                data: ({
                    question_type: question_type,
                    _token: "{{ csrf_token() }}"
                }),
                success: function(response) {
                    $('.cb_question_setup').html(response);
                },
            });
        });
    </script>
    @endpush