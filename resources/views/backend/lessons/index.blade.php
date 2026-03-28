@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')
@section('title', __('labels.backend.lessons.title').' | '.app_name())
@push('after-styles')
 <style>
       
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
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 26px;
    position: absolute;
    top: 4px;
    right: 1px;
    width: 20px;
}
    .dropdown-item{
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
   <h4>
      @lang('labels.backend.lessons.title')
   </h4>
    @can('lesson_create')
       <div class="">
           <a href="{{ route('admin.lessons.create') }}@if(request('course_id')){{'?course_id='.request('course_id')}}@endif"
              class="btn add-btn">@lang('strings.backend.general.app_add_new')</a>

       </div>
   @endcan
 
</div>
    <div class="card">
        <!-- <div class="card-header">
            
            <h3 class="page-title d-inline">@lang('labels.backend.lessons.title')</h3>
            @can('lesson_create')
                <div class="float-right">
                    <a href="{{ route('admin.lessons.create') }}@if(request('course_id')){{'?course_id='.request('course_id')}}@endif"
                       class="btn btn-success">@lang('strings.backend.general.app_add_new')</a>

                </div>
            @endcan
        </div> -->
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-6 form-group">
                    <label for="course_id" class="control-label">
    {{ trans('labels.backend.lessons.fields.course') }}
</label>
                    <div class=" custom-select-wrapper">
    <select name="course_id" id="course_id" class="form-control custom-select-box select2">
        <option value="">Select Course</option>
        @foreach($courses as $id => $course)
            <option value="{{ $id }}" 
                @if(request('course_id') == $id || old('course_id') == $id) selected @endif>
                {{ $course }}
            </option>
        @endforeach
    </select>
    <span class="custom-select-icon">
        <i class="fa fa-chevron-down"></i>
    </span>
</div>
                </div>
            </div>
            <div class="d-block">
                <ul class="list-inline">
                    <li class="list-inline-item">
                        <a href="{{ route('admin.lessons.index') }}"
                           style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">{{trans('labels.general.all')}}</a>
                    </li>
                    |
                    <li class="list-inline-item">
                        <a href="{{trashUrl(request()) }}"
                           style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">{{trans('labels.general.trash')}}</a>
                    </li>
                </ul>
            </div>

            <div class="table-responsive">

                <table id="myTable"
                       class="table table-striped custom-teacher-table @can('lesson_delete') @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                    <thead>
                    <tr>
                        @can('lesson_delete')
                            @if ( request('show_deleted') != 1 )
                                <th style="text-align:center;"><input class="mass" type="checkbox" id="select-all"/>
                                </th>@endif
                        @endcan
                            <th>@lang('labels.general.sr_no')</th>

                            {{-- <th>@lang('labels.general.id')</th> --}}
                        <th>@lang('labels.backend.lessons.fields.title')</th>
                        <th>@lang('Lesson Start Date')</th>
                        <th>@lang('Duration [minutes]')</th>
                        <th>@lang('Attendance [count]')</th>
                        <th>@lang('labels.backend.courses.fields.qr_code')</th>
                        <th>@lang('labels.backend.lessons.fields.published')</th>
                        @if( request('show_deleted') == 1 )
                            <th style="text-align:center;">@lang('strings.backend.general.actions') &nbsp;</th>
                        @else
                            <th style="text-align:center;">@lang('strings.backend.general.actions') &nbsp;</th>
                        @endif
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
    <script>

        $(document).ready(function () {
            var route = '{{route('admin.lessons.get_data')}}';


            @php
                $show_deleted = (request('show_deleted') == 1) ? 1 : 0;
                $course_id = (request('course_id') != "") ? request('course_id') : '';
            $route = route('admin.lessons.get_data',['show_deleted' => $show_deleted,'course_id' => $course_id]);
            @endphp

            route = '{{$route}}';
            route = route.replace(/&amp;/g, '&');

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
            {
                extend: 'csv',
                text: 'CSV',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
                }
            },
            {
                extend: 'pdf',
                text: 'PDF',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
                }
            }
        ]
    },
      {extend: 'colvis',
    text: '<i class="fa fa-eye icon-styles" aria-hidden="true"></i>',
    className: ''},
],
                // buttons: [
                //     {
                //         extend: 'csv',
                //         exportOptions: {
                //             columns: [ 1, 2, 3, 4]
                //         }
                //     },
                //     {
                //         extend: 'pdf',
                //         exportOptions: {
                //             columns: [ 1, 2, 3, 4]
                //         }
                //     },
                //     'colvis'
                // ],
                ajax: route,
                columns: [
                        @if(request('show_deleted') != 1)
                    {
                        "data": function (data) {
                            return '<input type="checkbox" class="single" name="id[]" value="' + data.id + '" />';
                        }, "orderable": false, "searchable": false, "name": "id"
                    },
                        @endif
                    {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable:false},
                    //{data: "id", name: 'id'},

                    {data: "title", name: 'title'},
                    {data: "lesson_start_date", name: 'lesson_start_date'},
                    {data: "duration", name: 'duration'},
                    {data: "attendance", name: 'attendance'},
                    {data: "qr_code" , name: "qr_code"},
                    {data: "published", name: "published"},
                    {data: "actions", name: "actions"}
                ],
                @if(request('show_deleted') != 1)
                columnDefs: [
                    {"width": "5%", "targets": 0},
                    {"className": "text-center", "targets": [0]}
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
              
                createdRow: function (row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
                language:{
                    url : "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{$locale_full_name}}.json",
                    buttons :{
                        colvis : '{{trans("datatable.colvis")}}',
                        pdf : '{{trans("datatable.pdf")}}',
                        csv : '{{trans("datatable.csv")}}',
                    }, 
                    search:"",
           }
            });

            @can('lesson_delete')
            @if(request('show_deleted') != 1)
            $('.actions').html('<a href="' + '{{ route('admin.lessons.mass_destroy') }}' + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>');
            @endif
            @endcan


            $(".js-example-placeholder-single").select2({
                placeholder: "{{trans('labels.backend.lessons.select_course')}}",
            });
            $(document).on('change', '#course_id', function (e) {
                var course_id = $(this).val();
                if (course_id) {
                    window.location.href = "{{ route('admin.lessons.index') }}" + "?course_id=" + course_id;
                } else {
                    window.location.href = "{{ route('admin.lessons.index') }}";
                }
            });
        });

    </script>
@endpush