@extends('backend.layouts.app')

@section('title', __('Assignments').' | '.app_name())

@push('after-styles')
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

/* .select2-container--default .select2-selection--single .select2-selection__arrow {
    background-image: url("data:image/svg+xml,%3Csvg fill='black' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 16px;
    width: 30px;
    height: 100%;
    right: 6px;
    top: 0;
} */


/* Add FontAwesome arrow */
.select2-container--default .select2-selection--single .select2-selection__arrow::after {
    content: '\f078'; /* Unicode for caret-down */
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-26%);
    pointer-events: none;
    color: #333;
    font-size: 14px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    display: none !important;
}

/* Basic style */
.select2-container--default .select2-selection--single {
    border: 1px solid #ced4da;
    border-radius: 4px;
    height: 35px;
    padding: 2px 5px;
    background-color: #fff;
    transition: border 0.3s ease;
}

/* Focus style */
.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}



/* Add FontAwesome arrow using ::after on the wrapper */


</style>
@endpush

@section('content')
<form action="{{ route('admin.assessment_accounts.course-assignment-invitation') }}" method="POST" enctype="multipart/form-data" class="form-horizontal">
    @csrf
   
    <div class="pb-3 d-flex justify-content-between align-items-center">
        <h4 >@lang('Create Invitation Assignment')</h4>
        <div>
            @if ($user_id != NULL)
            <a href="{{ route('admin.assessment_accounts.account_assignments', $user_id) }}" class="btn add-btn">@lang('View Assignments')</a>
            @else
            <a href="{{ route('admin.assessment_accounts.assignments') }}" class="btn add-btn">@lang('View Assignments')</a>
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
                <input type="hidden" name="reschedule" value="{{ $reschedule }}" />
                <div class="">
                <label style="font-size: 17px;font-weight: 600;padding-left: 12px;border-bottom: 1px solid #e7e7e7;padding-bottom: 11px;margin-bottom: 25px;display: block;">Make a New Assignment (Step-1)</label>
                </div>

                {{-- <div class="form-group row">
                    <label class="col-md-12 form-control-label" for="title">Title</label>
                    <div class="col-md-12">
                        <input class="form-control" type="text" name="title" id="title" placeholder="Title for assignment" required="" autofocus="">
                    </div>
                </div> --}}

                {{-- <div class="form-group row">
                    <label class="col-md-12 form-control-label" for="test_id">Course Language</label>
                    <div class="col-md-12">
                        <select class="form-control" name="course_language" id="course_language" >
                            <option value="english" selected>English</option>
                            <option value="arabic">Arabic</option>
                        </select>
                    </div>
                </div> --}}

                <div class="form-group row">
                    <div class="col-md-12">
                        <div class="custom-select-wrapper ">
                            <select class="form-control custom-select-box" name="course_type" id="course_type">
                                <option value="Offline" {{ old('course_type') == 'Offline' ? 'selected' : '' }}>Live-Online</option>
                                <option value="Live-Classroom" {{ old('course_type') == 'Live-Classroom' ? 'selected' : '' }}>Live-Classroom</option>
                            </select>
                            <span class="custom-select-icon" style="right: 10px;">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-12 form-control-label" for="course_id">Course</label>
                    <div class="col-md-12">
                        <select class="form-control custom-select-box select2" name="course_id">
                            <option value="" disabled {{ old('course_id') ? '' : 'selected' }}>Select One Course</option>
                            @foreach ($courses as $key => $value)
                                <option value="{{ $value->id }}" {{ old('course_id') == $value->id ? 'selected' : '' }}>
                                    {{ $value->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-12 form-control-label" for="title">Date</label>
                    <div class="col-md-12">
                        <input class="form-control" value="{{ old('due_date') }}" type="datetime-local" name="due_date" id="due_date" required="" autofocus="">
                    </div>
                </div>

                <div class="form-group row" id="show-meetlink">
                    <label class="col-md-12 form-control-label" for="title">Meeting Link</label>
                    <div class="col-md-12">
                        <input class="form-control" value="{{ old('meeting_link') }}" type="text" name="meeting_link" id="meeting_link" autofocus="">
                    </div>
                </div>

                <div class="form-group row" id="show-location" style="display: none;">
                    <label class="col-md-12 form-control-label" for="title">Classroom Location</label>
                    <div class="col-md-12">
                        <input class="form-control" value="{{ old('classroom_location') }}" type="text" name="classroom_location" id="classroom_location" autofocus="">
                    </div>
                </div>

                <div class="form-group row" id="show-meetlink-datetime">
                    <label class="col-md-12 form-control-label" for="title">Meeting End DateTime</label>
                    <div class="col-md-12">
                        <input class="form-control" type="datetime-local" name="meeting_end_datetime" value="{{ old('meeting_end_datetime') }}"  id="meeting_end_datetime" autofocus="">
                    </div>
                </div>

                

                

                {{-- <div class="">
                <label class="">Assign a...</label>
                </div> --}}

                

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
                <label style=" font-size: 17px;font-weight: 600;padding-left: 12px;border-bottom: 1px solid #e7e7e7;padding-bottom: 11px;margin-bottom: 25px;display: block;">Make a New Assignment (Step-2)</label>
                </div>

                <div class="">
                <label class="">Assign to...</label>
                </div>


                <div class="form-group row">
                <label class="col-md-12 form-control-label" for="test_id">Users</label>
                    <div class="col-md-12 custom-select-wrapper">
                        <select name="teachers[]" class="form-control custom-select-box select2 js-example-placeholder-multiple" multiple>
                            @foreach ($teachers as $value => $label)
                                <option value="{{ $value }}" {{ in_array($value, old('teachers', [])) ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <span class="custom-select-icon" style="right:23px">
        <i class="fa fa-chevron-down"></i>
    </span>
                        
                    </div>
                </div>


         OR
         <br>
         <div class="row">

             <div class="col-md-12">
               <label class=" form-control-label" for="first_name">@lang('Select Department')</label>
   
               <div class=" custom-select-wrapper">
                   <select name="department_id" class="form-control custom-select-box select2">
                       <option value=""> @lang('select-one') </option>
                       @foreach($departments as $row)
                       <option value="{{ $row->id }}"> {{ $row->title }} </option>
                       @endforeach
                   </select>
                   <span class="custom-select-icon" style="right: 10px;">
        <i class="fa fa-chevron-down"></i>
    </span>
               </div>
               <!--col-->
           </div>
         </div>
        <br>

                <div class="form-group row">
                    <label class="col-md-12 form-control-label" for="verify_code">Add Custome message</label>
                    <div class="col-md-12">
                        <input class="form-control" type="text" name="message" id="message">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-12 col-md-12 d-flex justify-content-between mt-3">
                        

                            <div class="mr-4">
    
                                {{ form_cancel(route('admin.assessment_accounts.assignments', $user_id), __('buttons.general.cancel')) }}
                            </div>
                            <div class="">
    
                                {{ form_submit(__('buttons.general.crud.create')) }}
                            </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {

        
        $('#course_type').change(function () {
            var course_type = $(this).val();

             $.ajax({
                type: 'POST',
                url: '{{ route("admin.get.courses.by_course_type") }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    'course_type':$(this).val()
                },
                datatype: "json",
                success: function (res) {
                    //console.log(res.courses)
                    let $select = $('select[name="course_id"]');
                    $select.empty();
                    $select.append('<option value="" disabled selected>Select One Course</option>');
                    $.each(res.courses, function (index, course) {
                        $select.append(`<option value="${course.id}">${course.title}</option>`);
                    });
                    $select.trigger('change');
                }
            })

            if(course_type == 'Offline') {
                
                 $('#show-location').hide();
                $('#show-meetlink').show();
                //$('#show-meetlink-datetime').show();
            } else {
               $('#show-location').show();
                $('#show-meetlink').hide();
                //$('#show-meetlink-datetime').hide();
            }
        })
        $('#course_language').change(function () {
            $.ajax({
                type: 'POST',
                url: '{{ route("admin.get.courses.by_lang") }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    'lang':$(this).val()
                },
                datatype: "json",
                success: function (res) {
                    //console.log(res.courses)
                    let $select = $('select[name="course_id"]');
                    $select.empty();
                    $select.append('<option value="" disabled selected>Select One Course</option>');
                    $.each(res.courses, function (index, course) {
                        $select.append(`<option value="${course.id}">${course.title}</option>`);
                    });
                    $select.trigger('change');
                }
            })
            
        })
    });
</script>
@push('after-scripts')



@endpush