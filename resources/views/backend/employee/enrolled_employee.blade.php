@extends('backend.layouts.app')
@section('title', 'Trainee'.' | '.app_name())
@push('after-styles')
    <link rel="stylesheet" href="{{asset('assets/css/colors/switch.css')}}">
    <style>
        .filter-form {
            position: absolute;
            width: 260px;
            left: 157px;
            top: 32px;
            z-index: 9999;
        }
        select.sel.form-control {
            width: 65%;
            display: inline-block;
        }
        .dataTables_wrapper .dataTables_filter {
            float: left;
            text-align: left;
            margin-left: 40%;
        }
        .enroll-tab-btn {
            cursor: pointer;
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: #f8f9fa;
            border-radius: 4px 4px 0 0;
            margin-right: 4px;
        }
        .enroll-tab-btn.active {
            background: #fff;
            border-bottom-color: #fff;
            font-weight: 600;
        }
        .enroll-tab-content {
            display: none;
        }
        .enroll-tab-content.active {
            display: block;
        }
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da !important;
            border-radius: .25rem !important;
            min-height: 38px;
        }
    </style>
@endpush
@section('content')

<div class="pb-3 d-flex justify-content-between align-items-center">
        <h4 class="">Enrolled Trainee [{{ CustomHelper::getCourseName($course_id); }}]</h4>
    @can('course_create')
        <div class="">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#enrollUsersModal">
                + Enroll Users
            </button>
        </div>
    @endcan
</div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <div class="d-block">
                            <ul class="list-inline">
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.employee.index') }}"
                                       style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">{{trans('labels.general.all')}}</a>
                                </li>
                                |
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.employee.index') }}?show_deleted=1"
                                       style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">{{trans('labels.general.trash')}}</a>
                                </li>
                            </ul>
                        </div>


                        <table id="myTable"
                               class="table dt-select custom-teacher-table table-striped @if(auth()->user()->isAdmin()) @if ( request('show_deleted') != 1 ) dt-select @endif @endcan" style="width: 1300px;">
                            <thead>
                            <tr>

                                @can('category_delete')
                                    @if ( request('show_deleted') != 1 )
                                        <th style="text-align:center;"><input type="checkbox" class="mass"
                                                                              id="select-all"/>
                                        </th>@endif
                                @endcan
                                <th>Trainee Type</th>
                                <th>@lang('labels.backend.teachers.fields.email')</th>
                                <th>Enrolled Date</th>
                                <th>@lang('labels.backend.teachers.fields.status')</th>
                                <th>Completed Status</th>
                                <th>Feedback</th>
                                <th>Issue Certificate</th>
                                <th>Track Employee</th>
                                <th>Track Percentage</th>
                                <!--
                                @if( request('show_deleted') == 1 )
                                    <th>&nbsp; @lang('strings.backend.general.actions')</th>
                                @else
                                    <th>&nbsp; @lang('strings.backend.general.actions')</th>
                                @endif
                                -->
                            </tr>
                            </thead>

                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>
        </div>
    </div>

