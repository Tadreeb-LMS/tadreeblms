@extends('backend.layouts.app')
@section('title', 'Employee'.' | '.app_name())
@push('after-styles')
    <link rel="stylesheet" href="{{asset('assets/css/colors/switch.css')}}">
    
    <style>
   .switch.switch-3d.switch-lg {
    width: 40px;
    height: 20px;
}
.switch.switch-3d.switch-lg .switch-handle {
    width: 20px;
    height: 20px;
}
    </style>
@endpush

@section('content')

<form action="{{ route('admin.employee.update', $teacher->id) }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="d-flex justify-content-between align-items-center pb-3">
    <h4>Edit Trainee</h4>
    <a href="{{ route('admin.teachers.index') }}" class="add-btn">View Trainee</a>
</div>

<div class="card">
<div class="card-body pt-0">
<div class="row">

{{-- First Name --}}
<div class="col-lg-6 mt-3">
    <label for="first_name" class="form-control-label">First Name</label>
    <input type="text" name="first_name" id="first_name" class="form-control"
           value="{{ $teacher->first_name }}" required>
</div>

{{-- Last Name --}}
<div class="col-lg-6 mt-3">
    <label for="last_name" class="form-control-label">Last Name</label>
    <input type="text" name="last_name" id="last_name" class="form-control"
           value="{{ $teacher->last_name }}" required>
</div>

{{-- Arabic First Name --}}
<div class="col-lg-6 mt-3">
    <label class="form-control-label">First Name (Arabic)</label>
    <input type="text" name="arabic_first_name" class="form-control"
           value="{{ $teacher->arabic_first_name }}" required>
</div>

{{-- Arabic Last Name --}}
<div class="col-lg-6 mt-3">
    <label class="form-control-label">Last Name (Arabic)</label>
    <input type="text" name="arabic_last_name" class="form-control"
           value="{{ $teacher->arabic_last_name }}" required>
</div>

{{-- Employee ID --}}
<div class="col-lg-6 mt-3">
    <label class="form-control-label">Employee ID</label>
    <input type="text" name="emp_id" class="form-control"
           value="{{ $teacher->emp_id }}" required>
</div>

{{-- Email --}}
<div class="col-lg-6 mt-3">
    <label class="form-control-label">Email</label>
    <input type="email" class="form-control" value="{{ $teacher->email }}" readonly>
</div>

{{-- Image --}}
<div class="col-lg-6 mt-3">
    <label class="form-control-label">Image</label>
    <input type="file" name="image" class="form-control">

    <div class="mt-3">
        <label class="form-control-label">Uploaded Image</label>
        <img src="{{ asset('public/uploads/employee/'.$teacher->avatar_location) }}" class="w-100">
    </div>
</div>

{{-- Department --}}
<div class="col-lg-6 mt-3">
    <label class="form-control-label">Department</label>
    <select name="department" class="form-control">
        <option value="">Select One</option>
        @foreach($departments as $row)
            <option value="{{ $row->id }}"
                {{ optional($teacher->employee->department_details)->id == $row->id ? 'selected' : '' }}>
                {{ $row->title }}
            </option>
        @endforeach
    </select>
</div>

{{-- Gender --}}
<div class="col-lg-12 mt-3">
    <label class="form-control-label">Gender</label><br>
    <label><input type="radio" name="gender" value="male" {{ $teacher->gender == 'male' ? 'checked' : '' }}> Male</label>
    <label class="ml-3"><input type="radio" name="gender" value="female" {{ $teacher->gender == 'female' ? 'checked' : '' }}> Female</label>
</div>

{{-- Preferred Language --}}
<div class="col-lg-12 mt-3">
    <label class="form-control-label">Preferred Language</label><br>
    <label><input type="radio" name="fav_lang" value="english" {{ $teacher->fav_lang == 'english' ? 'checked' : '' }}> English</label>
    <label class="ml-3"><input type="radio" name="fav_lang" value="arabic" {{ $teacher->fav_lang == 'arabic' ? 'checked' : '' }}> Arabic</label>
</div>

{{-- Position --}}
<div class="col-lg-6 mt-3">
    <label class="form-control-label">Position</label>
    <select name="position" class="form-control">
        <option value="">Select One</option>
        @foreach($positions as $row)
            <option value="{{ $row->title }}"
                {{ $new_positions->position == $row->title ? 'selected' : '' }}>
                {{ $row->title }}
            </option>
        @endforeach
    </select>
</div>

{{-- Status --}}
<div class="col-lg-6 mt-3">
    <label class="form-control-label">Status</label><br>
    <label class="switch switch-lg switch-3d switch-primary">
        <input type="checkbox" name="active" value="1"
               class="switch-input" {{ $teacher->active ? 'checked' : '' }}>
        <span class="switch-label"></span>
        <span class="switch-handle"></span>
    </label>
</div>

{{-- Buttons --}}
<div class="col-12 d-flex justify-content-between mt-4">
    <a href="{{ route('admin.teachers.index') }}" class="cancel-btn">Cancel</a>
    <button type="submit" class="add-btn">Update</button>
</div>

</div>
</div>
</div>
</form>
@endsection

@push('after-scripts')
    <script>
        $(document).on('change', '#payment_method', function(){
            if($(this).val() === 'bank'){
                $('.paypal_details').hide();
                $('.bank_details').show();
            }else{
                $('.paypal_details').show();
                $('.bank_details').hide();
            }
        });
    </script>
    <script>
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const label = input.nextElementSibling;
            const fileName = e.target.files.length > 0 ? e.target.files[0].name : 'Choose a file';
            label.innerHTML = '<i class="fa fa-upload mr-1"></i> ' + fileName;
        });
    });
</script>
@endpush