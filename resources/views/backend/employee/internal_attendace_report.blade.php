@extends('backend.layouts.app')
@section('title', 'Attendance Report' . ' | ' . app_name())
@push('after-styles')


<style>
    #myTable {
        table-layout: fixed !important;
        width: 100% !important;
    }

    .control-btns {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }
    .dropdown-item{
        border-bottom: none;
   
    }

     .select2-container--default .select2-selection--single .select2-selection__arrow {
    display: none !important;
}
   .select2-container .select2-search--inline .select2-search__field {
    box-sizing: border-box;
    border: none;
    font-size: 100%;
    margin-top: 5px;
    padding-left: 8px;
}

.select2-container--default .select2-selection--multiple:focus {
    outline: none !important;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important;
    border-color: #007bff !important;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
     outline: none !important;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important;
    border-color: #007bff !important;
}
.select2-container--default .select2-selection--multiple{
    border: 1px solid #ccc !important;
}

.select2-container--default .select2-selection--single {
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow b{
    display: none;
}
.select2-container .select2-selection--single .select2-selection__rendered {
    display: block;
    padding-left: 10px;
    padding-right: 20px;
    padding-top: 3px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.select2-container .select2-selection--single {
    box-sizing: border-box;
    cursor: pointer;
    display: block;
    height: 34px;
    user-select: none;
    -webkit-user-select: none;
}
.buttons-colvis{
top: 7px !important;
}

.dt-buttons a:hover svg {
    color: #007bff !important;
}
</style>
@endpush
@section('content')
<div class="pb-3 align-items-center d-flex justify-content-between">
    <h5>@lang('Attendance Report')</h5>
    {{-- <a href="{{ asset('/storage/exports/internal_attendance_report.csv') }}">Download CSV Report</a> --}}
    <div id="download_link" style="display: none;">
        {{-- <a id="download_link_anchor" href="" download>Download CSV Report</a> --}}
        <span id="msg"></span>
    </div>
    <div>
                        <button class="add-btn" id="sync-reports" type="button">Sync Report</button> 
                    </div>
</div>
<div class="card">
    
    <div class="card-body pt-0">
        <form id="advace_filter">
            <div class="row">

                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">
                    <div for="">
                        Select Employee By 
                    </div>
                    <div class="custom-select-wrapper mt-2">
                    <select class="form-control custom-select-box select2 js-example-placeholder-single" name="user_by" id="user_by" >
                        <option value="">Select</option>
                        <option @if('email' == request()->user_by) selected @endif value="email">Email</option>
                        <option @if('code' == request()->user_by) selected @endif value="code">Code</option>
                        <option @if('name' == request()->user_by) selected @endif value="name">Name</option>
                    </select>
                    <span class="custom-select-icon" style="right: 10px;">
                                <i class="fa fa-chevron-down"></i>
                            </span>
                </div>
                </div>
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
                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3" style="display:none;" id="name-block">
                    Select Employee By Name 
                    <div class="custom-select-wrapper mt-2">
                    <select class="select2" name="emp_name" id="emp_name" >
                        <option value="">Select</option>
                        @if($internal_users)
                            @foreach($internal_users as $user)
                                <option @if($user->id == request()->user) selected @endif value="{{ $user->id }}">{{ $user->first_name . ' ' . $user->last_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                </div>
                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3" style="display:none;" id="code-block">
                    <div class="custom-select-wrapper mt-2">
                    Select Employee By Code 
                    <select class="select2" name="emp_code" id="emp_code" >
                        <option value="">Select</option>
                        @if($internal_users)
                            @foreach($internal_users as $user)
                                <option @if($user->id == request()->user) selected @endif value="{{ $user->id }}">{{ $user->emp_id }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                </div>
                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">

                    Select Course
                    <div class="custom-select-wrapper mt-2">
                        <select name="course_id" id="course_id" class="select2 form-control custom-select-box">
                            <option value="">Select</option>
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
                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">

                    Select Dept
                    <div class="custom-select-wrapper mt-2">
                        <select name="dept_id" id="dept_id" class="select2 form-control custom-select-box">
                            <option value="">Select</option>
                            @if($published_department)
                            @foreach($published_department as $row)
                            <option @if($row->id == request()->dept_id) selected @endif value="{{ $row->id }}">{{ $row->title }}</option>
                            @endforeach
                            @endif
                        </select>
                        <span class="custom-select-icon">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">

                    <div class="">
                        <div class="mb-2">
                            Assign From date
                        </div>
                        <input type="date" name="from" value="{{ request()->from }}" id="assign_from_date" class="w-100" style="border: 1px solid #c8ced3;border-radius:4px;padding-left:8px;padding-right:8px;padding-top:4px;padding-bottom:5px">
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">
                    <div class="mb-2">
                        Assign To date
                    </div>
                    <div class="">
                        <input type="date" name="to" value="{{ request()->to }}" id="assign_to_date" class="w-100" style="border: 1px solid #c8ced3;border-radius:4px;padding-left:8px;padding-right:8px;padding-top:4px;padding-bottom:5px">
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">
                    <div class="mb-2">
                       Due date
                    </div>
                    <div class="">
                        <input type="date" name="due_date" value="{{ request()->due_date }}" id="due_date" class="w-100" style="border: 1px solid #c8ced3;border-radius:4px;padding-left:8px;padding-right:8px;padding-top:4px;padding-bottom:5px">
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
        
        <div class="row">
            <div class="col-12 mt-3">


                <div class="table-responsive">
                    <table id="myTable"
                        class="table dt-select custom-teacher-table table-striped @can('category_delete') @if (request('show_deleted') != 1) dt-select @endif @endcan" style="width:2500px">
                        <thead>
                            <tr>
                                <th style="width:80px">@lang('EID')</th>
                                <th style="width:120px">@lang('User Status')</th>
                                <th style="width:80px">@lang('Name')</th>
                                <th style="width:80px">@lang('Email')</th>
                                <th style="width:120px">@lang('Department')</th>
                                <th style="width:120px">@lang('Position')</th>
                                <th style="width:130px">@lang('Enrollment Type')</th>
                                <th style="width:130px">@lang('Course Category')</th>
                                <th style="width:120px">@lang('Course Code')</th>
                                <th style="width:120px">@lang('Course Name')</th>
                                <th style="width:130px">@lang('User Progress %')</th>
                                <th style="width:120px">@lang('Progress Status')</th>
                                <th style="width:140px">@lang('Assessment Score')</th>
                                <th style="width:140px">@lang('Assessment status')</th>
                                <th style="width:120px">@lang('Trainer Name')</th>
                                <th style="display:none;width:140px">@lang('Assignment Date')</th>
                                <th style="width:120px;width:140px">@lang('Assignment Date')</th>
                                <th style="width:120px">@lang('Due Date')</th>
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


    var emp_by = @json(request()->user_by);
    setSelectUser(emp_by);

    
    $('#user_by').change(function (){
        
        emp_by = $(this).val();

        setSelectUser(emp_by);
        
    })


    //alert(emp_by)

    function setSelectUser(emp_by)
    {
        if(emp_by == 'email') {
            $('#code-block').hide();
            $('#name-block').hide();
            $('#email-block').show();
        }
        if(emp_by == 'code') {
            $('#code-block').show();
            $('#name-block').hide();
            $('#email-block').hide();
        }
        if(emp_by == 'name') {
            $('#code-block').hide();
            $('#name-block').show();
            $('#email-block').hide();
        }
    }
    
    
    

    const fromDateInput = document.getElementById('assign_from_date');
const toDateInput = document.getElementById('assign_to_date');
const MS_PER_DAY = 24 * 60 * 60 * 1000;
const MAX_RANGE_DAYS = 90; // ⬅️ updated range
const today = new Date();

function formatDate(date) {
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const dd = String(date.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}

function isValidDate(dateString) {
    return !isNaN(new Date(dateString).getTime());
}

function initializeDates() {
    if (!isValidDate(fromDateInput.value)) {
        const defaultFrom = new Date(today.getTime() - MAX_RANGE_DAYS * MS_PER_DAY);
        fromDateInput.value = formatDate(defaultFrom);
    }
    if (!isValidDate(toDateInput.value)) {
        toDateInput.value = formatDate(today);
    }

    const minDate = new Date(today.getTime() - 365 * MS_PER_DAY);
    fromDateInput.min = formatDate(minDate);
    fromDateInput.max = formatDate(today);
    toDateInput.min = formatDate(minDate);
    toDateInput.max = formatDate(today);
}

function enforceMaxRangeOnFromChange() {
    const fromDate = new Date(fromDateInput.value);
    if (!isValidDate(fromDateInput.value)) return;

    const toDate = new Date(toDateInput.value);
    if (!isValidDate(toDateInput.value) || (toDate - fromDate) > MAX_RANGE_DAYS * MS_PER_DAY) {
        let newToDate = new Date(fromDate.getTime() + MAX_RANGE_DAYS * MS_PER_DAY);
        if (newToDate > today) newToDate = today;
        toDateInput.value = formatDate(newToDate);
    }
}

function enforceMaxRangeOnToChange() {
    const toDate = new Date(toDateInput.value);
    if (!isValidDate(toDateInput.value)) return;

    const fromDate = new Date(fromDateInput.value);
    if (!isValidDate(fromDateInput.value) || (toDate - fromDate) > MAX_RANGE_DAYS * MS_PER_DAY) {
        let newFromDate = new Date(toDate.getTime() - MAX_RANGE_DAYS * MS_PER_DAY);
        if (newFromDate < new Date(fromDateInput.min)) {
            newFromDate = new Date(fromDateInput.min);
        }
        fromDateInput.value = formatDate(newFromDate);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    //initializeDates(); // Optional if you want default range

    fromDateInput.addEventListener('change', () => {
        enforceMaxRangeOnFromChange();
    });

    toDateInput.addEventListener('change', () => {
        enforceMaxRangeOnToChange();
    });
});


    //$('#assign_from_date').val("");
    //$('#assign_to_date').val("");
</script>


    <script>


        $(document).ready(function () {
            loadDataTable(); 
        });

        $('#reset').click(function (){
                //initializeDates();
            $('#user').val(null).trigger('change');
            $('#emp_name').val(null).trigger('change');
            $('#emp_code').val(null).trigger('change');
            $('#course_id').val(null).trigger('change');
            $('#dept_id').val(null).trigger('change');
            $('#assign_from_date').val(null);
            $('#assign_to_date').val(null);

            $('#due_date').val(null);

            $('#advace_filter').submit();
            //location.reload(`{{ route('admin.employee.internal-attendence-report') }}`) local
        })

        $('#advace_filter').submit(function (e) {
            e.preventDefault();
            $('#advance-search-btn').prop('disabled', true);
            loadDataTable(); // 👉 filter submission
        });

        function loadDataTable() {
            const user_by = $('#user_by').val();
            let user_id = null;

            if (user_by === 'email') {
                user_id = $('#user').val();
            } else if (user_by === 'name') {
                user_id = $('#emp_name').val();
            } else if (user_by === 'code') {
                user_id = $('#emp_code').val();
            }

            let course_id = $('#course_id').val() || null;
            let dept_id = $('#dept_id').val() || null;

            if ((user_id && user_id !== "") || (course_id && course_id !== "")) {
                //$('#assign_from_date').val("");
                //$('#assign_to_date').val("");
            }

            if ($.fn.DataTable.isDataTable('#myTable')) {
                dataTableInstance.clear().destroy();
                $('#myTable tbody').empty();
            }

            dataTableInstance = $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: "<'table-controls'lB>" +
                    "<'table-responsive't>" +
                    "<'d-flex justify-content-between align-items-center mt-3'ip><'actions'>",

                ajax: {
                    url: "{{ route('admin.employee.internal-attendence-report') }}",
                    type: "GET",
                    data: function (d) {
                        d.user_id = user_id;
                        d.course_id = course_id;
                        d.dept_id = dept_id;
                        d.from = $('#assign_from_date').val();
                        d.to = $('#assign_to_date').val();
                        d.due_date = $('#due_date').val();
                    }
                },

                buttons: [
                    {
                        extend: 'csv',
                        text: '<i class="fa fa-download" style="color:#ccc;font-size:19px"></i>',
                        className: 'btn btn-sm btn-outline-primary',
                        action: function (e, dt, button, config) {
                            $.ajax({
                                url: `{{ route('admin.employee.internal-progress-report') }}`,
                                method: "GET",
                                data: {
                                    'course_id': course_id,
                                    'user_id': user_id,
                                    'dept_id': dept_id,
                                    'from': $('#assign_from_date').val(),
                                    'to': $('#assign_to_date').val(),
                                    'due_date': $('#due_date').val()
                                },
                                xhrFields: { responseType: "blob" },
                                beforeSend: function () {
                                    $("#loader").removeClass("d-none");
                                },
                                complete: function () {
                                    alert("Once the report is ready, it will be emailed.");
                                    $("#loader").addClass("d-none");
                                },
                                success: function (data, status, xhr) {
                                    // const blob = new Blob([data], { type: xhr.getResponseHeader("Content-Type") });
                                    // const link = document.createElement("a");
                                    // link.href = window.URL.createObjectURL(blob);
                                    // link.download = "internal-attendance-report.csv";
                                    // document.body.appendChild(link);
                                    // link.click();
                                    // document.body.removeChild(link);
                                    return;
                                },
                                error: function (xhr, status, error) {
                                    //console.error("CSV Download Failed:", error);
                                }
                            });
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-eye" style="color:#ccc;font-size:19px"></i>'
                    }
                ],

                columns: [
                    { data: 'emp_id', orderable: false },
                    { data: 'user_status', orderable: false },
                    { data: 'emp_name', orderable: false },
                    { data: 'emp_email', orderable: false },
                    { data: 'department', orderable: false },
                    { data: 'emp_postition', orderable: false },
                    { data: 'enroll_type', orderable: false },
                    { data: 'course_category', orderable: false },
                    { data: 'course_code', orderable: false },
                    { data: 'course', orderable: false },
                    { data: 'progress_per', orderable: false },
                    { data: 'progress_status', orderable: false },
                    { data: 'assignment_score', orderable: false },
                    { data: 'assignment_status', orderable: false },
                    { data: 'trainer_name', orderable: false },
                    { data: 'assign_date', orderable: false },
                    { data: 'due_date', orderable: false }
                ],

                   initComplete: function () {
                     let $searchInput = $('#myTable_filter input[type="search"]');
            $searchInput
                .addClass('custom-search')
                .wrap('<div class="search-wrapper position-relative d-inline-block"></div>')
                .after('<i class="fa fa-search search-icon"></i>');

            $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
                        },

                language: {
                    url: `//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{ $locale_full_name }}.json`,
                    buttons: {
                        colvis: '{{ trans("datatable.colvis") }}',
                        csv: '{{ trans("datatable.csv") }}'
                    },
                    emptyTable: "No records found"
                }
            });

            dataTableInstance.on('draw', function () {
                $('#advance-search-btn').prop('disabled', false);
            });
        }


        // $('#sync-reports').click(function () {
        //     if (!confirm("Are you sure you want to sync the report?")) return;

        //     $.ajax({
        //         url: "{{ route('admin.sync.report') }}",
        //         method: "POST",
        //         data: {
        //             _token: "{{ csrf_token() }}"
        //         },
        //         beforeSend: function () {
        //             $('#sync-reports').prop('disabled', true).text('Syncing...');
        //         },
        //         success: function (response) {
        //             alert(response.message);
        //         },
        //         error: function (xhr) {
        //             alert("Failed to sync. Please try again.");
        //         },
        //         complete: function () {
        //             $('#sync-reports').prop('disabled', false).text('Sync Report');
        //         }
        //     });
        // });


         $(document).ready(function() {



            var route = '{{ route('admin.employee.get_data') }}';

            @if (request('show_deleted') == 1)
                route = '{{ route('admin.employee.get_data', ['show_deleted' => 1]) }}';
            @endif

            var table = $('#myTable').DataTable({
                // processing: true,
                // serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom:  "<'table-controls'lB>" +
                     "<'table-responsive't>" +
                     "<'d-flex justify-content-between align-items-center mt-3'ip><'actions'>",
                // buttons: [{
                //         extend: 'csv',
                //     },
                //     {
                //         extend: 'pdfHtml5',
                //         customize: function(doc) {
                //             doc.defaultStyle.fontSize = 5; // Adjust the font size as needed
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
                text: 'CSV',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
                }
            },
           {
                        extend: 'pdfHtml5',
                        text:"PDf",
                        // customize: function(doc) {
                        //     doc.defaultStyle.fontSize = 5;
                        // }
                    },
        ]
    },
      {extend: 'colvis',
    text: '<i class="fa fa-eye icon-styles" aria-hidden="true"></i>',
    className: ''},
],
                // buttons: [{
                //         extend: 'csv',
                //         exportOptions: {
                //             columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
                //         }
                //     },
                //     {
                //         extend: 'pdf',
                //         exportOptions: {
                //             columns: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                //         }
                //     },
                //     'colvis'
                // ],
                ajax: route,
                 columns: [
                      { data: 'emp_id', name: 'emp_id', orderable: false },
                         { data: 'user_status', name: 'user_status', orderable: false },
                         { data: 'emp_name', name: 'emp_name', orderable: false },
                         { data: 'emp_email', name: 'emp_email', orderable: false },
                         { data: 'department', name: 'department', orderable: false },
                         { data: 'emp_postition', name: 'emp_postition', orderable: false },
                         { data: 'enroll_type', name: 'enroll_type', orderable: false },
                         { data: 'course_category', name: 'course_category', orderable: false },
                         { data: 'course_code', name: 'course_code', orderable: false },
                         { data: 'course', name: 'course', orderable: false },
                         { data: 'progress_per', name: 'progress_per', orderable: false },
                         { data: 'progress_status', name: 'progress_status', orderable: false },
                         { data: 'assignment_score', name: 'assignment_score', orderable: false },
                         { data: 'assignment_status', name: 'assignment_status', orderable: false },
                         { data: 'trainer_name', name: 'trainer_name', orderable: false },
                         { data: 'assign_date', name: 'assign_date', orderable: false },
                         { data: 'due_date', name: 'due_date', orderable: false }
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
                    search:""
      
                }

            });
            @if (auth()->user()->isAdmin())
                $('.actions').html('<a href="' + '{{ route('admin.teachers.mass_destroy') }}' +
                    '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>'
                );
            @endif



        });
    </script>
@endpush
