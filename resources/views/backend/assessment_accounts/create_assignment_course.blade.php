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
.select2-selection__arrow {
    display: none !important;
}
.select2-container--default .select2-selection--single {
        border: 1px solid #ccc !important;
        border-radius: 5px !important;
        /* padding: 4px; */
    }
    .select2-container .select2-selection--single {
    box-sizing: border-box;
    cursor: pointer;
    display: block;
    height: 34px;
    user-select: none;
    -webkit-user-select: none;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #444;
    line-height: 30px;
}
</style>
@endpush

@section('content')

<form method="POST"
      action="{{ route('admin.assessment_accounts.course-assignment') }}"
      enctype="multipart/form-data"
      class="form-horizontal">

@csrf

<div class="pb-3 d-flex justify-content-between">
    <h4>@lang('Create Assignment')</h4>

    <div>
        @if ($user_id != NULL)
            <a href="{{ route('admin.assessment_accounts.account_assignments', $user_id) }}"
               class="btn btn-primary">@lang('View Assignments')</a>
        @else
            <a href="{{ route('admin.assessment_accounts.assignments') }}"
               class="btn btn-primary">@lang('View Assignments')</a>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body">

        <div class="row">
            <div class="col-12">

                @if ($user_id != NULL)
                    <input type="hidden" name="user_id" value="{{ $user_id }}">
                    <input type="hidden" name="user_type" value="2">
                @else
                    <input type="hidden" name="user_type" value="1">
                @endif

                <label class="step-title">
                    Make a New Assignment (Step-1)
                </label>

                {{-- Title --}}
                <div class="form-group row">
                    <label class="col-md-12 form-control-label">Title</label>
                    <div class="col-md-12">
                        <input class="form-control"
                               type="text"
                               name="title"
                               placeholder="Title for assignment"
                               value="{{ old('title') }}"
                               required>
                    </div>
                </div>

                {{-- Due Date --}}
                <div class="form-group row">
                    <label class="col-md-12 form-control-label">Due Date</label>
                    <div class="col-md-12">
                        <input class="form-control"
                               type="date"
                               name="due_date"
                               value="{{ old('due_date') }}"
                               required>
                    </div>
                </div>

                <label class="step-title">
                    Make a New Assignment (Step-2)
                </label>

                {{-- Course Language --}}
                <div class="form-group row">
                    <label class="col-md-12 form-control-label">Course Language</label>

                    <div class="col-md-12 custom-select-wrapper">
                        <select class="form-control custom-select-box"
                                name="course_language">
                            <option value="english" selected>English</option>
                            <option value="arabic">Arabic</option>
                        </select>

                        <span class="custom-select-icon" style="right:23px;">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                {{-- Course --}}
                <div class="form-group row">
                    <label class="col-md-12 form-control-label">Course</label>

                    <div class="col-md-12 custom-select-wrapper">
                        <select class="form-control custom-select-box select2"
                                name="course_id">
                            <option value="" disabled {{ old('course_id') ? '' : 'selected' }}>
                                Select One Course
                            </option>

                            @foreach ($courses as $value)
                                <option value="{{ $value->id }}"
                                    {{ old('course_id') == $value->id ? 'selected' : '' }}>
                                    {{ $value->title }}
                                </option>
                            @endforeach
                        </select>

                        <span class="custom-select-icon" style="right:23px;">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                <label class="step-title">
                    Make a New Assignment (Step-3)
                </label>

                <label class="">Assign to...</label>

                {{-- Users Multiple Select --}}
                <div class="form-group row">
                    <label class="col-md-12 form-control-label">Users</label>

                    <div class="col-md-12 custom-select-wrapper">
                        <select name="teachers[]"
                                class="form-control select2 custom-select-box"
                                multiple>
                            @foreach ($teachers as $key => $name)
                                <option value="{{ $key }}"
                                    {{ collect(old('teachers'))->contains($key) ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>

                        <span class="custom-select-icon" style="right:23px;">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                {{-- OR Department --}}
                <p class="mt-3 mb-1">OR</p>

                <div class="row">
                    <label class="col-md-12 form-control-label">Select Department</label>

                    <div class="col-md-12 custom-select-wrapper">
                        <select name="department_id"
                                class="form-control select2 custom-select-box">
                            <option value="">Select One</option>

                            @foreach ($departments as $row)
                                <option value="{{ $row->id }}">
                                    {{ $row->title }}
                                </option>
                            @endforeach
                        </select>

                        <span class="custom-select-icon" style="right:23px;">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                {{-- Message --}}
                <div class="form-group row mt-3">
                    <label class="col-md-12 form-control-label">Add Custom Message</label>
                    <div class="col-md-12">
                        <input class="form-control"
                               type="text"
                               name="message"
                               id="message">
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="form-group">
                    <div class="col-12 d-flex justify-content-end pr-0">

                        <a href="{{ route('admin.assessment_accounts.assignments', $user_id) }}"
                           class="btn btn-secondary mr-3">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            Create
                        </button>

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