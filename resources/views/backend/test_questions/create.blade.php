@extends('backend.layouts.app')
@section('title', __('labels.backend.questions.title').' | '.app_name())

@section('content')
<!-- {!! Form::open(['method' => 'POST', 'route' => ['admin.questions.store'], 'files' => true,]) !!} -->
<style>
.col-12.mt-5.buttons {
    display: flex;
    justify-content: space-between;
}
.frm_submit {
    cursor: pointer;
}
</style>



@include('backend.includes.partials.course-steps', ['step' => 3, 'course_id' => $course_id, 'course' => $course ?? null ])


<div class="pb-3 d-flex justify-content-between align-items-center">
       <h4>
           @lang('labels.backend.questions.create')
       </h4>
         <div class="">
       <a href="{{ route('admin.test_questions.index') }}" class="btn btn-primary">@lang('labels.backend.questions.view')</a>
   </div>
     
   </div>
<div class="card">
    <!-- <div class="card-header">
        <h3 class="page-title float-left mb-0">@lang('labels.backend.questions.create')</h3>
        <div class="float-right">
            <a href="{{ route('admin.test_questions.index') }}" class="btn btn-success">@lang('labels.backend.questions.view')</a>
        </div>
    </div> -->
    <div class="card-body">
        <input type="hidden" id="temp_id" name="temp_id" value="{{ $temp_id }}">
        <input type="hidden" id="action_btn" name="action_btn" value="">
        <input type="hidden" id="course_id" name="course_id" value="{{ $course_id }}">
        <div class="row">
            <div class="col-12">
                <label>Test</label>
                <div class="custom-select-wrapper">

                    <select @if($auto_test_id) disabled @endif class="form-control custom-select-box" name="test_id" id="test_id" required>
                        <option value="">Select Test</option>
                        @foreach($tests as $key=> $value)
                        <option @if((request()->get('test_id') == $value->id) || ($auto_test_id == $value->id)) selected @endif value="{{$value->id}}">{{$value->title}}</option>
                        @endforeach
                    </select>
                     <span class="custom-select-icon">
        <i class="fa fa-chevron-down"></i>
    </span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 mt-3">
                <label>Question Type</label>
                <div class="custom-select-wrapper">

                    <select class="form-control custom-select-box" name="question_type" id="question_type">
                        <option value="1"> Single Choice </option>
                        {{-- <option value="2"> Multiple Choice </option>
                        <option value="3"> Short Answer </option> --}}
                    </select>
                     <span class="custom-select-icon">
        <i class="fa fa-chevron-down"></i>
    </span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 mt-3">
                <label>Question</label>
                <textarea class="form-control editor" rows="3" name="question" id="question" required="required"></textarea>
            </div>
        </div>
        <div class="cb_question_setup">
            
            <div class="row">
                <div class="col-12 col-md-6 mt-3">
                    <label>Option</label>
                    <textarea class="form-control editor" rows="3" name="option" id="option" required="required"></textarea>
                    <button type="button" id="add_option" class="btn btn-primary pull-right mt-2">Add Option</button>
                </div>
                <div class="col-12 col-md-6">
                    <div id="option-area" class="pt-4"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mt-3">
                    <label>Solution</label>
                    <textarea class="form-control textarea-col editor" rows="3" name="solution" id="solution"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mt-3">
                    <label>Marks</label>
                    <input type="number" class="form-control" name="marks" id="marks" placeholder="Enter Marks" required />
                </div>
            </div>
            <div class="row">
                <div class="col-12 mt-3">
                    <label>Comment</label>
                    <textarea class="form-control textarea-col editor" rows="3" name="comment" id="comment"></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 mt-5 buttons">
     {!! Form::button('Save & Add More', ['class' => 'frm_submit add-btn form-group', 'id'=>'save_and_add_more', 'value'=>'save_and_add_more']) !!}
     <div class="text-right">
        <button
            type="button"
            class="frm_submit cancel-btn mb-4 form-group"
            id="save_as_draft"
            value="Save As Draft">
            Save As Draft
        </button>

        <button
            type="button"
            class="frm_submit add-btn form-group"
            id="save"
            value="Next">
            Next
        </button>
    </div>
    </div>
    
</div>


