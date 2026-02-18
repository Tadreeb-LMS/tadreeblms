@extends('backend.layouts.app')

@section('title', __('Assignments').' | '.app_name())

@push('after-styles')
<style>
.select2-selection__arrow {
    display: none !important;
}
.select2-container--default .select2-selection--single {
    border: 1px solid #ccc !important;
    border-radius: 5px !important;
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

@if ($user_id != NULL)
    <input type="hidden" name="user_id" value="{{ $user_id }}">
    <input type="hidden" name="user_type" value="2">
@else
    <input type="hidden" name="user_type" value="1">
@endif

<div class="pb-3 d-flex justify-content-between">
    <h4>@lang('Assign Course')</h4>

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

                {{-- Course --}}
                <div class="form-group row">
                    <label class="col-md-12 form-control-label">Course</label>

                    <div class="col-md-12 custom-select-wrapper">
                        <select class="form-control custom-select-box select2"
                                name="course_id"
                                required>
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

                {{-- Buttons --}}
                <div class="form-group mt-4">
                    <div class="col-12 d-flex justify-content-end pr-0">

                        <a href="{{ route('admin.assessment_accounts.assignments', $user_id) }}"
                           class="btn btn-secondary mr-3">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            Assign
                        </button>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

</form>

@endsection

@push('after-scripts')
@endpush
