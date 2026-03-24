@extends('backend.layouts.app')

@section('title', __('Assignments').' | '.app_name())

@section('content')
<style>
.or_optional::after {
    content: "OR";
    position: absolute;
    right: -9px;
    top: 5px;
    font-size: 14px;
    color: #373737;
}

</style>

<form id="addUserAssisment" enctype="multipart/form-data" method="POST" action="{{ route('admin.assessment_accounts.assignment_store') }}">
    @csrf
<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4 class="page-title d-inline">Add Course Assessment</h4>
    <div class="">
        @if ($user_id != NULL)
        @else
        <a href="{{ route('admin.assessment_accounts.assignments') }}" class="btn add-btn">@lang('View Course Assessment')</a>
        @endif
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                @if ($user_id != NULL)
                <input type="hidden" name="user_id" value="{{$user_id}}">
                <input type="hidden" name="user_type" value="2">
                @else
                <input type="hidden" name="user_type" value="1">
                @endif
                <input type="hidden" name="add_test_to_course_only" value="1">
                <div class="form-group row">
                    <label class="col-md-2 form-control-label" for="test_id">Course</label>
                    <div class="col-md-10 custom-select-wrapper">
                        <select class="form-control custom-select-box select2" name="course_id" id="course_id">
                            <option selected disabled>Select One Course</option>
                            @foreach ($courses as $key => $value)
                            <option @if($course_id == $value->id) selected @endif value="{{$value->id}}">{{$value->title}}</option>
                            @endforeach
                        </select>
                        <span class="custom-select-icon" style="right: 23px;" >
        <i class="fa fa-chevron-down"></i>
    </span>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-2 col-md-12 col-sm-12 form-control-label" for="test_id">Test</label>
                    <div class="col-lg-8 col-md-12 col-sm-12 mb-3  custom-select-wrapper">
                        <select class="form-control custom-select-box" name="test_id" id="test_id" required="" autofocus="">
                            <option value="">Select One Test</option>
                            @foreach ($tests as $key => $value)
                            <option @if(request()->get('test_id') == $value->id) selected @endif value="{{$value->id}}">{{$value->title}}</option>
                            @endforeach
                        </select>
                        <span class="custom-select-icon" style="right: 23px;">
        <i class="fa fa-chevron-down"></i>
    </span>
                    </div>
                    <div class="col-lg-2 col-md-12 col-sm-12">
                    <a href="{{ url('user/tests/create').'?course_id='.$course_id.'&new_test' }}"
                       class="btn btn-primary mt-auto w-100">Add New Test</a>
                    </div>
                </div>

                

                

                <div class="form-group row">
                    <div class="col-12 col-md-12 "> 
                        <div class="d-flex justify-content-between">

                        <div class="mr-4">

                            {{ form_cancel(route('admin.assessment_accounts.assignments', $user_id), __('buttons.general.cancel')) }}
                        </div>
                        <div>

                            {{ form_submit(__('buttons.general.crud.create')) }}
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="feedback_index" value="{{ route('admin.feedback.feedback-question-multiple',[$course_id]) }}">
        <input type="hidden" id="new-assisment" value="{{ route('admin.assessment_accounts.new-assisment') }}">
    </div>
</div>

</form>
@endsection
@push('after-scripts')
<script>
     var nxt_url_val= '';

    $('.frm_submit').on('click', function (){
        nxt_url_val = $(this).val();
    });

    $('#course_id').change(function (){
        var create_route = "{{route('admin.assessment_accounts.assignment_create')}}";
        var url = create_route +"?assis_new&course_id=" + $(this).val();
        document.location = url;
    })

    $(document).on('submit', '#addUserAssisment', function (e) {
    e.preventDefault();
    if($('#test_id').val() == "") {
        alert("Select Test name"); return false;
    }
    hrefurl=$(location).attr("href");
    last_part=hrefurl.substr(hrefurl.lastIndexOf('/') + 8)
    setTimeout(() => {
        let data = $('#addUserAssisment').serialize();
        let url = '{{route('admin.assessment_accounts.assignment_store')}}';

        var redirect_url=$("#feedback_index").val();
        var redirect_url_course=$("#new-assisment").val();

            $.ajax({
                    type: 'POST',
                    url: url,
                    data: data,
                    datatype: "json",
                    success: function (res) {
                    
                    if(res.done) {
                        return window.location.href = '/user/assignments'
                    }

                    if(last_part == 'assis_new'){
                        window.location.href = redirect_url_course;
                        return;
                    }
                    else{
                        window.location.href = redirect_url;
                        return;
                    }
                }
                })
            }, 100);
        })
</script>
@endpush