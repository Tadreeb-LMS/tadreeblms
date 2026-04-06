@extends('backend.layouts.app')

@section('title', __('Send Email Notification') . ' | ' . app_name())

@section('style')

@endsection
@push('after-styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet" />
<style>
    .step_assign {
        font-size: 17px;
        font-weight: 600;
        padding-left: 12px;
        border-bottom: 1px solid #e7e7e7;
        padding-bottom: 11px;
        margin-bottom: 25px;
        display: block;
    }



    .form-check-input {
        position: absolute;
        margin-top: 0.3rem;
        margin-left: 0.75px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
    display: none !important;
}
   .select2-container .select2-search--inline .select2-search__field {
    box-sizing: border-box;
    border: none;
    font-size: 100%;
    margin-top: 5px;
    padding-left: 8px;
}

.select2-container--default .select2-selection--multiple:focus {
    outline: none !important;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important;
    border-color: #007bff !important;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
     outline: none !important;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important;
    border-color: #007bff !important;
}
.select2-container--default .select2-selection--multiple{
    border: 1px solid #ccc !important;
}

.select2-container--default .select2-selection--single {
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow b{
    display: none;
}
.select2-container .select2-selection--single .select2-selection__rendered {
    display: block;
    padding-left: 10px;
    padding-right: 20px;
    padding-top: 1px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.select2-container .select2-selection--single {
    box-sizing: border-box;
    cursor: pointer;
    display: block;
    height: 32px;
    user-select: none;
    -webkit-user-select: none;
}
</style>
@endpush
@section('content')
<div class="pb-3">
    <h4 class="">@lang('Send Email Notification')</h4>
</div>
<div class="card">
    <form action="{{ url('/user/send-email-notification') }}" method="post" class="ajax" enctype="multipart/form-data">
        <div class="card-body">
            <div class="row">

                <div class="col-12">


                    <div class=" row">
                        <label class="col-lg-3 col-md-12 col-sm-12 form-control-label required"
                            for="test_id">@lang('Users')</label>
                        <div class="col-lg-9 col-md-12 col-sm-12 custom-select-wrapper">
                            <select name="users[]" class="form-control custom-select-box select2 js-example-questions-placeholder-multiple"
                                multiple>
                                @foreach ($users as $row)
                                <option value="{{ $row->id }}"> {{ $row->full_name }} </option>
                                @endforeach
                            </select>
                            <span class="custom-select-icon" style="right: 23px;">
                                <i class="fa fa-chevron-down"></i>
                            </span>
                        </div>
                    </div>

                    <br>
                    @lang('OR')
                    <br>

                    <div class="form-group row mt-2">
                        <label class="col-lg-3 col-md-12 col-sm-12 form-control-label required"
                            for="first_name">@lang('Select Department')</label>
                        <div class="col-lg-9 col-md-12 col-sm-12 mb-3 custom-select-wrapper">
                            <select name="department_id" class="form-control custom-select-box select2 js-example-placeholder-single">
                                <option value="" selected disabled> @lang('Select One') </option>
                                @foreach ($departments as $row)
                                <option value="{{ $row->id }}"> {{ $row->title }} </option>
                                @endforeach
                            </select>
                            <span class="custom-select-icon" style="right: 23px;">
                                <i class="fa fa-chevron-down"></i>
                            </span>
                        </div><!--col-->
                    </div>

                    @lang('OR')
                    <br>

                    <div class="form-group row mt-2">
                        <label class="col-lg-3 col-md-12 col-sm-12 mb-4 form-control-label"
                            for="first_name">@lang('Import Users')</label>
                        <div class="col-lg-9 col-md-12 col-sm-12 mb-4">

                            <div class="custom-file-upload-wrapper">
                                <input type="file" name="import_users" id="customFileInput" class="custom-file-input ">
                                <label for="customFileInput" class="custom-file-label">
                                    <i class="fa fa-upload mr-1"></i> Choose a file
                                </label>
                            </div>
                        </div>
                    </div>

                    @lang('OR')
                    <br>

                    <div class="form-group row mt-2">
                        <label class="col-lg-3 col-md-12 col-sm-12 form-control-label required"
                            for="first_name">@lang('Send to all users')</label>
                        <div class="col-lg-9 col-md-12 col-sm-12 mb-3 or_optional">
                            <input class="form-check-input" type="checkbox" value="1" id="select_all_users" name="select_all_users">
                        </div><!--col-->
                    </div>

                    <div class="form-group row mt-2">
                        <label for="emailContent" class="col-lg-3 col-md-12 col-sm-12 form-control-label required">Subject</label>
                        <div class="col-lg-9 col-md-12 col-sm-12 mb-3 or_optional">
                            <input type="text" name="subject" class="form-control" placeholder="Please write subject here">
                        </div>
                    </div>

                    <div class="form-group row mt-2">
                        <label for="emailContent" class="col-lg-3 col-md-12 col-sm-12 form-control-label required">Register Button</label>
                        <div class="col-lg-9 col-md-12 col-sm-12 mb-3 or_optional">
                            <input type="text" name="register_button" class="form-control" placeholder="Please add register link">
                        </div>
                    </div>

                    <div class="form-group row mt-2">
                        <label for="emailContent" class="col-lg-3 col-md-12 col-sm-12 form-control-label required">Email
                            Content</label>
                        <div class="col-lg-9 col-md-12 col-sm-12 mb-3 or_optional">
                            <textarea class="form-control" id="emailContent" name="email_content"
                                placeholder="Enter the email content here..."></textarea>
                        </div>
                    </div>
                    <div class="form-group justify-content-end row">
                        <button class="add-btn mr-3" type="submit">
                            <span class="btn-text">Send Notification</span>
                            <span class="btn-spinner d-none">
                                <i class="fa fa-spinner fa-spin mr-2"></i>Loading...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
@push('after-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="/js/helpers/form-submit.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>

<script>
    $('[name="users[]"]').change(function(e) {
        if ($('[name="department_id"]').val() && $('[name="users[]"]').val()) {
            $('[name="department_id"]').val('').trigger('change');
        }
    });

    $(document).ready(function() {
        ClassicEditor
            .create($('#emailContent')[0]) // Use jQuery to select the element
            .then(editor => {
                console.log('Editor initialized');
                // Optionally store the editor instance for later use
                $('#emailContent').data('editor', editor);
            })
            .catch(error => {
                console.error('There was a problem initializing the editor.', error);
            });
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
<style>
    .ck-editor__editable {
        height: 150px !important;
    }

    .form-check-input {
        position: absolute;
        margin-top: 0.3rem;
        margin-left: 0.75px !important;
    }
</style>