@extends('backend.layouts.app')
@section('title', __('assessment_accounts_pages.course_invitation_list.title') . ' | ' . app_name())
@push('after-styles')
    <link rel="stylesheet" href="{{ asset('assets/css/colors/switch.css') }}">
      <style>
      
        a.btn.add-btn.rechedule {
            background: #d1107a;
        }

    </style>
@endpush
@section('content')

<div class="pb-3 d-flex justify-content-between alig-itens-center">
    <h4>{{ __('assessment_accounts_pages.course_invitation_list.title') }}</h4>
    @can('course_create')
        <div class="">
            <a href="{{ route('admin.add_asmnt_invitation') }}" class="btn add-btn">+ {{ __('assessment_accounts_pages.course_invitation_list.make_new_assignment') }}</a>
        
            <a href="{{ route('admin.add_asmnt_invitation',['reschudule'=>true]) }}" class="btn add-btn rechedule">+ {{ __('assessment_accounts_pages.course_invitation_list.reschedule_assignment') }}</a>
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
                                {{ __('assessment_accounts_pages.course_invitation_list.select_employee_by_email') }}
                                <div class="custom-select-wrapper mt-2">
                                <select class="form-control custom-select-box select2 js-example-placeholder-single" name="user" id="user" >
                                    <option value="">{{ __('assessment_accounts_pages.course_invitation_list.select') }}</option>
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

                                {{ __('assessment_accounts_pages.course_invitation_list.select_course') }}
                                <div class="custom-select-wrapper mt-2">
                                    <select name="course_id" id="course_id" class="select2 form-control custom-select-box">
                                        <option value="">{{ __('assessment_accounts_pages.course_invitation_list.select') }}</option>
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
                                    <button class="btn btn-primary" id="advance-search-btn" type="submit">{{ __('assessment_accounts_pages.course_invitation_list.advance_search') }}</button>
                                </div>
                                <div>
                                    <button class="btn btn-danger ml-3" id="reset" type="button">{{ __('assessment_accounts_pages.course_invitation_list.reset') }}</button>

                                </div>
                                
                            </div>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table id="myTable"
                            class="table dt-select custom-teacher-table table-striped @if (auth()->user()->isAdmin()) @if (request('show_deleted') != 1) dt-select @endif @endcan" style="width: 1350px;">
                            <thead>
                                <tr>
                                    {{-- <th style="width: 80px;">@lang('Assign title')</th> --}}
                                    <th style="width: 80px;">{{ __('assessment_accounts_pages.course_invitation_list.course_code') }}</th>
                                    <th style="width: 80px;">{{ __('assessment_accounts_pages.course_invitation_list.course_name') }}</th>
                                    <th style="width: 80px;">{{ __('assessment_accounts_pages.course_invitation_list.course_category') }}</th>
                                    <th style="width: 80px;">{{ __('assessment_accounts_pages.course_invitation_list.assign_by') }}</th>
                                    <th style="width: 80px;">{{ __('assessment_accounts_pages.course_invitation_list.assign_date') }}</th>
                                    <th style="width: 100px;">{{ __('assessment_accounts_pages.course_invitation_list.assign_to_department') }}</th>
                                    <th style="width: 110px;">{{ __('assessment_accounts_pages.course_invitation_list.assign_to_specific_user') }}</th>
                                    <th style="width: 80px;">{{ __('assessment_accounts_pages.course_invitation_list.due_date') }}</th>
                                    <th style="width: 80px;">{{ __('assessment_accounts_pages.course_invitation_list.meeting_end_datetime') }}</th>
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

        $(document).ready(function() {
            let user_id = $('#user').val();
            let course_id = $('#course_id').val() || null;

            if ($.fn.DataTable.isDataTable('#myTable')) {
                dataTableInstance.clear().destroy();
                $('#myTable tbody').empty();
            }
           dataTableInstance = $('#myTable').dataTable({
                processing: true,
                serverSide: true,
                searching: false,
                //ajax: "/user/course-invitation-list",
                ajax: {
                    //url: "/user/course-invitation-list",
                    url: "{{ route('admin.assessment_accounts.course-invitation-list') }}",
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
                    // {
                    //     data: 'assignment_title',
                    //     name: 'assignment_title',
                    //     orderable: false,
                    // },
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
{
                        data: 'meeting_end_datetime',
                        name: 'meeting_end_datetime',
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
                    "emptyTable": "{{ __('assessment_accounts_pages.course_invitation_list.no_data_available') }}",
                    lengthMenu: '{{ trans('datatable.length_menu') }}',
                    search:"",
             },
                "order": [
                    
                ],
                dom:  "<'table-controls'lfB>" +
                     "<'table-responsive't>" +
                     "<'d-flex justify-content-between align-items-center mt-3'ip><'actions'>",
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
                 buttons: [
    {
        extend: 'collection',
        text: '<i class="fa fa-download icon-styles"></i>',
        className: '',
        buttons: [
            {
                        extend: 'csv',
                        text: '{{ trans("datatable.csv") }}',
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
                text: '{{ trans("datatable.pdf") }}',
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
        });

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
