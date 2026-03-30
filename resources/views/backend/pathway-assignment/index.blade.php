@extends('backend.layouts.app')
@section('title', __('admin_pages.pathway_assignments.title') . ' | ' . app_name())
@push('after-styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet" />
  <style>
       
    </style>
@endpush
@section('content')

<div class="pb-3 align-items-center d-flex justify-content-between">
    <h5>{{ __('admin_pages.pathway_assignments.title') }}</h5>
    @can('course_create')
        <div >
            <a href="{{ url('/user/pathway-assignments/create') }}" class="btn add-btn">+ {{ __('admin_pages.pathway_assignments.make_new_assignment') }}</a>
        </div>
    @endcan
</div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <form id="advace_filter">
                        <div class="row">

                            
                            <div class="col-lg-4 col-sm-6 col-xs-12 mt-3" id="email-block">
                                {{ __('admin_pages.pathway_assignments.select_employee_by_email') }}
                                <div class="custom-select-wrapper mt-2">
                                <select class="form-control custom-select-box select2 js-example-placeholder-single" name="user" id="user" >
                                    <option value="">{{ __('admin_pages.pathway_assignments.select') }}</option>
                                    @if($internal_users)
                                        @foreach($internal_users as $user)
                                            <option @if($user->id == request()->user) selected @endif value="{{ $user->id }}">{{ $user->email }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <span class="custom-select-icon" style="right: 10px;">
                                            <i class="fa fa-chevron-down"></i>
                                        </span>
                                </div>
                            </div>
                            
                            
                            
                            <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">

                                {{ __('admin_pages.pathway_assignments.select_course') }}
                                <div class="custom-select-wrapper mt-2">
                                    <select name="course_id" id="course_id" class="select2 form-control custom-select-box">
                                        <option value="">{{ __('admin_pages.pathway_assignments.select') }}</option>
                                        @if($published_courses)
                                        @foreach($published_courses as $row)
                                        <option @if($row->id == request()->course_id) selected @endif value="{{ $row->id }}">{{ $row->title }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                    <span class="custom-select-icon">
                                        <i class="fa fa-chevron-down"></i>
                                    </span>
                                </div>
                            </div>
                            
                            
                           

                           

                            <div class="col-lg-4 col-md-12 col-sm-6 col-xs-12 d-flex align-items-center mt-4">

                            <div class="d-flex justify-content-between mt-3">
                                <div>
                                    <button class="btn btn-primary" id="advance-search-btn" type="submit">{{ __('admin_pages.pathway_assignments.advance_search') }}</button>
                                </div>
                                <div>
                                    <button class="btn btn-danger ml-3" id="reset" type="button">{{ __('admin_pages.pathway_assignments.reset') }}</button>

                                </div>
                                
                            </div>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table id="myTable"
                            class="table dt-select custom-teacher-table table-striped @if (auth()->user()->isAdmin()) @if (request('show_deleted') != 1) dt-select @endif @endcan">
                            <thead>
                                <tr>
                                    <th>{{ __('admin_pages.pathway_assignments.assign_title') }}</th>
                                    {{-- <th>@lang('Pathway Name')</th> --}}
                                    <th>{{ __('admin_pages.pathway_assignments.course_name') }}</th>
                                    <th>{{ __('admin_pages.pathway_assignments.assigned_by') }}</th>
                                    <th>{{ __('admin_pages.pathway_assignments.assigned_users') }}</th>
                                    <th>{{ __('admin_pages.pathway_assignments.user_email') }}</th>
                                    <th>{{ __('admin_pages.pathway_assignments.assign_date') }}</th>
                                    <th>{{ __('admin_pages.pathway_assignments.due_date') }}</th>
                                    {{-- <th>@lang('Action')</th> --}}
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('after-scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        window.pathwayAssignmentsI18n = {
            endpoint: @json(route('admin.pathway-assignments.index')),
            emptyTable: @json(__('admin_pages.pathway_assignments.no_data_available'))
        };
    </script>
    <script src="/js/helpers/confirm-modal.js"></script>
    <script src="/js/helpers/load-modal.js"></script>
    <script src="/js/pages/learning-pathway-assignment.js"></script>
@endpush
