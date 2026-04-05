@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')
@section('title', __('labels.backend.lessons.title') . ' | ' . app_name())

@push('after-styles')
    <style>
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

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 26px;
            position: absolute;
            top: 4px;
            right: 1px;
            width: 20px;
        }

        .dropdown-item {
            border-bottom: none;
        }

        table.dataTable td {
            overflow: visible;
            position: relative;
        }
    </style>
@endpush

@section('content')

<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4>@lang('labels.backend.lessons.title')</h4>
    @can('lesson_create')
        <a href="{{ route('admin.lessons.create') }}@if(request('course_id')){{'?course_id=' . request('course_id')}}@endif"
            class="btn add-btn">@lang('strings.backend.general.app_add_new')</a>
    @endcan
</div>

<div class="card">
    <div class="card-body">

        {{-- Filter Bar --}}
        <div class="row align-items-end mb-3">
            <div class="col-12 col-lg-4 form-group mb-0">
                <label for="course_id" class="control-label">{{ trans('labels.backend.lessons.fields.course') }}</label>
                <div class="custom-select-wrapper">
                    <select id="course_id" name="course_id" class="form-control custom-select-box select2">
                        <option value="">{{ __('All Courses') }}</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                    <span class="custom-select-icon"><i class="fa fa-chevron-down"></i></span>
                </div>
            </div>
            <div class="col-12 col-lg-3 form-group mb-0">
                <label for="status_filter" class="control-label">{{ __('Published Status') }}</label>
                <div class="custom-select-wrapper">
                    <select id="status_filter" class="form-control custom-select-box">
                        <option value="">{{ __('All') }}</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>
                            {{ __('Published') }}
                        </option>
                        <option value="unpublished" {{ request('status') === 'unpublished' ? 'selected' : '' }}>
                            {{ __('Unpublished') }}
                        </option>
                    </select>
                    <span class="custom-select-icon"><i class="fa fa-chevron-down"></i></span>
                </div>
            </div>
            <div class="col-12 col-lg-2 form-group mb-0">
                <a href="{{ route('admin.lessons.index') }}"
                    class="btn btn-secondary btn-block">{{ __('Reset Filters') }}</a>
            </div>
        </div>

        {{-- Trash Toggle --}}
        <div class="d-block">
            <ul class="list-inline">
                <li class="list-inline-item">
                    <a href="{{ route('admin.lessons.index') }}"
                        style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">
                        {{ trans('labels.general.all') }}
                    </a>
                </li>
                |
                <li class="list-inline-item">
                    <a href="{{ trashUrl(request()) }}"
                        style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">
                        {{ trans('labels.general.trash') }}
                    </a>
                </li>
            </ul>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table id="myTable"
                class="table table-striped custom-teacher-table @can('lesson_delete') @if(request('show_deleted') != 1) dt-select @endif @endcan">
                <thead>
                    <tr>
                        @can('lesson_delete')
                            @if(request('show_deleted') != 1)
                                <th style="text-align:center;"><input class="mass" type="checkbox" id="select-all" /></th>
                            @endif
                        @endcan
                        <th>@lang('labels.general.sr_no')</th>
                        <th>@lang('labels.backend.lessons.fields.title')</th>
                        <th>Course</th>
                        <th>@lang('Lesson Start Date')</th>
                        <th>@lang('Duration [minutes]')</th>
                        <th>@lang('Attendance [count]')</th>
                        <th>@lang('labels.backend.courses.fields.qr_code')</th>
                        <th>@lang('labels.backend.lessons.fields.published')</th>
                        <th style="text-align:center;">@lang('strings.backend.general.actions') &nbsp;</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>

@stop

@push('after-scripts')
    <script>
        $(document).ready(function () {
            @php
                $show_deleted = request('show_deleted') == 1 ? 1 : 0;
                $ajaxRoute = route('admin.lessons.get_data', [
                    'show_deleted' => $show_deleted,
                    'course_id' => request('course_id', ''),
                    'status' => request('status', ''),
                ]);
            @endphp

            var ajaxRoute = '{{ $ajaxRoute }}'.replace(/&amp;/g, '&');

            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: "<'table-controls'lfB>" +
                    "<'table-responsive't>" +
                    "<'d-flex justify-content-between align-items-center mt-3'ip><'actions'>",
                buttons: [
                    {
                        extend: 'collection',
                        text: '<i class="fa fa-download icon-styles"></i>',
                        className: '',
                        buttons: [
                            { extend: 'csv', text: 'CSV', exportOptions: { columns: [1, 2, 3, 4, 5] } },
                            { extend: 'pdf', text: 'PDF', exportOptions: { columns: [1, 2, 3, 4, 5] } },
                        ],
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-eye icon-styles" aria-hidden="true"></i>',
                        className: '',
                    },
                ],
                ajax: ajaxRoute,
                columns: [
                    @if(request('show_deleted') != 1)
                                                        {
                            data: function (data) {
                                return '<input type="checkbox" class="single" name="id[]" value="' + data.id + '" />';
                            },
                            orderable: false,
                            searchable: false,
                            name: 'id',
                        },
                    @endif
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', searchable: false, orderable: false },
                    { data: 'title', name: 'title' },
                    { data: 'course', name: 'course.title', defaultContent: 'N/A' },
                    { data: 'lesson_start_date', name: 'lesson_start_date' },
                    { data: 'duration', name: 'duration' },
                    { data: 'attendance', name: 'attendance' },
                    { data: 'qr_code', name: 'qr_code' },
                    { data: 'published', name: 'published' },
                    { data: 'actions', name: 'actions' },
                ],
                @if(request('show_deleted') != 1)
                                                    columnDefs: [
                        { width: '5%', targets: 0 },
                        { className: 'text-center', targets: [0] },
                    ],
                @endif
                initComplete: function () {
                    let $searchInput = $('#myTable_filter input[type="search"]');
                    $searchInput
                        .addClass('custom-search')
                        .wrap('<div class="search-wrapper position-relative d-inline-block"></div>')
                        .after('<i class="fa fa-search search-icon"></i>');

                    $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
                },
                createdRow: function (row, data) {
                    $(row).attr('data-entry-id', data.id);
                },
                language: {
                url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{ $locale_full_name }}.json',
                buttons: {
                    colvis: '{{ trans("datatable.colvis") }}',
                    pdf: '{{ trans("datatable.pdf") }}',
                    csv: '{{ trans("datatable.csv") }}',
                },
                search: '',
            },
                            });

        @can('lesson_delete')
            @if(request('show_deleted') != 1)
                $('.actions').html(
                    '<a href="{{ route('admin.lessons.mass_destroy') }}" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left:20px;">Delete selected</a>'
                );
            @endif
        @endcan

        $('.js-example-placeholder-single').select2({
            placeholder: '{{ trans("labels.backend.lessons.select_course") }}',
        });

        function applyFilters() {
            var courseId = $('#course_id').val();
            var status = $('#status_filter').val();
            var params = [];

            if (courseId) params.push('course_id=' + courseId);
            if (status) params.push('status=' + status);

            window.location.href = '{{ route('admin.lessons.index') }}'
                + (params.length ? '?' + params.join('&') : '');
        }

        $(document).on('change', '#course_id, #status_filter', applyFilters);
                        });
    </script>
@endpush