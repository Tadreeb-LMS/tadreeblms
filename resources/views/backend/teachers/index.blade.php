@extends('backend.layouts.app')

@section('title', __('labels.backend.teachers.title') . ' | ' . app_name())

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('assets/css/colors/switch.css') }}">
    <style>
        .actions-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            white-space: nowrap;
        }

        .actions-cell a,
        .actions-cell button,
        .actions-cell form {
            display: flex !important;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0 !important;
            margin: 0 !important;
        }

        .actions-cell i {
            font-size: 14px;
        }

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
    <div>
        <div class="pb-3 d-flex justify-content-between align-items-center">
            <h4>{{ __('admin_pages.teachers.title') }}</h4>

            @can('trainer_create')
                <div>
                    <a href="{{ route('admin.auth.user.create', ['return_to' => route('admin.teachers.index')]) }}"
                       class="btn add-btn">
                        {{ __('admin_pages.teachers.add_more_trainers') }}
                    </a>
                </div>
            @endcan
        </div>

        <div class="card" style="border: none;">
            <div class="card-body">
                <div class="d-block mt-2">
                    <ul class="list-inline">
                        <li class="list-inline-item">
                            <a href="{{ route('admin.teachers.index') }}"
                               style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">
                                {{ trans('labels.general.all') }}
                            </a>
                        </li>
                        |
                        <li class="list-inline-item">
                            <a href="{{ route('admin.teachers.index', ['show_deleted' => 1]) }}"
                               style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">
                                {{ trans('labels.general.trash') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <table id="myTable" class="custom-teacher-table table-striped" style="width: 1550px;">
                    <thead>
                        <tr>
                            @can('trainer_delete')
                                @if(request('show_deleted') != 1)
                                    <th style="text-align:center;">
                                        <input type="checkbox" class="mass" id="select-all">
                                    </th>
                                @endif
                            @endcan

                            <th>{{ __('labels.general.sr_no') }}</th>
                            <th>{{ __('admin_pages.employee.employee_id') }}</th>
                            <th>{{ __('labels.backend.teachers.fields.first_name') }}</th>
                            <th>{{ __('labels.backend.teachers.fields.last_name') }}</th>
                            <th>{{ __('labels.backend.teachers.fields.email') }}</th>
                            <th>{{ __('admin_pages.auth_users.department') }}</th>
                            <th>{{ __('position_pages.table.position_name') }}</th>
                            @if(request('show_deleted') != 1)
                                <th>{{ __('labels.backend.teachers.fields.status') }}</th>
                            @endif
                            <th style="text-align:center;">{{ __('strings.backend.general.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
    <script>
        $(document).ready(function () {
            var route = '{{ route('admin.teachers.get_data') }}';

            @if(request('show_deleted') == 1)
                route = '{{ route('admin.teachers.get_data', ['show_deleted' => 1]) }}';
            @endif

            var table = $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: "<'table-controls'lfB>" +
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
                                text: 'CSV',
                                exportOptions: { columns: [1, 2, 3, 4, 5] }
                            },
                            {
                                extend: 'pdf',
                                text: 'PDF',
                                exportOptions: { columns: [1, 2, 3, 4, 5] }
                            }
                        ]
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-eye icon-styles" aria-hidden="true"></i>'
                    }
                ],
                ajax: route,
                columns: [
                    @can('trainer_delete')
                        @if(request('show_deleted') != 1)
                            {
                                data: null,
                                name: 'checkbox',
                                orderable: false,
                                searchable: false,
                                render: function (data, type, row) {
                                    return '<input type="checkbox" class="mass" name="ids[]" value="' + row.id + '">';
                                }
                            },
                        @endif
                    @endcan
                    { data: 'id', name: 'id' },
                    { data: 'emp_id', name: 'emp_id' },
                    { data: 'first_name', name: 'first_name' },
                    { data: 'last_name', name: 'last_name' },
                    { data: 'email', name: 'email' },
                    { data: 'department', name: 'department', orderable: false, searchable: false },
                    { data: 'position', name: 'position', orderable: false, searchable: false },
                    @if(request('show_deleted') != 1)
                        { data: 'status', name: 'status', orderable: false, searchable: false },
                    @endif
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                @if(request('show_deleted') != 1)
                    columnDefs: [
                        { width: '5%', targets: -1 },
                        { className: 'text-center', targets: -1 }
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
                createdRow: function (row, data) {
                    $(row).attr('data-entry-id', data.id);
                },
                language: {
                    url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{ $locale_full_name }}.json",
                    buttons: {
                        colvis: '{{ trans('datatable.colvis') }}',
                        pdf: '{{ trans('datatable.pdf') }}',
                        csv: '{{ trans('datatable.csv') }}'
                    },
                    emptyTable: '{{ __('admin_pages.teachers.no_data_available') }}',
                    search: ''
                }
            });

            @if(auth()->user()->isAdmin())
                $('.actions').html(
                    '<a href="{{ route('admin.teachers.mass_destroy') }}" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left:20px;">{{ __('admin_pages.teachers.delete_selected') }}</a>'
                );
            @endif

            $(document).on('change', '.switch-input', function () {
                let checkbox = $(this);
                let id = checkbox.data('id');
                let isChecked = checkbox.prop('checked');
                let message = isChecked
                    ? '{{ __('admin_pages.teachers.activate_user_confirm') }}'
                    : '{{ __('admin_pages.teachers.deactivate_user_confirm') }}';

                if (!confirm(message)) {
                    checkbox.prop('checked', !isChecked);
                    return false;
                }

                $.ajax({
                    type: 'POST',
                    url: '{{ route('admin.teachers.status') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        status: isChecked ? 1 : 0
                    },
                    success: function () {
                        table.ajax.reload(null, false);
                    },
                    error: function () {
                        checkbox.prop('checked', !isChecked);
                        alert('{{ __('admin_pages.teachers.something_went_wrong') }}');
                    }
                });
            });
        });
    </script>
@endpush
