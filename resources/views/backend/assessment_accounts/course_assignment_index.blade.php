@extends('backend.layouts.app')
@section('title', __('Course Assignments') . ' | ' . app_name())
@push('after-styles')
    <link rel="stylesheet" href="{{ asset('assets/css/colors/switch.css') }}">
      <style>

    </style>
@endpush
@section('content')

<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4 class="page-title d-inline">@lang('Course Assignment')</h4>
    @can('course_create')
        <div class="">
            <a href="{{ route('admin.asmnt_0_withcourse') }}" class="btn add-btn">+ @lang('Make New Assignment')</a>
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
                                Select Employee By Email 
                                <div class="custom-select-wrapper mt-2">
                                <select class="form-control custom-select-box select2 js-example-placeholder-single" name="user" id="user" >
                                    <option value="">Select</option>
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

                                Select Course
                                <div class="custom-select-wrapper mt-2">
                                    <select name="course_id" id="course_id" class="select2 form-control custom-select-box">
                                        <option value="">Select</option>
                                        @if($published_courses)
                                        @foreach($published_courses as $row)
 <option value="{{ $row->id }}"
            @if(isset($selectedCourse) && $row->id == $selectedCourse) selected @endif>
            {{ $row->title }}
        </option>                                        @endforeach
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
                                    <button class="btn btn-primary" id="advance-search-btn" type="submit">Advance Search</button>
                                </div>
                                <div>
                                    <button class="btn btn-danger ml-3" id="reset" type="button">Reset</button>

                                </div>
                                
                            </div>
                            </div>
                        </div>
                    </form> 
                    <div class="table-responsive">
                        <table id="myTable"
                            class="table dt-select custom-teacher-table table-striped @if (auth()->user()->isAdmin()) @if (request('show_deleted') != 1) dt-select @endif @endcan" style="width: 1300px;">
                            <thead>
                                <tr>
                                    <th>@lang('Assign title')</th>
                                    <th>@lang('Course Code')</th>
                                    <th>@lang('Course Name')</th>
                                    <th>@lang('Course Category')</th>
                                    <th>@lang('Assign. By')</th>
                                    <th>@lang('Assign. Date')</th>
                                    <th>@lang('Assign. to Department')</th>
                                    <th>@lang('Assign. to Specific User')</th>
                                    <th>@lang('Due Date')</th>
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
    <script>

        $('#reset').click(function (){
                //initializeDates();
            $('#user').val(null).trigger('change');
           
            $('#course_id').val(null).trigger('change');
            
            $('#advace_filter').submit();
           
        })

        $('#advace_filter').submit(function (e) {
            e.preventDefault();
            //$('#advance-search-btn').prop('disabled', true);
            loadDataTable(); // 👉 filter submission
        });

        $(document).ready(function () {
            loadDataTable(); 
        });

        function loadDataTable() {

            let user_id = $('#user').val();
            let course_id = $('#course_id').val() || null;

            if ($.fn.DataTable.isDataTable('#myTable')) {
                dataTableInstance.clear().destroy();
                $('#myTable tbody').empty();
            }

            dataTableInstance = $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                //ajax: "/user/course-assign-list", 
                ajax: {
                    url: "{{ route('admin.assessment_accounts.course-assign-list') }}",
                    type: "GET",
                    data: function (d) {
                        d.user_id = user_id;
                        d.course_id = course_id;
                        // d.dept_id = dept_id;
                        // d.from = $('#assign_from_date').val();
                        // d.to = $('#assign_to_date').val();
                        // d.due_date = $('#due_date').val();
                    }
                },
                columns: [
                    {
                        data: 'title',
                        name: 'title',
                        orderable: false,
                    },
                    {
                        data: 'course_code',
                        name: 'course_code',
                        orderable: false,
                    },
                    {
                        data: 'course_title',
                        name: 'course_title',
                        orderable: false,
                    },
                    {
                        data: 'course_cat',
                        name: 'course_cat',
                        orderable: false,
                    },
                    {
                        data: 'assign_by',
                        name: 'assign_by',
                        orderable: false,
                    },
                    {
                        data: 'assign_date',
                        name: 'assign_date',
                        orderable: false,

                    },
                    {
                        data: 'deprt_title',
                        name: 'deprt_title',
                        orderable: false,
                    },
                    {
                        data: 'assigned_user_names',
                        name: 'assigned_user_names',
                        orderable: false,
                    },
                    {
                        data: 'due_date',
                        name: 'due_date',
                        orderable: false,
                    },

                    // {
                    //     data: "actions",
                    //     render: function (data, type, row, meta) {
                    //         return `<div class="actions d-flex">
                    //                     <a class="btn btn-xs btn-info mb-1" href="/user/course_assign_edit/${row.id}"><i class="icon-pencil"></i></a>
                    //                     <a onclick="return confirm('Are you sure you want to delete?')" class="btn btn-xs btn-danger mb-1" href="/user/course_assign_delete/${row.id}"><i class="fa fa-trash"></i></a>
                    //                 </div>`;
                    //     },
                    // },
                ],
                "paginate": true,
                "sort": true,
                "language": {
                    "emptyTable": "No Data Is Available.",
                    search:"",
    
                },
                "order": [
                    
                ],
                dom:  "<'table-controls'lfB>" +
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
                        action: function(e, dt, button, config) {

                            $.ajax({
                                url: `/user/course-assignment-report-as-csv`,
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
                                    link.download = "course-assignment-report.csv";
                                    document.body.appendChild(link);
                                    link.click();
                                    document.body.removeChild(link);
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error downloading file:", error);
                                },
                            });
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
                //         action: function(e, dt, button, config) {

                //             $.ajax({
                //                 url: `/user/course-assignment-report-as-csv`,
                //                 method: "GET",
                //                 xhrFields: {
                //                     responseType: "blob",
                //                 },
                //                 beforeSend: function() {
                //                     $("#loader").removeClass("d-none");
                //                 },
                //                 complete: function() {
                //                     $("#loader").addClass("d-none");
                //                 },
                //                 success: function(data, status, xhr) {
                //                     var blob = new Blob([data], {
                //                         type: xhr.getResponseHeader(
                //                             "Content-Type"),
                //                     });
                //                     var link = document.createElement("a");
                //                     link.href = window.URL.createObjectURL(blob);
                //                     link.download = "course-assignment-report.csv";
                //                     document.body.appendChild(link);
                //                     link.click();
                //                     document.body.removeChild(link);
                //                 },
                //                 error: function(xhr, status, error) {
                //                     console.error("Error downloading file:", error);
                //                 },
                //             });
                //         }
                //     },
                //     {
                //         extend: 'pdf',
                //         exportOptions: {
                //             columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                //             modifier: {
                //                 page: 'all'
                //             }
                //         }
                //     },
                //     'colvis'
                // ],
                 initComplete: function () {
                    let $searchInput = $('#myTable_filter input[type="search"]');
                $searchInput
                    .addClass('custom-search')
                    .wrap('<div class="search-wrapper position-relative d-inline-block"></div>')
                    .after('<i class="fa fa-search search-icon"></i>');

                $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
                            },
                            
                        });

            dataTableInstance.on('draw', function () {
                $('#advance-search-btn').prop('disabled', false);
            });
        }

        

        $(document).on('click', '.switch-input', function(e) {
            var id = $(this).data('id');
            $.ajax({
                type: "POST",
                url: "{{ route('admin.assessment_accounts.status') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                },
            }).done(function() {
                var table = $('#myTable').DataTable();
            });
        })
    </script>
@endpush
