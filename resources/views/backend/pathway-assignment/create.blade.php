@extends('backend.layouts.app')

@section('title', __('Assign Pathway') . ' | ' . app_name())

@push('after-styles')
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

    /* 1. Hide default Select2 arrow completely */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        display: none !important;
    }

    /* 2. Custom select wrapper positioning */
    .custom-select-wrapper {
        position: relative;
    }

    /* 3. Custom icon positioning */
    .custom-select-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: #999;
        transition: color 0.3s ease;
        z-index: 10;
    }

    /* 4. Add space for the custom icon inside Select2 box */
    .select2-container--default .select2-selection--single {
        padding-right: 2.5rem !important;
        border: 1px solid #ccc !important;
        height: 38px;
        display: flex;
        align-items: center;
    }

    /* 5. Blue border on focus */
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #007bff !important;
    }

    /* 6. Icon turns blue on focus */
    .select2-container--focus~.custom-select-icon i {
        color: #007bff;
    }
</style>
@endpush
@push('after-styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet" />
@endpush
@section('content')

<form method="POST"
      action="{{ route('admin.pathway-assignments.store') }}"
      enctype="multipart/form-data"
      class="form-horizontal ajax">

@csrf

<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4>@lang('Create Assignment')</h4>
    <div>
        <a href="{{ url('/user/pathway-assignments') }}" class="add-btn">@lang('View Assignments')</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12">

                <label class="step-title">
                    Make a New Assignment (Step-1)
                </label>

                {{-- Title --}}
                <div class="form-group row">
                    <label class="col-md-12 form-control-label required">Title</label>
                    <div class="col-md-12">
                        <input type="text" name="title" id="title" class="form-control"
                               placeholder="Title for assignment" value="{{ old('title') }}" required autofocus>
                    </div>
                </div>

                {{-- Due Date --}}
                <div class="form-group row">
                    <label class="col-md-12 form-control-label required">Due Date</label>
                    <div class="col-md-12">
                        <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date') }}" required>
                    </div>
                </div>

                <label class="step-title">
                    Make a New Assignment (Step-2)
                </label>

                {{-- Learning Pathway --}}
                <div class="form-group row mb-3">
                    <label class="col-md-12 form-control-label required">Learning Pathway</label>
                    <div class="col-md-12 mt-2">
                        <div class="custom-select-wrapper position-relative">
                            <select name="learning_pathway_id" class="form-control select2" required>
                                <option value="" selected disabled>Select Pathway</option>
                                @foreach ($pathways as $value)
                                    <option value="{{ $value->id }}" {{ old('learning_pathway_id') == $value->id ? 'selected' : '' }}>
                                        {{ $value->title }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="custom-select-icon"><i class="fa fa-chevron-down"></i></span>
                        </div>
                    </div>
                </div>

                <label class="step-title">
                    Make a New Assignment (Step-3)
                </label>

                <label class="required">Assign to...</label>

                {{-- Users --}}
                <div class="form-group row">
                    <label class="col-md-12 form-control-label">Users</label>
                    <div class="col-md-12">
                        <div class="custom-select-wrapper position-relative">
                            <select name="teachers[]" class="form-control select2 js-example-placeholder-multiple" multiple>
                                @foreach ($teachers as $key => $value)
                                    <option value="{{ $key }}" {{ in_array($key, (array) old('teachers')) ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="custom-select-icon"><i class="fa fa-chevron-down"></i></span>
                        </div>
                    </div>
                </div>

                <p class="mt-3 mb-1">OR</p>

                {{-- Department --}}
                <div class="row">
                    <label class="col-md-12 form-control-label">@lang('Select Department')</label>
                    <div class="col-md-12">
                        <div class="custom-select-wrapper position-relative">
                            <select name="department_id" class="form-control select2">
                                <option value="" selected disabled>@lang('Select Department')</option>
                                @foreach ($departments as $row)
                                    <option value="{{ $row->id }}" {{ old('department_id') == $row->id ? 'selected' : '' }}>
                                        {{ $row->title }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="custom-select-icon"><i class="fa fa-chevron-down"></i></span>
                        </div>
                    </div>
                </div>

                <br>

                {{-- Custom Message --}}
                <div class="form-group row">
                    <label class="col-md-12 form-control-label">Add Custom Message</label>
                    <div class="col-md-12">
                        <input type="text" name="message" id="message" class="form-control" value="{{ old('message') }}">
                    </div>
                </div>

                {{-- Submit --}}
                <div class="form-group row">
                    <div class="col-12 text-right mt-3">
                        <button type="submit" class="btn btn-primary">
                            @lang('buttons.general.crud.create')
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="/js/helpers/form-submit.js"></script>

<script>
    $('[name="teachers[]"]').change(function(e) {
        if ($('[name="department_id"]').val() && $('[name="teachers[]"]').val()) {
            $('[name="department_id"]').val('').trigger('change');
        }
    });
    // $('[name="department_id"]').change(function (e) { 
    //     if ($('[name="teachers[]"]').val() && $('[name="department_id"]').val()) {
    //         $('[name="teachers[]"]').val('').trigger('change');
    //     }
    // });
</script>
@endpush