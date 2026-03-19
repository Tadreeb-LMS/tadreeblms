@extends('backend.layouts.app')
@section('title', __('labels.backend.questions.title').' | '.app_name())

@section('content')
<form method="POST" action="{{ route('admin.test_questions.store') }}" enctype="multipart/form-data">
@csrf
@push('after-styles')
<style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #858796;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --light-color: #f8f9fc;
        --dark-color: #5a5c69;
        --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .question-builder-container {
        padding: 20px 0;
    }
 

    .card-header {
        background-color: #fff !important;
        border-bottom: 1px solid #f2f4f9 !important;
        border-radius: 12px 12px 0 0 !important;
        padding: 1.25rem !important;
    }

    .card-header h5 {
        color: #4e73df;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group label {
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .form-control {
        border-radius: 8px;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
    }

    /* Options Builder Styling */
    .option-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 15px;
        transition: all 0.2s;
        position: relative;
    }

    .option-item:hover {
        border-color: #cbd5e0;
        background: #f1f5f9;
        transform: translateY(-2px);
    }

    .option-item .drag-handle {
        cursor: grab;
        color: #cbd5e0;
    }

    .option-item .option-content {
        flex-grow: 1;
        font-size: 0.95rem;
    }

    .option-item .option-actions {
        display: flex;
        gap: 8px;
    }

    .option-item.correct {
        border-left: 4px solid #1cc88a;
        background: #f0fff4;
    }

    /* Floating Preview Styling */
    .preview-panel {
        position: sticky;
        top: 20px;
        z-index: 100;
    }

    .preview-card {
        background: #fff;
        border-top: 4px solid #4e73df;
    }

    .preview-header {
        background: #f8fafc;
        padding: 15px;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 700;
        color: #2d3748;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
    }

    .preview-body {
        padding: 24px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .badge-difficulty {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-easy { background: #def7ec; color: #03543f; }
    .badge-medium { background: #fef3c7; color: #92400e; }
    .badge-hard { background: #fde8e8; color: #9b1c1c; }

    /* Sticky Footer */
    .sticky-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        padding: 15px 30px;
        box-shadow: 0 -5px 20px rgba(0,0,0,0.05);
        z-index: 1000;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #e2e8f0;
    }

    .main {
        padding-bottom: 80px !important; /* Space for footer */
    }

    /* Custom Radio/Checkbox */
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #4e73df;
        border-color: #4e73df;
    }

    /* Helper spacing */
    .gap-10 { gap: 10px; }
    .mt-auto { margin-top: auto; }
</style>
@endpush




@include('backend.includes.partials.course-steps', ['step' => 3, 'course_id' => $course_id, 'course' => $course ?? null ])


 <div class="pb-3 d-flex justify-content-between align-items-center addcourseheader">
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
        <div class="row mt-3">
            <div class="col-12 col-md-6">
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
         
            <div class="col-12 col-md-6">
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
          <div class="col-12 col-md-6 mt-3 notextarea"> 
                <label>Question</label>
                <textarea class="form-control editor" rows="3" name="question" id="question" required="required"></textarea>
            </div>
         
                <div class="col-12 col-md-6"> 
                     <div class="mt-3 notextarea">
                    <label>Option</label>
                    <textarea class="form-control editor" rows="3" name="option" id="option" required="required"></textarea>
                    <div class="addoptbtn">
                    <button type="button" id="add_option" class="btn btn-primary mt-2">Add Option</button>
                </div>
              <div class="addoptiontable ">
                    <div id="option-area" class=""></div>
                </div>
               </div>
            </div>
</div>


            <div class="row">
                 <div class="col-12 col-md-5 notextarea">
                    <label>Solution</label>
                    <textarea class="form-control textarea-col editor" rows="3" name="solution" id="solution"></textarea>
                </div>
             
             <div class="col-12 col-md-2">
                    <label>Marks</label>
                    <input type="number" 
                        class="form-control" 
                        name="score" 
                        id="score" 
                        placeholder="Enter Marks"  
                        min="1"       
                        max="999"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,3);"
                        required />
                </div>
             
                <div class="col-12 col-md-5 notextarea">
                    <label>Comment</label>
                    <textarea class="form-control textarea-col editor" rows="3" name="comment" id="comment"></textarea>

         

         
        </div>
        </div>


     <div class="btmbtns">
        <div class="row">
    <div class="col-12 mt-5 buttons">
        
     <button type="button" class="frm_submit add-btn" id="save_and_add_more" value="save_and_add_more">Save & Add More</button>
    
     <span class="text-right pull-right">
        <button
            type="button"
            class="frm_submit cancel-btn"
            id="save_as_draft"
            value="Save As Draft">
            Save As Draft
        </button>

        <button
            type="button"
            class="frm_submit add-btn"
            id="save"
            value="Next">
            Next
        </button>
</span>
    </div>
    
</div>
</div>



</form>
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
        var score = $("#score").val();
        return {
            temp_id,
            test_id,
            question_type,
            question,
            options: JSON.stringify(options),
            solution,
            comment,
            score
        }
    }

    $(document).on('click', ".frm_submit", function() {
        flag = 0;
        sendData();
       
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
                    // Show the error message
                    if (res.errors && res.errors.score) {
                        alert(res.errors.score[0]); // 🔥 shows: Marks cannot exceed 999
                    } else {
                        alert(res.message);
                    }
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
