@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')
@section('title', __('labels.backend.lessons.title').' | '.app_name())

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="page-title d-inline">Attendance List</h3>
        </div>
        <div class="card-body">
            @if($course_id && $lesson_id)
                <div class="table-responsive">

                    <table id="myTable"
                           class="table table-bordered table-striped @can('lesson_delete') @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                        <thead>
                        <tr>
                            
                            <th>@lang('labels.general.sr_no')</th>
                            <th>Username/Email</th>
                            <th>@lang('labels.backend.lessons.fields.title')</th>
                            <th>Course</th>
                            <th>Duration [minutes]</th>
                            
                            {{-- @if( request('show_deleted') == 1 )
                                <th>@lang('strings.backend.general.actions') &nbsp;</th>
                            @else
                                <th>@lang('strings.backend.general.actions') &nbsp;</th>
                            @endif --}}
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                </div>
            @endif

        </div>
    </div>

@stop

@push('after-scripts')
    <script>

        $(document).ready(function () {
            var route = '{{route('attendance.attendance.get_data',[$course_id,$lesson_id])}}';

            
            

            @if($course_id && $lesson_id)

            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: 'lfBrtip<"actions">',
                buttons: [
                    {
                        extend: 'csv',
                        exportOptions: {
                            columns: [ 1, 2, 3, 4]
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: [ 1, 2, 3, 4]
                        }
                    },
                    'colvis'
                ],
                ajax: route,
                columns: [
                        
                    {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable:false},
                    {data: "username", name: 'username'},
                    {data: "title", name: 'title'},
                    {data: "course", name: 'course'},
                    {data: "duration", name: 'duration'},
                    
                    //{data: "actions", name: "actions"}
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

            @endif

        });

    </script>
@endpush