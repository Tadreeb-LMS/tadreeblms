@extends('backend.layouts.app')

@section('title', __('labels.backend.courses.title').' | '.app_name())

@section('content')
<form method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data">
@csrf


 <div class="card">
    <div class="card-header">
        <h3 class="page-title float-left">@lang('labels.backend.courses.create')</h3>
        <div class="float-right">
            <a href="{{ route('admin.courses.index') }}" class="btn btn-success">@lang('labels.backend.courses.view')</a>
        </div>
    </div>

        <div class="card-body">
      <!-- <input type="radio" onclick="check()" id="internal_student" name="internal_student" value="internal_student">
      <label for="html">Internal Students</label>
      <input type="radio" onclick="check()" id="department" name="department" value="department">
      <label for="css">Select Department</label><br> -->

        @if (Auth::user()->isAdmin())
        <div class="row">
            <div class="col-10 form-group">
                <label for="internal_students" class="control-label">{{ trans('labels.backend.courses.fields.internal_students') }}</label>
                <select name="internalStudents[]" class="form-control select2 js-example-internal-student-placeholder-multiple" multiple>
                    @foreach($internalStudents as $id => $name)
                        <option value="{{ $id }}" {{ in_array($id, old('internalStudents', [])) ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif
            <input type="hidden" name="course_id">
        <div class="row">
            <label class="col-md-2 form-control-label" for="first_name">Select Department</label>
            <div class="col-md-10">
                <select name="department_id" class="form-control">
                    <option value=""> Select One </option>
                    @foreach($departments as $row)
                    <option value="{{ $row->id }}"> {{ $row->title }} </option>
                    @endforeach
                </select>
            </div>
            <!--col-->
        </div>

        <div class="row">
            <div class="col-12 text-center form-group">
                <button type="submit" class="btn btn-lg btn-danger">{{ trans('strings.backend.general.app_save') }}</button>
            </div>
        </div>
    </div>
</div>
</form>
@stop

@push('after-scripts')

<script>
    // function check(){
    //    var ins_std = $('#internal_student').val();
    //    var dpt = $('#department').val();
    //     if(ins_std == 'internal_student'){
    //         $("#internal_student").css("display: block;");
    //     }
    //     if(dpt == 'department'){
    //         $("#department").css("block");
    //  }
    // }
</script>

@endpush