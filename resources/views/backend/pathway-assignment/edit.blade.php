@extends('backend.layouts.app')

@section('title', __('Edit Pathway Assignment') . ' | ' . app_name())

@section('style')
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
    </style>
@endsection
@push('after-styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet" />
@endpush
@section('content')
    {{ html()->form('POST', '/user/pathway-assignments/' . $lpa->id)->acceptsFiles()->class('form-horizontal ajax')->open() }}
    @method('PUT')
    <div class="pb-3 d-flex justify-content-between align-items-center">
        <h4 class="">@lang('Edit Pathway Assignment')</h4>
        <div class="float-right">
            <!-- <a href="{{ url('/user/pathway-assignments') }}" class="add-btn">@lang('View Assignments')</a> -->
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="">
                        <label
                            style="font-size: 17px;font-weight: 600;padding-left: 12px;border-bottom: 1px solid #e7e7e7;padding-bottom: 11px;margin-bottom: 25px;display: block;">Make
                            a New Assignment (Step-1)</label>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-12 form-control-label required" for="title">Title</label>
                        <div class="col-md-12">
                            <input class="form-control" type="text" name="title" id="title"
                                placeholder="Title for assignment" value="{{ $lpa->title }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-12 form-control-label required" for="title">Due Date</label>
                        <div class="col-md-12">
                            <input class="form-control" type="date" name="due_date" id="due_date" value="{{ $lpa->due_date }}">
                        </div>
                    </div>

                    <div class="">
                        <label
                            style="font-size: 17px;font-weight: 600;padding-left: 12px;border-bottom: 1px solid #e7e7e7;padding-bottom: 11px;margin-bottom: 25px;display: block;">Make
                            a New Assignment (Step-2)</label>
                    </div>

                    {{-- <div class="">
                <label class="">Assign a...</label>
                </div> --}}
                    <br>
                    <div class="row mb-3">
                    <div class="col-md-12 form-control-label required" for="test_id">Learning Pathway</div>
                    <div class="col-md-12 mt-2">
                        <!-- <select class="form-control select2" name="learning_pathway_id">
                            <option value="" selected disabled>Select Pathway</option>
                            @foreach ($pathways as $value)
                            <option value="{{ $value->id }}">{{ $value->title }}</option>
                            @endforeach
                        </select> -->

                        <div class="custom-select-wrapper position-relative">
                            <select class="form-control select2" name="learning_pathway_id" required>
                                <option value="" selected disabled>Select Pathway</option>
                                @foreach ($pathways as $value)
                                <option value="{{ $value->id }}">{{ $value->title }}</option>
                                @endforeach
                            </select>
                            <span class="custom-select-icon">
                                <i class="fa fa-chevron-down"></i>
                            </span>
                        </div>

                    </div>
                </div>

                    <div class="">
                        <label
                            style=" font-size: 17px;font-weight: 600;padding-left: 12px;border-bottom: 1px solid #e7e7e7;padding-bottom: 11px;margin-bottom: 25px;display: block;">Make
                            a New Assignment (Step-3)</label>
                    </div>

                    <div class="">
                        <label class="required">Assign to...</label>
                    </div>


                    <div class="form-group row">
                        <label class="col-md-12 form-control-label" for="test_id">Users</label>
                        <div class="col-md-12">
                            <select name="teachers[]" class="form-control select2 js-example-placeholder-multiple" multiple>
                                @foreach ($teachers as $key => $val)
                                    <option value="{{ $key }}" @if(in_array($key, json_decode($lpa->assigned_to))) selected @endif> {{ $val }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row justify-content-end">
                        <div class="col-6 col-md-4">
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
