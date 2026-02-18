@extends('backend.layouts.app')

@section('title', __('Assessment Accounts').' | '.app_name())

@section('content')

<style>
.assignment_form {
    max-width: 850px;
}

.or_optional::after {
    content: "OR";
    position: absolute;
    right: -9px;
    top: 5px;
    font-size: 14px;
    color: #373737;
}

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
@media screen and (max-width: 768px) {
.create_done {
    padding: 5px 20px;
}
}
</style>

<div class="tabs assignment_form">
    @if(isset($_GET['show_type']) && ($_GET['show_type'] == 1))
    <input type="radio" name="tab-btn" id="tab-btn-1" value="" checked>
    <label for="tab-btn-1">@lang('Add assessment with courses')</label>
    @endif
     @if(isset($_GET['show_type']) && ($_GET['show_type'] == 2))
    <input type="radio" name="tab-btn" id="tab-btn-2" value="" checked>
    <label for="tab-btn-2">@lang('Add Assessment Manually')</label>
 @endif
    <div id="content-1">
    <form id="addAssisment" enctype="multipart/form-data" method="POST" action="{{ route('admin.assessment_accounts.store') }}">
        @csrf
       <!-- {{ html()->form('POST', route('admin.assessment_accounts.store'))->acceptsFiles()->class('form-horizontal')->open() }} -->
