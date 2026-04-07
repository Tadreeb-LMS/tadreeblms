@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')

@section('title', __('admin_pages.subscription.title') . ' | ' . app_name())
@push('after-styles')
 <style>
    

 
    </style>

@section('content')

<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4>{{ __('admin_pages.subscription.employee_requests') }}</h4>
    @can('blog_create')
        <div >
            <a href="#" class="add-btn">{{ __('admin_pages.subscription.view_all') }}</a>
        </div>
    @endcan
</div>
    <div class="card">
        <div class="card-body">

            <div class="table-responsive">
                <div class="d-block">
                    <ul class="list-inline">
                        <li class="list-inline-item">
                            <a href="{{ route('admin.subscription.index') }}"
                                style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">{{ trans('labels.general.all') }}</a>
                        </li>
                        |
                        <li class="list-inline-item">
                            <a href="{{ route('admin.subscription.index') }}?show_deleted=1"
                                style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">{{ trans('labels.general.trash') }}</a>
                        </li>
                    </ul>
                </div>


                <table id="myTable" class="table dt-select custom-teacher-table table-striped">
                    <thead>
                        <tr>
                            @can('lesson_delete')
                                @if (request('show_deleted') != 1)
                                    <th style="text-align:center;"><input class="mass" type="checkbox" id="select-all" />
                                    </th>
                                @endif
                            @endcan
                            <th>{{ __('admin_pages.subscription.trainee_no') }}</th>
                            <th>{{ __('admin_pages.subscription.user_name') }}</th>
                            <th>{{ __('admin_pages.subscription.email') }}</th>
                            <th>{{ __('admin_pages.subscription.course_name') }}</th>
                            <th>{{ __('admin_pages.subscription.position') }}</th>
                            <th>@lang('labels.backend.pages.fields.status')</th>
                            <th>@lang('labels.backend.pages.fields.created')</th>
                            @if (request('show_deleted') == 1)
                                <th>@lang('strings.backend.general.actions') &nbsp;</th>
                            @else
                                <th>@lang('strings.backend.general.actions') &nbsp;</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

            </div>

        </div>
    </div>

@endsection

@push('after-scripts')
    <script>
        $(document).ready(function() {
            var route = '{{ route('admin.subscription.get_data') }}';

            @if (request('show_deleted') == 1)
                route = '{{ route('admin.subscription.get_data', ['show_deleted' => 1]) }}';
            @endif



            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: "<'table-controls'lf>" +
                     "<'table-responsive't>" +
                     "<'d-flex justify-content-between align-items-center mt-3'ip><'actions'>",
                // buttons: [{
                //         extend: 'csv',
                //         exportOptions: {
                //             columns: [1, 2, 3, 4]
                //         }
                //     },
                //     {
                //         extend: 'pdf',
                //         exportOptions: {
                //             columns: [1, 2, 3, 4]
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
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
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
                    @endif {
                        data: "DT_RowIndex",
                        name: 'DT_RowIndex',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: "user_name",
                        name: 'user_name'
                    },
                    {
                        data: "email",
                        name: 'email'
                    },
                    {
                        data: "course_id",
                        name: 'course_id'
                    },
                    {
                        data: "position",
                        name: 'position'
                    },
                    {
                        data: "status",
                        name: 'status'
                    },
                    {
                        data: "created",
                        name: "created"
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
                    lengthMenu: '{{ trans('datatable.length_menu') }}',
                    emptyTable: '{{ __('admin_pages.subscription.no_data_available') }}',
                    buttons: {
                        colvis: '{{ trans('datatable.colvis') }}',
                        pdf: '{{ trans('datatable.pdf') }}',
                        csv: '{{ trans('datatable.csv') }}',
                    },
                                search:"",
       
                }
            });


            @can('blog_delete')
                @if (request('show_deleted') != 1)
                    $('.actions').html('<a href="' + '{{ route('admin.subscription.mass_destroy') }}' +
                        '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">{{ __('admin_pages.subscription.delete_selected') }}</a>'
                        );
                @endif
            @endcan

        });

        $(document).on('click', '.switch-input', function(e) {
            var id = $(this).data('id');
            $.ajax({
                type: "POST",
                url: "{{ route('admin.subscription.status') }}",
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
