@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')
@section('title', __('labels.backend.courses.title') . ' | ' . app_name())
@push('after-styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet" />
     <style>
    .dropdown-item{
        border-bottom: none;
    }
    .dropdown-menu{
        padding: 5px;
    }
    </style>

@endpush

@section('content')
   
<div class="pb-3 d-flex justify-content-between align-items-center">
   <h4>
      @lang('labels.backend.courses.title')
   </h4>
   <div class="d-flex">
   <div class="">
         <a href="{{ route('admin.courses.cms-course') }}" class="btn btn-outline-success create_done mr-3">@lang('View CME Course')</a>

     </div>
       @can('course_create')
          <div class="">
              <a href="{{ route('admin.courses.create') }}" class="btn add-btn">@lang('strings.backend.general.app_add_new')</a>

          </div>
      @endcan
     
   </div>
   
</div>
    <div class="card">
        <!-- <div class="card-header">
            
            <h3 class="page-title float-left mb-0">@lang('labels.backend.courses.title')</h3>
            @can('course_create')
                <div class="float-right">
                    <a href="{{ route('admin.courses.create') }}" class="btn btn-success">@lang('strings.backend.general.app_add_new')</a>

                </div>
            @endcan
            <div class="float-right">
                <a href="{{ route('admin.courses.cms-course') }}" class="btn create_done">@lang('View CME Course')</a>

            </div>
        </div> -->
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
                    class="table custom-teacher-table table-striped  @can('course_delete') @if (request('show_deleted') != 1) dt-select @endif @endcan" style="width: 2100px;">
                    <thead>
                        <tr>
                            @can('course_delete')
                                @if (request('show_deleted') != 1)
                                    <th style="text-align:center;"><input type="checkbox" class="mass" id="select-all" /></th>
                                @endif
                            @endcan




                            <th>@lang('CC')</th>
                            <th>@lang('CL')</th>
                            <th>@lang('labels.backend.courses.fields.title')</th>
                            <!-- <th>@lang('Arabic Title')</th> -->
                            <th>@lang('labels.backend.courses.fields.category')</th>

                            
                            @if (Auth::user()->isAdmin())
                               
                                <th>@lang('labels.backend.courses.fields.teachers')</th>
                            @else
                              
                            @endif
                            {{-- <th>@lang('Assignment')</th> --}}
                            <th>@lang('TSE')</th>
                            <th>@lang('TD')</th>
                            <th>@lang('labels.backend.courses.fields.status')</th>
                            <th>@lang('labels.backend.courses.fields.qr_code')</th>
                            <th>@lang('labels.backend.lessons.title')</th>
                            <th >@lang('Test')</th>
                            <th>@lang('Assesment')</th>
                            <th>@lang('FD')</th>
                            @if (request('show_deleted') == 1)
                                <th >@lang('strings.backend.general.actions')</th>
                            @else
                                <th style="text-align:center;width:150px">@lang('strings.backend.general.actions')</th>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            var route = '{{ route('admin.courses.get_data') }}';

            @if (request('show_deleted') == 1)
                route = '{{ route('admin.courses.get_data', ['show_deleted' => 1]) }}';
            @endif

            @if (request('teacher_id') != '')
                route = '{{ route('admin.courses.get_data', ['teacher_id' => request('teacher_id')]) }}';
            @endif

            @if (request('cat_id') != '')
                route = '{{ route('admin.courses.get_data', ['cat_id' => request('cat_id')]) }}';
            @endif

            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                //retrieve: true,
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
                    @if (request('show_deleted') != 1)
                        {
                            "data": function(data) {
                                return '<input type="checkbox" class="single" name="id[]" value="' +
                                    data.id + '" />';
                            },
                            "orderable": false,
                            "searchable": false,
                            "name": "id"
                        },
                    @endif

                    {
                        data: "course_code",
                        name: 'course_code'
                    },
                    {
                        data: "course_lang",
                        name: 'course_lang'
                    },
                    {
                        data: "title",
                        name: 'title'
                    },
                    // {
                    //     data: "arabic_title",
                    //     name: 'arabic_title'
                    // },
                    {
                        data: "category",
                        name: 'category'
                    },
                    // {data: "department", name: 'department'},
                    @if (Auth::user()->isAdmin())
                        // {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable:false},
                        // {data: "id", name: 'id'},
                        {
                            data: "teachers",
                            name: 'teachers'
                        },
                    @else
                        // {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false},
                        // {data: "id", name: 'id'},
                    @endif
                    {
                        data: "total_students_enrolled",
                        name: "total_students_enrolled"
                    },
                    {   
                        data:"duration",
                        name:"duration"

                    },
                    {
                        data: "status",
                        name: "status"
                    },
                    {
                        data: "qr_code",
                        name: "qr_code"
                    },
                   
                    {
                        data: "lessons",
                        name: "lessons"
                    },
                    {
                        data: "tests",
                        name: "tests"
                    },
                    {   data: "assignment", 
                        name: "assignment"
                    },
                    {   data: "feedback", 
                        name: "feedback"
                    },
                    {
                        data: "actions",
                        name: "actions"
                    }
                ],
                @if (request('show_deleted') != 1)
                    columnDefs: [{
                            "width": "5%",
                            "targets": 0
                        },
                        {
                            "className": "text-center",
                            "targets": [0]
                        }
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
  
                createdRow: function(row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
                language: {
                    url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{ $locale_full_name }}.json",
                    buttons: {
                        colvis: '{{ trans('datatable.colvis') }}',
                        pdf: '{{ trans('datatable.pdf') }}',
                        csv: '{{ trans('datatable.csv') }}',
                    },
                    search:"",
    
                }
            }); 
           
        });

        $(document).on('click', '.copy-offline-link', function (e) {
            e.preventDefault();
            //alert("hi")
            const textToCopy = $(this).attr('data-url');
            const tempInput = document.createElement('textarea');
            tempInput.value = textToCopy;
            document.body.appendChild(tempInput);
            tempInput.select();
            try {
                document.execCommand('copy');
                toastr.success('Link copied successfully');
            } catch (err) {
                toastr.error('Failed to copy link. Please try later.');
            }
            document.body.removeChild(tempInput);
        });
        
    </script>
@endpush
