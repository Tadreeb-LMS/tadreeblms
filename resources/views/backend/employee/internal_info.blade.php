@extends('backend.layouts.app')
@section('title', 'Employee' . ' | ' . app_name())
@push('after-styles')
    <link rel="stylesheet" href="{{ asset('assets/css/colors/switch.css') }}">
      <style>
         .switch.switch-3d.switch-lg {
    width: 40px;
    height: 20px;
}
.switch.switch-3d.switch-lg .switch-handle {
    width: 20px;
    height: 20px;
}



    </style>
@endpush
@section('content')

<div class="pb-3">
    <h5 class="">@lang('Trainee Info')</h5>
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
                                        style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">{{ trans('labels.general.all') }}</a>
                                </li>
                                |
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.employee.index') }}?show_deleted=1"
                                        style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">{{ trans('labels.general.trash') }}</a>
                                </li>
                            </ul>
                        </div>


                        <table id="myTable"
                            class="table dt-select custom-teacher-table table-striped @if (auth()->user()->isAdmin()) @if (request('show_deleted') != 1) dt-select @endif @endcan" style="width: 1300px;">
                            <thead>
                                <tr>

                                    @can('category_delete')
                                        @if (request('show_deleted') != 1)
                                            <th style="text-align:center;"><input type="checkbox" class="mass"
                                                    id="select-all" />
                                            </th>
                                        @endif
                                    @endcan

                                    {{-- <th>#</th> --}}
                                    <th>@lang('EID')</th>
                                    <th>@lang('ID')</th>
                                    <th>@lang('labels.backend.teachers.fields.first_name')</th>
                                    <th>@lang('labels.backend.teachers.fields.last_name')</th>
                                    <th>@lang('labels.backend.teachers.fields.email')</th>
                                    <th>@lang('Department')</th>
                                    <th>@lang('Position')</th>
                                    <th style="width: 80px;">@lang('Gender')</th>
                                    <!-- <th>@lang('labels.backend.courses.fields.qr_code')</th> -->
                                    <th style="width: 80px;">@lang('labels.backend.teachers.fields.status')</th>
                                    <!-- @if (request('show_deleted') == 1)
    <th>&nbsp; @lang('strings.backend.general.actions')</th>
@else
    <th>&nbsp; @lang('strings.backend.general.actions')</th>
    @endif -->
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
                dom:  "<'table-controls'lfB>" +
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
                    columns: [1, 2, 3, 4]
                }
            },
           {
                        extend: 'pdfHtml5',
                        text:"PDf",
                        customize: function(doc) {
                            doc.defaultStyle.fontSize = 5; // Adjust the font size as needed
                        }
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
                    // {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable:false},
                    {
                        data: "emp_id",
                        name: 'emp_id'
                    },
                    {
                        data: "id",
                        name: 'id'
                    },
                    {
                        data: "first_name",
                        name: 'first_name'
                    },
                    {
                        data: "last_name",
                        name: 'last_name'
                    },
                    {
                        data: "email",
                        name: 'email'
                    },
                    {
                        data: "department",
                        name: 'department'
                    },
                    {
                        data: "position",
                        name: 'position'
                    },
                    {
                        data: "gender",
                        name: 'gender'
                    },
                    // {data: "qr_code", name: 'qr_code'},
                    {
                        data: "status",
                        name: 'status'
                    },
                    // {data: "actions", name: 'actions'}
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
        $(document).on('click', '.switch-input', function(e) {
            var id = $(this).data('id');
            $.ajax({
                type: "POST",
                url: "{{ route('admin.employee.status') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                },
            }).done(function() {
                var table = $('#myTable').DataTable();
                table.ajax.reload();
            });
        })
    </script>
@endpush
