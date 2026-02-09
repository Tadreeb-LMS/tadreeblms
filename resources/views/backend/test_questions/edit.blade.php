@extends('backend.layouts.app')
@section('title', __('labels.backend.questions.title').' | '.app_name())

@section('content')
<!-- {!! Form::open(['method' => 'POST', 'route' => ['admin.questions.store'], 'files' => true,]) !!} -->

<div class="pb-3 d-flex justify-content-between align-items-center addcourseheader">
       <h4>
           @lang('labels.backend.questions.edit')
       </h4>
       <div >
       <a href="{{ route('admin.test_questions.index') }}" class="btn add-btn">@lang('labels.backend.questions.view')</a>
   </div>
     
   </div>
<div class="card">
    <!-- <div class="card-header">
        <h3 class="page-title float-left mb-0">@lang('labels.backend.questions.edit')</h3>
        <div class="float-right">
            <a href="{{ route('admin.test_questions.index') }}" class="btn btn-success">@lang('labels.backend.questions.view')</a>
        </div>
    </div> -->
    <div class="card-body">
    <input type="hidden" name="edit_id" id="edit_id" value="@if(isset($question->id)){{$question->id}}@endif">

    @if(isset($question->id))
   
    @php 
      $has_option = 0;
       $optiions = $question->option_json ? json_decode($question->option_json) : [];
       if(count($optiions) > 0) {
         $has_option = 1;
       } 
    @endphp
    @endif
    <input type="hidden" name="has_option" id="has_option" value="{{ $has_option }}" />
        <div class="row">
            <div class="col-12 col-md-6">
               
                   <label for="course_id" class="control-label">
                    Test
                </label>
                <div class=" custom-select-wrapper">
                    <select name="course_id" id="course_id" class="form-control custom-select-box" required>
                        <option value="">Select Course</option>
                       @foreach($tests as $key=> $value)
                    <option value="{{$value->id}}" @if($question->test_id==$value->id) selected  @endif>{{$value->title}}</option>
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
                        <option value="1" @if($question->question_type==1) selected  @endif> Single Choice </option>
                        <option value="2" @if($question->question_type==2) selected  @endif> Multiple Choice </option>
                        <option value="3" @if($question->question_type==3) selected  @endif> Short Answer </option>
                    </select>
                    <span class="custom-select-icon">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="mt-3 notextarea">
                <label>Question </label>
                <textarea class="form-control editor" rows="3" name="question" id="question" value="" required="required">@if(isset($question->id)){{$question->question_text}}@endif</textarea>
            </div>
 </div>

            <div class="col-12 col-md-6 ">
 <div class="mt-3 notextarea">
               
                    <label>Option</label>
                    <textarea class="form-control editor" rows="3" name="option" id="option" required="required"></textarea>
                    <div class="addoptbtn">
                    <button type="button" id="add_option" class="btn btn-primary">Add Option</button>
                    </div>
                    <div class="addoptiontable ">
                         <div id="option-area"></div>
                        </div>
              </div>
             


        </div>




        <!-- <div class="cb_question_setup">
            
            

                <div class="col-6">
                    
                    
                   
                    
                </div>
            </div> -->
            
        </div>
    </div>




<div class="row">
                <div class="col-12 col-md-5 notextarea">
                    <label>Solution</label>
                    <textarea class="form-control textarea-col editor" rows="3" name="solution" id="solution" value="$question->solution">@if(isset($question->id)){{$question->solution}}@endif</textarea>
                </div>
            
                <div class="col-12 col-md-2">
                    <label>Marks</label>
                    <input type="number" class="form-control" name="marks" id="marks" placeholder="Enter Marks" required value="{{$question->marks}}" />
                </div>
            
                <div class="col-12 col-md-5 notextarea">
                    <label>Comment</label>
                    <textarea class="form-control textarea-col editor" rows="3" name="comment" id="comment">@if(isset($question->id)){{$question->comment}}@endif</textarea>
                </div>
            </div>



<div class="btmbtns">
    <div class="col-12 text-right t-3">
        {!! Form::button(trans('strings.backend.general.app_save'), ['class' => 'add-btn', 'id'=>'save']) !!}
    </div>
</div></div>


<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.8.0/ckeditor.js"></script>
 -->
 {{-- <script src="//cdn.ckeditor.com/44.3.0/full/ckeditor.js"></script> --}}
<!-- {!! Form::close() !!} -->
<script src="{{asset('ckeditor/ckeditor.js')}}" type="text/javascript"></script>
<script type="text/javascript">
    CKEDITOR.replace('question');


    CKEDITOR.replace('option');
    
    CKEDITOR.replace('solution');
    
    CKEDITOR.replace('comment')
    
</script>
@stop
@push('after-scripts')
<script type="text/javascript">
    var options = @if(isset($question->id))
        {!! json_encode($question->option_json ? json_decode($question->option_json) : []) !!}
    @else
        []
    @endif;
    var flag = 0;

    function removeOptions(pos) {
        options.splice(pos, 1);
        showOptions();
    }

    var has_option = $('#has_option').val();
    if(has_option) {
        showOptions();
    }
    


    function addOptions() {
        var option = CKEDITOR.instances["option"].getData();
        options_length = (options != null && options != undefined) ? options.length : 0;
        options.push([option.trim(), 0]);
        CKEDITOR.instances["option"].setData('');
    }

    $(document).on('click', "#add_option", function() {
        if (CKEDITOR.instances["option"].getData() != "") {
            
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
        var solution = CKEDITOR.instances["solution"].getData();
        var comment = CKEDITOR.instances["comment"].getData();
        var marks = $("#marks").val();
        return {
            test_id,
            question_type,
            question,
            options: JSON.stringify(options),
            solution,
            comment,
            marks
        }
    }

    $(document).on('click', "#save", function() {
        flag = 0;
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
    });

    var question_submit_url = "{{route('admin.test_questions.update')}}";

    function sendData(data) {
        var data = dataCollection();
        data['_token'] = "{{ csrf_token() }}";
        data['id'] = $('#edit_id').val();
        $.ajax({
            url: question_submit_url,
            type: 'post',
            data: data,
            success: function(response) {
                response = JSON.parse(response);
                if (response.code == 200) {
                    window.location.replace("{{route('admin.test_questions.index')}}");
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

    function showOptions(show_remove_options = true) {

        console.log(options)
        var has_option = $('#has_option').val();

        
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
                
            },
        });
    });
</script>
@endpush