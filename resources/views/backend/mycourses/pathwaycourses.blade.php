@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')
@section('title', __('labels.backend.courses.title') . ' | ' . app_name())
@push('after-styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet" />
@endpush

@section('content')
    <style>
        .create_done {
            padding: 10px 40px;
            background: #20a8d8;
            border: none;
            outline: none;
            float: right;
            margin: 0 15px 0 0;
            color: #fff;

        }

        .create_done.next {
            background: #4dbd74;
        }
    </style>

    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">@lang('labels.backend.courses.title')</h3>
            @can('course_create')
                <div class="float-right">
                    <a href="{{ route('admin.courses.create') }}" class="btn btn-success">@lang('strings.backend.general.app_add_new')</a>

                </div>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <div class="d-block">
                    <ul class="list-inline">
                        <li class="list-inline-item">
                            <a href="{{ route('admin.courses.index') }}"
                                style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">{{ trans('labels.general.all') }}</a>
                        </li>
                        |
                        <li class="list-inline-item">
                            <a href="{{ route('admin.courses.index') }}?show_deleted=1"
                                style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">{{ trans('labels.general.trash') }}</a>
                        </li>
                    </ul>
                </div>


                <table id="myTable"
                    class="table table-bordered table-striped">
                    <thead>
                        <tr>
                           <th>@lang('Pathway')</th>
                            <th>@lang('Lebel')</th>
                            <th>@lang('Course Title')</th>
                            <th>@lang('Category')</th>
                            
                            <th>@lang('Lessons')</th>
                            <th>@lang('Duration')</th>
                            <th>@lang('Due Date')</th>
                            <th>@lang('Progress')</th>
                            <th>@lang('Download Certificate')</th>
                            <th>@lang('Actions')</th>
                        </tr>
                    </thead>

                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@push('after-scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {

            var route = '{{ route('user.mypathwaycourses.getdata') }}';

            

             $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                ajax: route,
                columns: [
                    { data: 'pathway', name: 'pathway' },
                    { data: 'lebel', name: 'lebel' },
                    { data: 'title', name: 'title' },
                    { data: 'category', name: 'category' },
                    
                    { data: 'duration', name: 'duration' },
                    { data: 'lessons', name: 'lessons' },
                    { data: 'due_date', name: 'due_date' },
                    { data: 'progress', name: 'progress' },
                    { data: 'download_certificate', name: 'download_certificate' },
                    { data: 'actions', name: 'actions' }
                ]
             });
            
        });

      
    </script>
@endpush