<div class="card">
    <div class="card-header">
        <h3 class="page-title d-inline">@lang('Create Assessment Account')</h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-12">
            <div class="form-group row">
                    <label class="col-lg-3 col-md-3 col-sm-4 form-control-label" for="test_id">@lang('Assessment')</label>
                    <div class="col-lg-6 col-md-6 col-sm-7 mb-3 or_optional">
                        <select class="form-control" name="assignment_id">
                            <option>@lang('Select Assessment')</option>
                            @foreach ($assignment as $key => $value)
                            <option value="{{$value->id}}">{{$value->title}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-6">
                    <a href="{{ url('user/assignments/create?assis_new') }}" class="btn btn-primary mt-auto w-100">@lang('Add New Assessment')</a>
                    </div>

            </div>



                <div class="form-group row">
                    <label class="col-lg-3 col-md-3 col-sm-4 form-control-label" for="test_id">@lang('Add Courses')</label>
                    <div class="col-lg-6 col-md-6 col-sm-7 mb-3 or_optional">
                        <select class="form-control" name="course_id">
                            <option>@lang('Select Course')</option>
                            @foreach ($courses as $key => $value)
                            <option value="{{$value->id}}">{{$value->title}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-6">
                    <a href="{{ url('/user/courses/create?course_new') }}"
                       class="btn btn-primary mt-auto w-100">@lang('Add New Course')</a>

                </div>
                </div>

                <!-- <h4>Step 2</h4>
            <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-6 form-group">
                {!! Form::label('questions',trans('Add Feedback'), ['class' => 'control-label']) !!}
            </div>
            <div class="col-lg-6 col-md-6 col-sm-7 mb-3 or_optional">
            {!! Form::select('feedback_question_ids[]', $questions, old('questions'), ['class' => 'form-control select2 js-example-questions-placeholder-multiple', 'multiple' => 'multiple', 'required' => true]) !!}
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                    <a href="{{ route('admin.feedback.create_question') }}"
                       class="btn btn-primary mt-auto w-100">@lang('strings.backend.general.app_add_new')</a>
                </div>
        </div> -->

        <div class="form-group row">
                    {{ html()->label(__('Status'))->class('col-md-2 form-control-label')->for('active') }}
                    <div class="col-md-10">
                        {{ html()->label(html()->checkbox('')->name('active')
                                        ->checked(true)->class('switch-input')->value(1)

                                    . '<span class="switch-label"></span><span class="switch-handle"></span>')
                                ->class('switch switch-lg switch-3d switch-primary')
                            }}
                    </div>
                </div>

                <div class="form-group row justify-content-center">
                    <div class="col-12">
                        {{ form_cancel(route('admin.assessment_accounts.index'), __('buttons.general.cancel')) }}
                        <button type="submit" value="Next" name="action" class="btn btn-lg btn-danger create_done next frm_submit" id="nextBtn">{{ trans('Next') }}</button>
                        <button type="submit" value="Done" name="action" class="btn btn-lg create_done frm_submit" id="doneBtn">{{ trans('Done') }}</button>
                        <!-- {{ form_submit(__('buttons.general.crud.create')) }} -->
                    </div>
                </div>

                <!-- <div class="form-group row">
                    <div class="col-lg-2 col-md-4 col-sm-6 col-6">
                    <a class="btn btn-danger w-100" href="http://127.0.0.1:8000/user/assessment_accounts">Cancel</a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-8 col-6">
                        {!! Form::submit(__('Next'), ['class' => 'btn btn-lg btn-danger create_done next frm_submit','id'=>'nextBtn']) !!}
                        {!! Form::submit(__('Done'), ['class' => 'btn btn-lg create_done frm_submit','id'=>'doneBtn']) !!} -->
                        <!-- <button class="btn btn-success w-100" type="submit">Create</button> -->
                        <!-- {{ form_submit(__('buttons.general.crud.create')) }} -->
                        <!-- </div>
                </div> -->
            </div>
        </div>
    </div>
</div>
<!-- {{ html()->form()->close() }} -->
    </form>
    </div>
    <div id="content-2">
    <form id="addAssisment" enctype="multipart/form-data" method="POST" action="{{ route('admin.assessment_accounts.store') }}">
        @csrf
    <!-- {{ html()->form('POST', route('admin.assessment_accounts.store'))->acceptsFiles()->class('form-horizontal')->open() }} -->
<div class="card">
    <div class="card-header">
        <h3 class="page-title d-inline">@lang('Create Assessment Account')</h3>
        <!-- <div>
            <a href="{{ route('admin.assessment_accounts.index') }}" class="btn btn-success">@lang('View Assessment Accounts')</a>
        </div> -->
    </div>
    <div class="card-body">
        <div class="row">

            <div class="col-12">

           <div class="form-group row">
                    <label class="col-lg-3 col-md-3 col-sm-4 form-control-label" for="test_id">@lang('Assessment')</label>
                    <div class="col-lg-6 col-md-6 col-sm-7 mb-3 or_optional">
                        <select class="form-control" name="assignment_id">
                            <option>@lang('Select Assessment')</option>
                            @foreach ($assignment as $key => $value)
                            <option value="{{$value->id}}">{{$value->title}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-6">
                    <a href="{{ route('admin.assessment_accounts.assignment_create_without_course') }}" class="btn btn-primary mt-auto w-100">@lang('Add New Assessment')</a>
                    </div>

                </div>

                <!-- <div class="float-right">
                    <a href="{{ route('admin.employee.create') }}"
                       class="btn btn-primary mt-auto">@lang('strings.backend.general.app_add_new')</a>

                </div> -->

              <div class="form-group row">
                <label class="col-lg-3 col-md-3 col-sm-4 form-control-label" for="test_id">@lang('Users')</label>
                    <div class="col-lg-6 col-md-6 col-sm-7 mb-3 or_optional">
                    <select name="teachers[]" class="form-control select2 js-example-placeholder-multiple" multiple>
                        @foreach($teachers as $key => $teacher)
                            <option value="{{ $key }}" @if(is_array(old('teachers')) && in_array($key, old('teachers'))) selected @endif>{{ $teacher }}</option>
                        @endforeach
                    </select>
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-6">
                    <a href="{{ url('user/employee/create?add_user') }}" class="btn btn-primary mt-auto w-100">@lang('Add User')</a>
                    </div>
                </div>

                <br>
                @lang('OR')
                <br>

                <div class="form-group row mt-2">
                        <label class="col-lg-3 col-md-3 col-sm-4 form-control-label" for="first_name">@lang('Select Department')</label>
                        <div class="col-lg-6 col-md-6 col-sm-7 mb-3 or_optional">
                            <select name="department" class="form-control">
                                <option value=""> @lang('Select One') </option>
                                @foreach($departments as $row)
                                    <option value="{{ $row->id }}"> {{ $row->title }} </option>
                                @endforeach
                            </select>
                        </div><!--col-->
                        <div>
                        <div class="col-lg-3 col-md-3 col-sm-6">
                            <a href="{{ url('user/department-create?add_dep') }}" class="btn btn-primary">@lang('Add Department')</a>
                        </div>
                        </div>
                    </div>
                    <div class="form-group row mt-2">
                        <label class="col-lg-3 col-md-3 col-sm-4 form-control-label" for="first_name">@lang('Due Date')</label>
                        <div class="col-lg-6 col-md-6 col-sm-7 mb-3">
                            <input type="date" class="form-control">
                        </div>    
                    </div>
                    <div class="form-group row mt-2">
                        <label class="col-lg-3 col-md-3 col-sm-4 form-control-label" for="first_name">@lang('Qualifying Percentage')</label>
                        <div class="col-lg-6 col-md-6 col-sm-7 mb-3">
                            <input type="number" class="form-control" min="1" max="100" oninput="this.value = Math.min(Math.max(this.value, 1), 100)">
                        </div>    
                    </div>
                <!-- <h4>Step 2</h4> -->
                <!-- <div class="row"> -->
            <!-- <div class="col-lg-3 col-md-3 col-sm-6 form-group">
                {!! Form::label('questions',trans('Add Feedback'), ['class' => 'control-label']) !!}
            </div> -->
            <!-- <div class="col-lg-6 col-md-6 col-sm-7 mb-3 or_optional">
            {!! Form::select('feedback_question_ids[]', $questions, old('questions'), ['class' => 'form-control select2 js-example-questions-placeholder-multiple', 'multiple' => 'multiple', 'required' => true]) !!}
            </div> -->
            <!-- <div class="col-lg-3 col-md-3 col-sm-6">
                    <a href="{{ route('admin.feedback.create_question') }}"
                       class="btn btn-primary mt-auto w-100">@lang('strings.backend.general.app_add_new')</a>
                </div>
        </div> -->

                {{-- <div class="form-group row">
                    {{ html()->label(__('Status'))->class('col-md-2 form-control-label')->for('active') }}
                    <div class="col-md-10">
                        {{ html()->label(html()->checkbox('')->name('active')
                                        ->checked(true)->class('switch-input')->value(1)

                                    . '<span class="switch-label"></span><span class="switch-handle"></span>')
                                ->class('switch switch-lg switch-3d switch-primary')
                            }}
                    </div>

                </div> --}}

                <div class="form-group row justify-content-center">
                    <div class="col-12">
                        {{ form_cancel(route('admin.assessment_accounts.index'), __('buttons.general.cancel')) }}
                        <button type="submit" value="Next" name="action" class="btn btn-lg btn-danger create_done next frm_submit" id="nextBtn">{{ trans('Next') }}</button>
                        <button type="submit" value="Done" name="action" class="btn btn-lg create_done frm_submit" id="doneBtn">{{ trans('Done') }}</button>
                        <!-- {{ form_submit(__('buttons.general.crud.create')) }} -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="assisment_index" value="{{ route('admin.assessment_accounts.index') }}">
    <input type="hidden" id="feedback_index" value="{{ route('admin.feedback.create_course_feedback') }}">
</div>
<!-- {{ html()->form()->close() }} -->
    </form>
    </div>

  </div>


@endsection
@push('after-scripts')
<script>
    var nxt_url_val= '';

    $('.frm_submit').on('click', function (){
        nxt_url_val = $(this).attr('value');
    });
$(document).on('submit', '#addAssisment', function (e) {
    e.preventDefault();
    // alert('ho');
    setTimeout(() => {
        let data = $('#addAssisment').serialize();
        let url = '{{route('admin.assessment_accounts.store')}}';
        var redirect_url=$("#feedback_index").val();
        var redirect_url_course=$("#assisment_index").val();
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
                if(nxt_url_val == 'Done'){
                    window.location.href = redirect_url_course;
                    return;
                }
            }
        })
    }, 100);
})
</script>
@endpush