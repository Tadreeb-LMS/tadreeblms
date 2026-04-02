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
    </style>
@endpush
@section('content')

    <div class="card">
        <div class="card-header">
                <h3 class="page-title d-inline">Internal Report</h3>
                <!-- @can('course_create')
                <div class="float-right">
                    <a href="{{ url('user/reports_create_internal/1') }}"
                       class="btn btn-success">@lang('strings.backend.general.app_add_new')</a>

                </div>
            @endcan -->
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="filter-form">
                        <form name="search-form" method="get">
                            <select id="trainee_type" name="trainee_type" class="sel form-control">
                                <option value="">Select</option>
                                <option value="internal">Internal Trainee</option>
                                <option value="external">External Trainee</option>
                            </select>
                            <input class="filter-search" type="button" name="filter" value="search"/>
                        </form>
                    </div>
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
                               class="table table-bordered table-striped @if(auth()->user()->isAdmin()) @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                            <thead>
                            <tr>

                                @can('category_delete')
                                    @if ( request('show_deleted') != 1 )
                                        <th style="text-align:center;"><input type="checkbox" class="mass"
                                                                              id="select-all"/>
                                        </th>@endif
                                @endcan
                                <th>Course Name</th>
                                <th>@lang('labels.backend.teachers.fields.email')</th>
                                <th>Enrolled Date</th>
                                <th>@lang('labels.backend.teachers.fields.status')</th>
                                <th>Completed Status</th>
                                <th>Lesson Quiz</th>
                                <th>Lesson Quiz Status</th>
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


@endsection

@push('after-scripts')
    <script>

        $(document).ready(function () {


            var route = "{{route('admin.employee.enrolled_get_data_internal',$course_id)}}";

            @if(request('show_deleted') == 1)
                route = '{{route('admin.employee.enrolled_get_data_internal',[ $course_id ,1])}}';
            @endif

           var table = $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: 'lfBrtip<"actions">',
                buttons: [
                    {
                        extend: 'csv',
                        exportOptions: {
                            columns: [ 1, 2, 3, 4,5, 6, 7, 8, 9]
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: [ 1, 2, 3, 4,5,6,7,8,9],
                        }
                    },
                    'colvis'
                ],
                ajax: {
                    url:route,
                    data: function (d) {
                            d.search_type = $('#trainee_type').val();
                    },
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
                    {data: "cousre_name", name: 'cousre_name'},
                    {data: "email", name: 'email'},
                    {data: "enrolled_date", name: 'enrolled_date'},
                    {data: "status", name: 'status'},
                    {data: "course_completed", name: 'course_completed'},
                    {data: "lesson_quiz", name: 'lesson_quiz'},
                    {data: "lesson_quiz_status", name: 'lesson_quiz_status'},
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
                language:{
                    url : "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{$locale_full_name}}.json",
                    buttons :{
                        colvis : '{{trans("datatable.colvis")}}',
                        pdf : '{{trans("datatable.pdf")}}',
                        csv : '{{trans("datatable.csv")}}',
                    }
                }

            });
            @if(auth()->user()->isAdmin())
            $('.actions').html('<a href="' + '{{ route('admin.teachers.mass_destroy') }}' + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>');
            @endif



        });
        $(document).on('click', '.filter-search', function (e) {

            var table = $('#myTable').DataTable();
            table.ajax.reload();

        })

    </script>

@endpush