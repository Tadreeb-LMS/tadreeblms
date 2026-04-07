@extends('backend.layouts.app')
@section('title', __('admin_pages.internal_attendance.title') . ' | ' . app_name())

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

    .dropdown-item {
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

    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ccc !important;
    }

    .select2-container--default .select2-selection--single {
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
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

    .buttons-colvis {
        top: 7px !important;
    }

    .dt-buttons a:hover svg {
        color: #007bff !important;
    }
</style>
@endpush

@section('content')
<div class="pb-3 align-items-center d-flex justify-content-between">
    <h5>{{ __('admin_pages.internal_attendance.title') }}</h5>

    <div id="download_link" style="display: none;">
        <span id="msg"></span>
    </div>

    <div>
        <button class="add-btn" id="sync-reports" type="button">
            {{ __('admin_pages.internal_attendance.sync_report') }}
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body pt-0">
        <form id="advace_filter">
            <div class="row">
                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">
                    <div>
                        {{ __('admin_pages.internal_attendance.select_employee_by') }}
                    </div>
                    <div class="custom-select-wrapper mt-2">
                        <select class="form-control custom-select-box select2 js-example-placeholder-single" name="user_by" id="user_by">
                            <option value="">{{ __('admin_pages.internal_attendance.select') }}</option>
                            <option @if('email' == request()->user_by) selected @endif value="email">
                                {{ __('admin_pages.internal_attendance.email') }}
                            </option>
                            <option @if('code' == request()->user_by) selected @endif value="code">
                                {{ __('admin_pages.internal_attendance.code') }}
                            </option>
                            <option @if('name' == request()->user_by) selected @endif value="name">
                                {{ __('admin_pages.internal_attendance.name') }}
                            </option>
                        </select>
                        <span class="custom-select-icon" style="right: 10px;">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3" id="email-block">
                    {{ __('admin_pages.internal_attendance.select_employee_by_email') }}
                    <div class="custom-select-wrapper mt-2">
                        <select class="form-control custom-select-box select2 js-example-placeholder-single" name="user" id="user">
                            <option value="">{{ __('admin_pages.internal_attendance.select') }}</option>
                            @if($internal_users)
                                @foreach($internal_users as $user)
                                    <option @if($user->id == request()->user) selected @endif value="{{ $user->id }}">
                                        {{ $user->email }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <span class="custom-select-icon" style="right: 10px;">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3" style="display:none;" id="name-block">
                    {{ __('admin_pages.internal_attendance.select_employee_by_name') }}
                    <div class="custom-select-wrapper mt-2">
                        <select class="select2" name="emp_name" id="emp_name">
                            <option value="">{{ __('admin_pages.internal_attendance.select') }}</option>
                            @if($internal_users)
                                @foreach($internal_users as $user)
                                    <option @if($user->id == request()->user) selected @endif value="{{ $user->id }}">
                                        {{ $user->first_name . ' ' . $user->last_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3" style="display:none;" id="code-block">
                    <div class="custom-select-wrapper mt-2">
                        {{ __('admin_pages.internal_attendance.select_employee_by_code') }}
                        <select class="select2" name="emp_code" id="emp_code">
                            <option value="">{{ __('admin_pages.internal_attendance.select') }}</option>
                            @if($internal_users)
                                @foreach($internal_users as $user)
                                    <option @if($user->id == request()->user) selected @endif value="{{ $user->id }}">
                                        {{ $user->emp_id }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">
                    {{ __('admin_pages.internal_attendance.select_course') }}
                    <div class="custom-select-wrapper mt-2">
                        <select name="course_id" id="course_id" class="select2 form-control custom-select-box">
                            <option value="">{{ __('admin_pages.internal_attendance.select') }}</option>
                            @if($published_courses)
                                @foreach($published_courses as $row)
                                    <option @if($row->id == request()->course_id) selected @endif value="{{ $row->id }}">
                                        {{ $row->title }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <span class="custom-select-icon">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">
                    {{ __('admin_pages.internal_attendance.select_department') }}
                    <div class="custom-select-wrapper mt-2">
                        <select name="dept_id" id="dept_id" class="select2 form-control custom-select-box">
                            <option value="">{{ __('admin_pages.internal_attendance.select') }}</option>
                            @if($published_department)
                                @foreach($published_department as $row)
                                    <option @if($row->id == request()->dept_id) selected @endif value="{{ $row->id }}">
                                        {{ $row->title }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <span class="custom-select-icon">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">
                    Progress Status
                    <div class="custom-select-wrapper mt-2">
                        <select name="progress_status" id="progress_status" class="form-control custom-select-box select2">
                            <option value="">All</option>
                            <option value="not_started" {{ request()->progress_status == 'not_started' ? 'selected' : '' }}>
                                Not Started
                            </option>
                            <option value="in_progress" {{ request()->progress_status == 'in_progress' ? 'selected' : '' }}>
                                In Progress
                            </option>
                            <option value="completed" {{ request()->progress_status == 'completed' ? 'selected' : '' }}>
                                Completed
                            </option>
                        </select>
                        <span class="custom-select-icon">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">
                    <div>
                        <div class="mb-2">
                            {{ __('admin_pages.internal_attendance.assign_from_date') }}
                        </div>
                        <input type="date" name="from" value="{{ request()->from }}" id="assign_from_date" class="w-100" style="border: 1px solid #c8ced3;border-radius:4px;padding-left:8px;padding-right:8px;padding-top:4px;padding-bottom:5px">
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">
                    <div class="mb-2">
                        {{ __('admin_pages.internal_attendance.assign_to_date') }}
                    </div>
                    <div>
                        <input type="date" name="to" value="{{ request()->to }}" id="assign_to_date" class="w-100" style="border: 1px solid #c8ced3;border-radius:4px;padding-left:8px;padding-right:8px;padding-top:4px;padding-bottom:5px">
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">
                    <div class="mb-2">
                        {{ __('admin_pages.internal_attendance.due_date') }}
                    </div>
                    <div>
                        <input type="date" name="due_date" value="{{ request()->due_date }}" id="due_date" class="w-100" style="border: 1px solid #c8ced3;border-radius:4px;padding-left:8px;padding-right:8px;padding-top:4px;padding-bottom:5px">
                    </div>
                </div>

                <div class="col-lg-4 col-md-12 col-sm-6 col-xs-12 d-flex align-items-center mt-4">
                    <div class="d-flex justify-content-between mt-3">
                        <div>
                            <button class="btn btn-primary" id="advance-search-btn" type="submit">
                                {{ __('admin_pages.internal_attendance.advance_search') }}
                            </button>
                        </div>
                        <div>
                            <button class="btn btn-danger ml-3" id="reset" type="button">
                                {{ __('admin_pages.internal_attendance.reset') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-12 mt-3">
                <div class="table-responsive">
                    <table id="myTable" class="table dt-select custom-teacher-table table-striped @can('category_delete') @if (request('show_deleted') != 1) dt-select @endif @endcan" style="width:2500px">
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
                                <th style="width:130px">@lang('Lesson Quiz')</th>
                                <th style="width:140px">@lang('Lesson Quiz Status')</th>
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

    $('#user_by').change(function () {
        emp_by = $(this).val();
        setSelectUser(emp_by);
    });

    function setSelectUser(emp_by)
    {
        if (emp_by == 'email') {
            $('#code-block').hide();
            $('#name-block').hide();
            $('#email-block').show();
        }

        if (emp_by == 'code') {
            $('#code-block').show();
            $('#name-block').hide();
            $('#email-block').hide();
        }

        if (emp_by == 'name') {
            $('#code-block').hide();
            $('#name-block').show();
            $('#email-block').hide();
        }
    }

    const fromDateInput = document.getElementById('assign_from_date');
    const toDateInput = document.getElementById('assign_to_date');
    const MS_PER_DAY = 24 * 60 * 60 * 1000;
    const MAX_RANGE_DAYS = 90;
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
        fromDateInput.addEventListener('change', () => {
            enforceMaxRangeOnFromChange();
        });

        toDateInput.addEventListener('change', () => {
            enforceMaxRangeOnToChange();
        });
    });
</script>

<script>
    let dataTableInstance;

    $(document).ready(function () {
        loadDataTable();
    });

    $('#reset').click(function () {
        $('#user').val(null).trigger('change');
        $('#emp_name').val(null).trigger('change');
        $('#emp_code').val(null).trigger('change');
        $('#course_id').val(null).trigger('change');
        $('#dept_id').val(null).trigger('change');
        $('#assign_from_date').val(null);
        $('#assign_to_date').val(null);
        $('#progress_status').val(null).trigger('change');
        $('#due_date').val(null);

        $('#advace_filter').submit();
    });

    $('#advace_filter').submit(function (e) {
        e.preventDefault();
        $('#advance-search-btn').prop('disabled', true);
        loadDataTable();
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
                    d.progress_status = $('#progress_status').val();
                }
            },

            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="fa fa-download" style="color:#ccc;font-size:19px"></i>',
                    className: 'btn btn-sm btn-outline-primary',
                    action: function () {
                        $.ajax({
                            url: `{{ route('admin.employee.internal-progress-report') }}`,
                            method: "GET",
                            data: {
                                course_id: course_id,
                                user_id: user_id,
                                dept_id: dept_id,
                                from: $('#assign_from_date').val(),
                                to: $('#assign_to_date').val(),
                                due_date: $('#due_date').val()
                            },
                            xhrFields: { responseType: "blob" },
                            beforeSend: function () {
                                $("#loader").removeClass("d-none");
                            },
                            complete: function () {
                                alert(@json(__('admin_pages.internal_attendance.report_ready_email_notice')));
                                $("#loader").addClass("d-none");
                            },
                            success: function () {
                                return;
                            },
                            error: function () {
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
                { data: 'lesson_quiz', orderable: false },
                { data: 'lesson_quiz_status', orderable: false },
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
                lengthMenu: '{{ trans('datatable.length_menu') }}',
                buttons: {
                    colvis: '{{ trans("datatable.colvis") }}',
                    csv: '{{ trans("datatable.csv") }}'
                },
                emptyTable: '{{ __('admin_pages.internal_attendance.no_records_found') }}'
            }
        });

        dataTableInstance.on('draw', function () {
            $('#advance-search-btn').prop('disabled', false);
        });
    }
</script>
@endpush