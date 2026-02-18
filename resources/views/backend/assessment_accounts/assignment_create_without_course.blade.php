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
<!-- {{ html()->form('POST', route('admin.assessment_accounts.assignment_store'))->acceptsFiles()->class('form-horizontal')->open() }} -->

<form id="addUserAssisment" enctype="multipart/form-data" method="POST" action="{{ route('admin.assessment_accounts.assignment_store') }}">
    @csrf
<div class="card">
    <div class="card-header">
        <h3 class="page-title d-inline">Add Assessment</h3>
        {{-- <div class="float-right">
            @if ($user_id != NULL)
            <a href="{{ route('admin.assessment_accounts.account_assignments', $user_id) }}" class="btn btn-success">@lang('View User Assessment')</a>
            @else
            <a href="{{ route('admin.assessment_accounts.assignments') }}" class="btn btn-success">@lang('View User Assessment')</a>
            @endif
        </div> --}}
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                @if ($user_id != NULL)
                <input type="hidden" name="user_id" value="{{$user_id}}">
                <input type="hidden" name="user_type" value="2">
                @else
                <input type="hidden" name="user_type" value="1">
                @endif
                
                <div class="form-group row">
                    <label class="col-lg-2 col-md-4 col-sm-4 form-control-label" for="test_id">Test</label>
                    <div class="col-lg-8 col-md-5 col-sm-7 mb-3 or_optional">
                        <select class="form-control" name="test_id" id="test_id" required="" autofocus="">
                            <option value="" selected disabled>Select One Test</option>
                            @foreach ($tests as $key => $value)
                            <option @if(request()->get('test_id') == $value->id) selected @endif value="{{$value->id}}">{{$value->title}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6">
                    <a href="{{ url('user/manual-test') }}" class="btn btn-primary mt-auto w-100">Add New Test</a>
                    </div>
                </div>

                

                <!-- <div class="form-group row">
                <label class="col-lg-2 col-md-4 col-sm-4 form-control-label" for="test_id">Users</label>
                    <div class="col-lg-8 col-md-5 col-sm-7 mb-3 or_optional">
                    {!! Form::select('teachers[]', $teachers, old('teachers'), ['class' => 'form-control select2 js-example-placeholder-multiple', 'multiple' => 'multiple', 'required' => false]) !!}
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6">
                    <a href="{{ url('user/employee/create?new_user') }}" class="btn btn-primary mt-auto w-100">Add New User</a>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-2 form-control-label" for="verify_code">Verification Code</label>
                    <div class="col-md-10">
                        <input class="form-control" type="text" name="verify_code" id="verify_code" placeholder="Enter Verification Code" required="" autofocus="">
                    </div>
                </div> -->

                <!-- <div class="form-group row">
                    <label class="col-md-2 form-control-label" for="total_question">Total Questions</label>
                    <div class="col-md-10">
                        <input class="form-control" type="number" name="total_question" id="total_question" placeholder="Enter Total Question" required="" autofocus="">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-2 form-control-label" for="duration">Duration (In Minutes)</label>
                    <div class="col-md-10">
                        <input class="form-control" type="number" name="duration" id="duration" min="1" placeholder="Duration(In Minutes)" required="" autofocus="">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-2 form-control-label" for="start_time">Start Time</label>
                    <div class="col-md-10">
                        <input class="form-control" type="datetime-local" name="start_time" id="start_time" placeholder="Time of starting Assignment" required="" autofocus="">
                    </div>
                </div> -->

                <!-- <div class="form-group row">
                    <label class="col-md-2 form-control-label" for="end_time">End Time</label>
                    <div class="col-md-10">
                        <input class="form-control" type="datetime-local" name="end_time" id="end_time" placeholder="Last Time of starting Assignment" required="" autofocus="">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-2 form-control-label" for="buffer_time">Buffer Time(In Minutes)</label>
                    <div class="col-md-10">
                        <input class="form-control" type="number" name="buffer_time" id="buffer_time" placeholder="Buffer Time of the Assignment" required="" autofocus="">
                    </div>
                </div> -->

                <div class="form-group row justify-content-center">
                    <div class="col-6 col-md-4">
                        {{ form_cancel(route('admin.assessment_accounts.assignments', $user_id), __('buttons.general.cancel')) }}
                        {{ form_submit(__('buttons.general.crud.create')) }}
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="feedback_index" value="{{ route('admin.feedback.feedback-question-multiple',[$course_id]) }}">
        <input type="hidden" id="new-assisment" value="{{ route('admin.assessment_accounts.new-assisment') }}">
    </div>
</div>
<!-- {{ html()->form()->close() }} -->
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
                        window.location.href = '/user/manual-assessments/create'; 
                }
                })
            }, 100);
        })
</script>
@endpush