{{-- Enroll Users Modal --}}
<div class="modal fade" id="enrollUsersModal" tabindex="-1" role="dialog" aria-labelledby="enrollUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enrollUsersModalLabel">Enroll Users</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="enroll-alert" class="alert d-none"></div>

                {{-- Tabs --}}
                <div class="mb-3" style="border-bottom: 1px solid #ddd;">
                    <span class="enroll-tab-btn active" data-tab="single-user">Single User</span>
                    <span class="enroll-tab-btn" data-tab="department">Entire Department</span>
                </div>

                <form id="enrollUsersForm">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $course_id }}">

                    {{-- Single User Tab --}}
                    <div class="enroll-tab-content active" id="tab-single-user">
                        <div class="form-group">
                            <label for="enroll_teachers">Select Users <span class="text-danger">*</span></label>
                            <select name="teachers[]" id="enroll_teachers" class="form-control select2" multiple style="width: 100%;">
                                @foreach ($teachers as $key => $name)
                                    <option value="{{ $key }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Department Tab --}}
                    <div class="enroll-tab-content" id="tab-department">
                        <div class="form-group">
                            <label for="enroll_department">Select Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="enroll_department" class="form-control select2" style="width: 100%;">
                                <option value="">Select One</option>
                                @foreach ($departments as $row)
                                    <option value="{{ $row->id }}">{{ $row->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btnEnrollSubmit">
                    <span class="spinner-border spinner-border-sm d-none" id="enrollSpinner" role="status"></span>
                    Enroll
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('after-scripts')
    <script>

        $(document).ready(function () {

            // Initialize Select2 inside modal
            $('#enrollUsersModal').on('shown.bs.modal', function () {
                $('#enroll_teachers').select2({
                    dropdownParent: $('#enrollUsersModal'),
                    placeholder: 'Search and select users...',
                    allowClear: true
                });
                $('#enroll_department').select2({
                    dropdownParent: $('#enrollUsersModal'),
                    placeholder: 'Select a department',
                    allowClear: true
                });
            });

            // Tab switching
            $(document).on('click', '.enroll-tab-btn', function () {
                $('.enroll-tab-btn').removeClass('active');
                $(this).addClass('active');
                var tab = $(this).data('tab');
                $('.enroll-tab-content').removeClass('active');
                $('#tab-' + tab).addClass('active');

                // Clear the other tab's selection
                if (tab === 'single-user') {
                    $('#enroll_department').val('').trigger('change');
                } else {
                    $('#enroll_teachers').val([]).trigger('change');
                }
            });

            // Submit enrollment
            $(document).on('click', '#btnEnrollSubmit', function () {
                var $btn = $(this);
                var $spinner = $('#enrollSpinner');
                var $alert = $('#enroll-alert');
                $alert.addClass('d-none').removeClass('alert-success alert-warning alert-danger');

                // Validate
                var activeTab = $('.enroll-tab-btn.active').data('tab');
                var teachers = $('#enroll_teachers').val();
                var department = $('#enroll_department').val();

                if (activeTab === 'single-user' && (!teachers || teachers.length === 0)) {
                    $alert.removeClass('d-none').addClass('alert-danger').text('Please select at least one user.');
                    return;
                }

                if (activeTab === 'department' && !department) {
                    $alert.removeClass('d-none').addClass('alert-danger').text('Please select a department.');
                    return;
                }

                // Build form data
                var formData = new FormData($('#enrollUsersForm')[0]);

                // Remove irrelevant field based on active tab
                if (activeTab === 'single-user') {
                    formData.delete('department_id');
                } else {
                    formData.delete('teachers[]');
                }

                $btn.prop('disabled', true);
                $spinner.removeClass('d-none');

                $.ajax({
                    url: '{{ route("admin.enroll_users") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        var msg = response.enrolled + ' user(s) enrolled successfully.';
                        var alertClass = 'alert-success';

                        if (response.already_enrolled > 0) {
                            msg += '\n' + response.already_enrolled + ' user(s) already enrolled: ' + response.already_enrolled_names.join(', ');
                            alertClass = response.enrolled > 0 ? 'alert-warning' : 'alert-warning';
                        }

                        if (response.skipped_inactive > 0) {
                            msg += '\n' + response.skipped_inactive + ' inactive user(s) skipped.';
                        }

                        $alert.removeClass('d-none').addClass(alertClass).css('white-space', 'pre-line').text(msg);

                        // Reload DataTable
                        if (response.enrolled > 0) {
                            $('#myTable').DataTable().ajax.reload(null, false);
                        }

                        // Reset form
                        $('#enroll_teachers').val([]).trigger('change');
                        $('#enroll_department').val('').trigger('change');
                    },
                    error: function (xhr) {
                        var errMsg = 'An error occurred.';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.errors) {
                                var errors = xhr.responseJSON.errors;
                                errMsg = Object.values(errors).flat().join('\n');
                            } else if (xhr.responseJSON.error) {
                                errMsg = xhr.responseJSON.error;
                            }
                        }
                        $alert.removeClass('d-none').addClass('alert-danger').css('white-space', 'pre-line').text(errMsg);
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                        $spinner.addClass('d-none');
                    }
                });
            });

            // Reset modal on close
            $('#enrollUsersModal').on('hidden.bs.modal', function () {
                $('#enroll-alert').addClass('d-none');
                $('#enroll_teachers').val([]).trigger('change');
                $('#enroll_department').val('').trigger('change');
                $('.enroll-tab-btn').removeClass('active').first().addClass('active');
                $('.enroll-tab-content').removeClass('active').first().addClass('active');
            });

            var route = '{{route('admin.employee.enrolled_get_data',[ $course_id ,0])}}';

            @if(request('show_deleted') == 1)
                route = '{{route('admin.employee.enrolled_get_data',[ $course_id ,1])}}';
            @endif

           var table = $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: "<'table-controls'lfB>" +
                     "<'table-responsive't>" +
                     "<'d-flex justify-content-end align-items-center mt-3'p><'actions'>",
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
                ajax: {
                    url:route,
                },
                columns: [
                        @if(request('show_deleted') != 1)
                    {
                        "data": function (data) {
                            return '<input type="checkbox" class="single" name="id[]" value="' + data.id + '" />';
                        }, "orderable": false, "searchable": false, "name": "id"
                    },
                        @endif
                    //{data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable:false},
                    {data: "trainee_type", name: 'trainee_type'},
                    {data: "email", name: 'email'},
                    {data: "enrolled_date", name: 'enrolled_date'},
                    {data: "status", name: 'status'},
                    {data: "course_completed", name: 'course_completed'},
                    {data: "feedback", name: 'feedback'},
                    {data: "issue_certificate", name: 'issue_certificate'},
                    {data: "track_employee", name: "track_employee"},
                    {data: "percentage", name: "percentage"},
                    //{data: "actions", name: 'actions'}
                ],
                @if(request('show_deleted') != 1)
                columnDefs: [
                    {"width": "5%", "targets": 0},
                    {"className": "text-center", "targets": [0]}
                ],
                @endif

                createdRow: function (row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
                initComplete: function () {
                    let $searchInput = $('#myTable_filter input[type="search"]');
    $searchInput
        .addClass('custom-search')
        .wrap('<div class="search-wrapper position-relative d-inline-block"></div>')
        .after('<i class="fa fa-search search-icon"></i>');

    $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
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
            @if(auth()->user()->isAdmin())
            $('.actions').html('<a href="' + '{{ route('admin.teachers.mass_destroy') }}' + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>');
            @endif



        });

    </script>

@endpush