<!-- {!! Form::close() !!} -->
<script src="{{asset('ckeditor/ckeditor.js')}}" type="text/javascript"></script>
<script type="text/javascript">

    $('.frm_submit').on('click', function (){
        //alert($(this).val())
        $('#action_btn').val($(this).val());
    });

    CKEDITOR.replace('question');
    
    CKEDITOR.replace('option');
    
    CKEDITOR.replace('solution');
    
    CKEDITOR.replace('comment')
    
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
        if (show_remove_options == true) {
            var option_text = '<table class="table table-bordered table-striped"><tbody><tr><th>Option</th>';
            var drag_drop_question_type = $('#question_type').val();
            option_text += '<th>Is Right</th></tr>';
            for (var i = 0; i < options.length; ++i) {
                option = options[i];
                option_text += '<tr>';
                option_text += '<td>' + option[0] + '</td>';
                if (parseInt($('#question_type').val()) == 1) {
                    option_text += '<td><input type="radio" ';
                } else {
                    option_text += '<td><input type="checkbox" class="cb_checkbox_mark" ';
                }
                if (option[1] === 1) {
                    option_text += 'checked="checked"';
                }
                option_text += ' onclick="markAsCorrectOption(' + i + ')"></td>';
                option_text += '<td><a href="javascript:void(0);"  onclick="removeOptions(' + i + ')" class="btn btn-danger remove"><i class="la la-trash"></i>Remove</a>';
                option_text += '</tr>'
            }
            option_text += '</tbody></table>';
            $('#option-area').html(option_text);
        } else {
            var option_text = '<table class="table table-bordered table-striped"><tbody><tr><th>Option</th><th>Is Right</th></tr>';
            for (var i = 0; i < options.length; ++i) {
                option = options[i];
                option_text += '<tr>';
                option_text += '<td>' + option[0] + '</td>';
                option_text += '<td><input type="radio" ';
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
        var temp_id = $("#temp_id").val();
        var test_id = $("#test_id").val();
        var question_type = $("#question_type").val();
        var question = CKEDITOR.instances["question"].getData();
        var solution = CKEDITOR.instances["solution"].getData();
        var comment = CKEDITOR.instances["comment"].getData();
        var marks = $("#marks").val();
        return {
            temp_id,
            test_id,
            question_type,
            question,
            options: JSON.stringify(options),
            solution,
            comment,
            marks
        }
    }

    $(document).on('click', ".frm_submit", function() {
        flag = 0;
        sendData();
        /*
        if (CKEDITOR.instances["question"].getData() != "" && $('#marks').val() != "" && $('#test_id').val() != "") {
            if ($('#question_type').val() == 1) {
                if ($('input:radio:checked').length > 0) {
                    sendData();
                } else {
                    alert('Please Select The Right Answer.');
                }
            } else if ($('#question_type').val() == 2) {
                if ($('input:checkbox:checked').length > 0) {
                    sendData();
                } else {
                    alert('Please Select at least one right Answer.');
                }
            } else {
                sendData();
            }
        } else {
            alert("Please fill all the details.");
        }
        */
    });

    var question_submit_url = "{{route('admin.test_questions.store')}}";

    function sendData(data) {
        var data = dataCollection();
        data['_token'] = "{{ csrf_token() }}";
        data['action_btn'] = $('#action_btn').val();
        data['course_id'] = $('#course_id').val();
        const redirect = "{{ request()->redirect }}";
        $.ajax({
            url: question_submit_url,
            type: 'post',
            data: data,
            success: function(response) {
                response = JSON.parse(response);
                if (response.code == 200) {
                    // if (data['action_btn'] == 'save_and_add_more') {
                    //     window.location.replace(response.redirect_url);
                    // }else{
                    //     window.location.replace(redirect);
                    // }
                    window.location.replace(response.redirect_url);
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    let res = xhr.responseJSON;
                    alert(res.message); // Show the error message
                    console.log('Validation errors:', res.errors);
                }
            }
        });
    }

    $(document).on('change', '#question_type', function() {
        var question_type = $(this).val();
        $.ajax({
            url: "{{route('admin.test_questions.question_setup')}}",
            type: 'post',
            data: ({
                question_type: question_type,
                _token: "{{ csrf_token() }}"
            }),
            success: function(response) {
                $('.cb_question_setup').html(response);
                // if (response.code == 200) {
                //     $('.cb_question_setup').html(response.question_setup);
                // } else {
                //     alert(response.message);
                // }
                // console.log(response);
            },
        });
    });
</script>
@endpush