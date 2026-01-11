@extends('backend.layouts.app')
@section('title', __('Edit Course Feedback') . ' | ' . app_name())

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
        margin: 0 15px 0 0;
        color: white;
    }

    .create_done.next {
        background: #4dbd74;
    }

    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ccc !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important;
        border-color: #007bff !important;
        outline: none;
    }

    @media screen and (max-width: 768px) {
        .create_done {
            padding: 5px 20px;
        }
    }
</style>

{!! Form::open(['method' => 'POST', 'id' => 'addFeedbackQue', 'files' => true]) !!}
<div>
    <div class="grow py-3">
        <h5 class="text-20">Edit Course Feedback Question</h5>
    </div>
    <div class="card">


        <div class="card-body">
            @if (Auth::user()->isAdmin())
            <div class="row">
                <label class="col-md-2 form-control-label" for="first_name">Courses</label> </br>

                <div class="col-md-10">
                    <select name="course_id" class="form-control">
                        <option value=""> Select Course </option>

                        @foreach ($courses as $row)
                        <option value="{{ $row->id }}" @if (@$cf->course_id == $row->id) selected @endif>
                            {{ $row->title }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <!--col-->
            </div>
            @endif

            @if (Auth::user()->isAdmin())
            <div class="row mt-3">
                <div class="col-md-2 form-control-label">
                    {!! Form::label('questions', trans('labels.backend.questions.fields.question'), ['class' => 'control-label']) !!}
                </div>
                <div class="col-md-10">
                    {!! Form::select('feedback_question_ids[]', $questions, $cf?->feedback_question_id, [
                    'class' => 'form-control select2 js-example-questions-placeholder-multiple',
                    'multiple' => 'multiple',
                    'required' => true,
                    ]) !!}
                </div>
            </div>
            @endif

            <div class="mt-4 row">
                <div class="col-12  form-group text-right">
                    {!! Form::submit(trans('Save'), [
                    'class' => 'btn add-btn frm_submit',
                    'id' => 'nextBtn',
                    ]) !!}
                </div>
            </div>
        </div>
        <input type="hidden" id="final_index" value="{{ route('admin.assessment_accounts.final-submit') }}">
        <input type="hidden" id="feedback_index" value="{{ route('admin.feedback_question.index') }}">
    </div>
</div>

{!! Form::close() !!}
@stop

@push('after-scripts')
<script type="text/javascript" src="{{ asset('/vendor/unisharp/laravel-ckeditor/ckeditor.js') }}"></script>
<script type="text/javascript" src="{{ asset('/vendor/unisharp/laravel-ckeditor/adapters/jquery.js') }}"></script>
<script src="{{ asset('/vendor/laravel-filemanager/js/lfm.js') }}"></script>
<script>
    $('.editor').each(function() {
        CKEDITOR.replace($(this).attr('id'), {
            filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
            filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token={{ csrf_token() }}',
            filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
            filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token={{ csrf_token() }}',
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
            placeholder: "{{ trans('labels.backend.courses.select_courses') }}",
        });

        $(".js-example-questions-placeholder-multiple").select2({
            placeholder: "{{ trans('labels.backend.courses.select_questions') }}",
        });
    });
</script>

<script>
    var nxt_url_val = '';

    $('.frm_submit').on('click', function() {
        nxt_url_val = $(this).val();
    });
    $(document).on('submit', '#addFeedbackQue', function(e) {
        e.preventDefault();
        // alert('ho');
        setTimeout(() => {
            let data = $('#addFeedbackQue').serialize();
            let url = '{{ route("admin.course-feedback-questions.update") }}';
            var redirect_url = $("#final_index").val();
            var redirect_url_course = $("#feedback_index").val();
            // alert(redirect_url_course);
            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                datatype: "json",
                success: function(res) {
                    alert('Course feedback question updated successfully');
                    window.location.href = {{ route("admin.course-feedback-questions.index") }};
                }
            })
        }, 100);
    })
</script>
@endpush