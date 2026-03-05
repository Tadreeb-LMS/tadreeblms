@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')
@section('title', __('labels.backend.courses.title').' | '.app_name())

@section('content')
 <style>
        .create_done {
            padding: 7px 40px;
            background: #4dbd74;
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


<div class="pb-3 d-flex justify-content-between align-items-center">
 <h4 >
     @lang('labels.backend.courses.title')
 </h4>
 @can('course_create')
     <div class="">
         <a href="{{ route('admin.courses.create') }}"
            class="btn btn-primary">@lang('strings.backend.general.app_add_new')</a>

     </div>
 @endcan
</div>
    <div class="card">
        <!-- <div class="card-header">
            <h3 class="page-title">@lang('labels.backend.courses.title')</h3>
            @can('course_create')
                <div class="float-right">
                    <a href="{{ route('admin.courses.create') }}"
                       class="btn btn-success">@lang('strings.backend.general.app_add_new')</a>

                </div>
            @endcan
        </div> -->
        <div class="card-body">
            <div class="table-responsive">
                <div class="d-block">
                    <ul class="list-inline">
                        <li class="list-inline-item">
                            <a href="{{ route('admin.courses.index') }}"
                               style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">{{trans('labels.general.all')}}</a>
                        </li>
                        |
                        <li class="list-inline-item">
                            <a href="{{ route('admin.courses.index') }}?show_deleted=1"
                               style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">{{trans('labels.general.trash')}}</a>
                        </li>
                    </ul>
                </div>


                <table id="myTable" class="table dt-select custom-teacher-table table-striped @can('course_delete') @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                    <thead>
                    <tr>
                        @can('course_delete')
                            @if ( request('show_deleted') != 1 )
                                <th style="text-align:center;"><input type="checkbox" class="mass" id="select-all"/></th>@endif
                        @endcan




                        <th>@lang('labels.backend.courses.fields.title')</th>

                        <th>@lang('labels.backend.courses.fields.category')</th>

                        <th>@lang('Department')</th>
                        @if (Auth::user()->isAdmin())
                                {{-- <th>@lang('labels.general.sr_no')</th>
                                <th>@lang('labels.general.id') --}}
                                <th>@lang('labels.backend.courses.fields.teachers')</th>
                        @else
                                {{-- <th>@lang('labels.general.sr_no')</th>
                                <th>@lang('labels.general.id') --}}

                        @endif
                        <!--th>@lang('labels.backend.courses.fields.price') <br><small>(in {{$appCurrency['symbol']}})</small></th-->
                        <th>Total Students Enrolled</th>
                        <th>@lang('labels.backend.courses.fields.status')</th>
                        <th>@lang('labels.backend.courses.fields.qr_code')</th>
                        <th>@lang('labels.backend.lessons.title')</th>
                        <th>Tests</th>
                        @if( request('show_deleted') == 1 )
                            <th>&nbsp; @lang('strings.backend.general.actions')</th>
                        @else
                            <th>&nbsp; @lang('strings.backend.general.actions')</th>
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
            var route = '{{route('admin.courses.get-cms-data')}}';



            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: "<'table-controls'lfB>" +
                     "<'table-responsive't>" +
                     "<'d-flex justify-content-between align-items-center mt-3'ip><'actions'>",
                  buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download icon-styles"></i>',
                    className: '',
                    buttons: [{
                            extend: 'csv',
                            action: function(e, dt, button, config) {

                            $.ajax({
                                url: `/user/exportCourseAsCsv`,
                                method: "GET",
                                xhrFields: {
                                    responseType: "blob",
                                },
                                beforeSend: function() {
                                    $("#loader").removeClass("d-none");
                                },
                                complete: function() {
                                    $("#loader").addClass("d-none");
                                },
                                success: function(data, status, xhr) {
                                    var blob = new Blob([data], {
                                        type: xhr.getResponseHeader(
                                            "Content-Type"),
                                    });
                                    var link = document.createElement("a");
                                    link.href = window.URL.createObjectURL(blob);
                                    link.download = "courses.csv";
                                    document.body.appendChild(link);
                                    link.click();
                                    document.body.removeChild(link);
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error downloading file:", error);
                                },
                            });
                        },
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
                {
                    extend: 'colvis',
                    text: '<i class="fa fa-eye icon-styles" aria-hidden="" ></i>',
                },
            ],
                ajax: route,
                columns: [
                        @if(request('show_deleted') != 1)
                    { "data": function(data){
                        return '<input type="checkbox" class="single" name="id[]" value="'+ data.id +'" />';
                    }, "orderable": false, "searchable":false, "name":"id" },
                        @endif

                    {data: "title", name: 'title'},
                    {data: "category", name: 'category'},
                    {data: "department", name: 'department'},
                    @if (Auth::user()->isAdmin())
                    // {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable:false},
                    // {data: "id", name: 'id'},
                    {data: "teachers", name: 'teachers'},

                    @else
                    // {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false},
                    // {data: "id", name: 'id'},

                    @endif
                    //{data: "price", name: "price"},
                    {data: "total_students_enrolled", name: "total_students_enrolled"},
                    {data: "status", name: "status"},
                    {data: "qr_code" , name: "qr_code"},
                    {data: "lessons", name: "lessons"},
                    {data: "tests", name: "tests"},
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
                    },search:""
                }
            });
            {{--@can('course_delete')--}}
            {{--@if(request('show_deleted') != 1)--}}
            {{--$('.actions').html('<a href="' + '{{ route('admin.courses.mass_destroy') }}' + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>');--}}
            {{--@endif--}}
            {{--@endcan--}}
        });

    </script>

@endpush