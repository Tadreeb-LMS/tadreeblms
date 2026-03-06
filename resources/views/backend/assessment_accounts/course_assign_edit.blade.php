@extends('backend.layouts.app')

@section('title', __('Assignments').' | '.app_name())

@section('style')
<style>
.step_assign{
  font-size: 17px;
  font-weight: 600;
  padding-left: 12px;
  border-bottom: 1px solid #e7e7e7;
  padding-bottom: 11px;
  margin-bottom: 25px;
  display: block;
}

</style>
@endsection

@section('content')
{{ html()->form('POST', route('admin.assessment_accounts.course_assignment_update'))->acceptsFiles()->class('form-horizontal')->open() }}
<div class="card">
    <div class="card-header">
        <h3 class="page-title d-inline">@lang('Edit Assignment')</h3>
        <div class="float-right">
           
        </div>
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
                <div class="">
                <label style="font-size: 17px;font-weight: 600;padding-left: 12px;border-bottom: 1px solid #e7e7e7;padding-bottom: 11px;margin-bottom: 25px;display: block;">Make a New Assignment (Step-1)</label>
                </div>

                <div class="form-group row">
                    <label class="col-md-12 form-control-label" for="title">Title</label>
                    <div class="col-md-12">
                        <input class="form-control" type="text" name="title" id="title" placeholder="Title for assignment" required="" autofocus="" value="{{$assessment->title}}">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-12 form-control-label" for="title">Due Date</label>
                    <div class="col-md-12">
                        <input class="form-control" type="date" name="due_date" id="due_date" required="" autofocus="" value="{{$assessment->due_date}}">
                    </div>
                </div>

                <div class="">
                <label style="font-size: 17px;font-weight: 600;padding-left: 12px;border-bottom: 1px solid #e7e7e7;padding-bottom: 11px;margin-bottom: 25px;display: block;">Make a New Assignment (Step-2)</label>
                </div>

                <div class="">
                <label class="">Assign a...</label>
                </div>

                <div class="form-group row">
                    <label class="col-md-12 form-control-label" for="test_id">Course</label>
                    <div class="col-md-12">
                        <select class="form-control" name="course_id">
                            <option value="">Select One Course</option>
                            @foreach ($courses as $key => $value)
                            <option value="{{$value->id}}" @if($assessment->course_id == $value->id) selected="" @endif>{{$value->title}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- or

                <br>

                <div class="form-group row">
                    <label class="col-md-12 form-control-label" for="test_id">Category</label>
                    <div class="col-md-12">
                        <select class="form-control" name="category_id">
                            <option value="">Select One Category</option>
                            @foreach ($category as $key => $value)
                            <option value="{{$value->id}}">{{$value->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div> -->

                <div class="">
                <label style=" font-size: 17px;font-weight: 600;padding-left: 12px;border-bottom: 1px solid #e7e7e7;padding-bottom: 11px;margin-bottom: 25px;display: block;">Make a New Assignment (Step-3)</label>
                </div>

                <div class="">
                <label class="">Assign to...</label>
                </div>


                <div class="form-group row" style="display: ;">
                <label class="col-md-12 form-control-label" for="test_id">Users</label>
                    <div class="col-md-12">

<select name="teachers[]" class="form-control select2 js-example-placeholder-multiple" multiple="">
<option value=""> Select One </option>
@foreach($teachers as $key=>$row)
<option value="{{ $key }}" @if(in_array($key, explode(',',$assessment->assign_to))) selected="" @endif> {{ $row }} </option>
@endforeach
</select>

                  
                    </div>
                </div>


         OR
         <br>
           <div class="row">
             <label class="col-md-12 form-control-label" for="first_name">@lang('Select Department')</label>

             <div class="col-md-12">
                 <select name="department_id" class="form-control">
                     <option value=""> @lang('select-one') </option>
                    @foreach($departments as $row)
                    <option value="{{ $row->id }}" @if($assessment->department_id == $row->id) selected="" @endif> {{ $row->title }} </option>
                    @endforeach
                </select>
            </div>
            <!--col-->
        </div>
        <br>

                <div class="form-group row">
                    <label class="col-md-12 form-control-label" for="verify_code">Add Custome message</label>
                    <div class="col-md-12">
                        <input class="form-control" type="text" name="message" id="message"   autofocus="" value="{{$assessment->message}}">
                    </div>
                </div>

                <!-- <div class="form-group row">
                    <label class="col-md-2 form-control-label" for="total_question">Total Questions</label>
                    <div class="col-md-10">
                        <input class="form-control" type="number" name="total_question" id="total_question" placeholder="Enter Total Question" required="" autofocus="">
                    </div>
                </div> -->

                <!-- <div class="form-group row">
                    <label class="col-md-2 form-control-label" for="duration">Duration (In Minutes)</label>
                    <div class="col-md-10">
                        <input class="form-control" type="number" name="duration" id="duration" min="1" placeholder="Duration(In Minutes)" required="" autofocus="">
                    </div>
                </div> -->

                <!-- <div class="form-group row">
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
                </div> -->

                <!-- <div class="form-group row">
                    <label class="col-md-2 form-control-label" for="buffer_time">Buffer Time(In Minutes)</label>
                    <div class="col-md-10">
                        <input class="form-control" type="number" name="buffer_time" id="buffer_time" placeholder="Buffer Time of the Assignment" required="" autofocus="">
                    </div>
                </div> -->

                <div class="form-group row justify-content-center">
                    <div class="col-6 col-md-4">
                        {{ form_cancel(route('admin.assessment_accounts.assignments', $user_id), __('buttons.general.cancel')) }}
                         {{ form_submit(__('buttons.general.crud.update')) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{ html()->form()->close() }}
@endsection
@push('after-scripts')
@endpush