@extends('backend.layouts.app')
@section('title', __("CourseFeedback").' | '.app_name())

@section('content')
<style>
    .create_done {
    padding: 10px 40px;
    font-size: 16px;
    font-weight: 500;
    background: #20a8d8;
    border: none;
    outline: none;
    float: right;
    /* margin: 0 15px 0 0; */
    color: white;
}
    .create_done.next {
    background: #4dbd74;
}
.select2-container .select2-search--inline .select2-search__field {
    box-sizing: border-box;
    border: none;
    font-size: 100%;
    margin-top: 5px;
    padding-left: 8px;
}

.select2-container--default .select2-selection--multiple:focus {
    outline: none !important;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important;
    border-color: #007bff !important;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
     outline: none !important;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important;
    border-color: #007bff !important;
}
.select2-container--default .select2-selection--multiple{
    border: 1px solid #ccc !important;
}

@media screen and (max-width: 768px) {
.create_done {
    padding: 5px 20px;
}
}
</style>


@include('backend.includes.partials.course-steps', ['step' => 4, 'course_id' => $_GET['course_id'], 'course' => null])

{!! Form::open(['method' => 'POST', 'id' => 'addFeedbackQue', 'files' => true]) !!}
<div class="">
    <div class="pb-3">
<div class="grow">
                <h4 class="text-20">Add Feedback Courses </h4>
              </div>
    </div>
    <div class="card">
       <!-- <div class="bg-secondary card-body d-flex justify-content-between">
            <h3 >
             Add Feedback Courses
            </h3>
           
        </div> -->
    <!-- <div class="card-header">
        <h3 class="page-title float-left">Add Feedback Courses</h3>
    </div> -->

    <div class="card-body">
        @if (Auth::user()->isAdmin())
        <div class="col-12">
            <div class="form-control-div" for="first_name">Courses</div>

            <div class="custom-select-wrapper mt-2">
                <select name="course_id" class="form-control custom-select-box select2">
                    <option value=""> Select Course </option>

                    @foreach($courses as $row)
                    <option value="{{ $row->id }}" @if(isset($_GET['course_id']) && ($_GET['course_id'] == $row->id)) selected="" @endif> {{ $row->title }} </option>
                    @endforeach
                </select>
                <span class="custom-select-icon">
        <i class="fa fa-chevron-down"></i>
    </span>
            </div>
            <!--col-->
        </div>
        @endif

        @if (Auth::user()->isAdmin())
        <div class="col-12 mt-3">
    <div class="form-control-div" for="questions">
        {{ trans('labels.backend.questions.fields.question') }}
    </div>

    <div class="custom-select-wrapper mt-2">
        <select name="feedback_question_ids[]" class="form-control custom-select-box select2 js-example-questions-placeholder-multiple" multiple required>
            @foreach($questions as $id => $question)
                <option value="{{ $id }}" @if(in_array($id, old('questions', []))) selected @endif>{{ $question }}</option>
            @endforeach
        </select>
        <span class="custom-select-icon">
            <i class="fa fa-chevron-down"></i>
        </span>
    </div>
        <!-- <div class="col-12 mt-3">
            <div class=" form-control-label">
                {!! Form::label('questions',trans('labels.backend.questions.fields.question'), ['class' => 'control-label']) !!}
            </div>
            <div class="mt-1">
            {!! Form::select('feedback_question_ids[]', $questions, old('questions'), ['class' => 'form-control select2 js-example-questions-placeholder-multiple', 'multiple' => 'multiple', 'required' => true]) !!}
            </div>
        </div> -->
        @endif
  <div class="form-group row  mt-4">
                         <div class="col-12 ">
                            <div class="d-flex justify-content-between">

                                <div>
      {!! Form::submit(trans('Done'), ['class' => 'btn  add-btn frm_submit','id'=>'doneBtn']) !!}
                                </div>
                                <div class="">
    
                                    {!! Form::submit(trans('Next'), ['class' => 'btn  cancel-btn frm_submit','id'=>'nextBtn']) !!}
                                </div>
                            </div>

                        </div>
                        <!-- <div class="col-4">
                            {{ form_cancel(route('admin.teachers.index'), __('buttons.general.cancel')) }}
                            {{ form_submit(__('buttons.general.crud.update')) }}
                        </div> -->
                    </div>

        <!-- <div class="d-flex justify-content-end mt-4 row">
            <div class="col-6 col-md-6 d-flex form-group justify-content-center text-center">
            {!! Form::submit(trans('Next'), ['class' => 'btn btn-lg btn-danger create_done next frm_submit','id'=>'nextBtn']) !!}
            {!! Form::submit(trans('Done'), ['class' => 'btn btn-lg create_done frm_submit','id'=>'doneBtn']) !!}
            
            </div>
        </div> -->
    </div>
    <input type="hidden" id="final_index" value="{{ route('admin.assessment_accounts.final-submit', [$_GET['course_id']]) }}"> 
    <input type="hidden" id="feedback_index" value="{{ route('admin.feedback_question.index') }}">
</div>
    
</div>


{!! Form::close() !!}
@stop

@push('after-scripts')
<script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script>
<script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/adapters/jquery.js')}}"></script>
<script src="{{asset('/vendor/laravel-filemanager/js/lfm.js')}}"></script>
<script>
    $('.editor').each(function() {
        CKEDITOR.replace($(this).attr('id'), {
            filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
            filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token={{csrf_token()}}',
            filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
            filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token={{csrf_token()}}',
            extraPlugins: 'smiley,lineutils,widget,codesnippet,prism,flash,colorbutton,colordialog',
        });
    });

    $(document).ready(function() {
        $('#start_date').datepicker({
            autoclose: true,
            dateFormat: "{{ config('app.date_format_js') }}"
        });

        var dateToday = new Date();
        $('#expire_at').datepicker({
            autoclose: true,
            minDate: dateToday,
            dateFormat: "{{ config('app.date_format_js') }}"
        });

        $(".js-example-courses-placeholder-multiple").select2({
            placeholder: "{{trans('labels.backend.courses.select_courses')}}",
        });

        $(".js-example-questions-placeholder-multiple").select2({
            placeholder: "{{trans('labels.backend.courses.select_questions')}}",
        });
    });
</script>

<script>
    var nxt_url_val= '';

    $('.frm_submit').on('click', function (){
        nxt_url_val = $(this).val();
    });
$(document).on('submit', '#addFeedbackQue', function (e) {
    e.preventDefault();
    // alert('ho');
    setTimeout(() => {
        let data = $('#addFeedbackQue').serialize();
        let url = '{{route('admin.feedback.course_feedback_store')}}';
        var redirect_url=$("#final_index").val();
        var redirect_url_course=$("#feedback_index").val();
        // alert(redirect_url_course);
    $.ajax({
            type: 'POST',
            url: url,
            data: data,
            datatype: "json",
            success: function (res) {
            console.log(res)

                if(nxt_url_val == 'Next'){
                    window.location.href = redirect_url;
                    return;
                }
                if(nxt_url_val = 'Done'){
                    window.location.href = redirect_url_course;
                    return;
                }
            }
        })
    }, 100);
})
</script>

@endpush