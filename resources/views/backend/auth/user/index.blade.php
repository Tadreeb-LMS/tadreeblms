@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('labels.backend.access.users.management'))
@push('after-styles')
<link rel="stylesheet" href="{{asset('assets/css/colors/switch.css')}}">
 <style>
         #myTable {
        table-layout: fixed !important;
        width: 100% !important;
    }
    .dropdown-item{
        border-bottom: none;
    }
    </style>

@endpush

@section('breadcrumb-links')
    @include('backend.auth.user.includes.breadcrumb-links')
@endsection

@section('content')
<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4>
           {{ __('labels.backend.access.users.management') }}
                <small class="text-muted ml-3">{{ __('labels.backend.access.users.active') }}</small>
    </h4>
     <div class="">
                   @include('backend.auth.user.includes.header-buttons')
            </div>
</div>
    <div class="card">
        <div class="card-body">
        

            <div class="row mt-4">
                <div class="col">

                    <div class="table-responsive">
                        <table id="myTable" class="table dt-select custom-teacher-table table-striped" style="width: 2500px;">
                            <thead>
                            <tr>
                                <th style="width: 100px;">@lang('labels.general.sr_no')</th>
                                {{-- <th style="width: 100px;">@lang('labels.general.id')</th> --}}
                                <th style="width: 100px;">@lang('labels.backend.access.users.table.first_name')</th>
                                <th style="width: 100px;">@lang('labels.backend.access.users.table.last_name')</th>
                                <th style="width: 100px;">@lang('labels.backend.access.users.table.email')</th>
                                <th style="width: 100px;">@lang('labels.backend.access.users.table.confirmed')</th>
                                <th style="width: 100px;">@lang('labels.backend.access.users.table.roles')</th>
                                <th style="width: 130px;">@lang('labels.backend.access.users.table.other_permissions')</th>
                                <th style="width: 100px;">@lang('labels.backend.access.users.table.social')</th>
                                <th style="width: 100px;">@lang('labels.backend.access.users.table.last_updated')</th>
                                <th style="width: 300px;" class="text-center">@lang('labels.general.actions')</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div><!--col-->
            </div><!--row-->

        </div><!--card-body-->
    </div><!--card-->
@endsection


@push('after-scripts')
    <script>

        $(document).ready(function () {
            var route = '{{route('admin.auth.user.getData')}}';

            var myTable = $('#myTable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: "<'table-controls'lfB>" +
                     "<'table-responsive't>" +
                     "<'d-flex justify-content-between align-items-center mt-3'ip><'actions'>",
                // buttons: [
                //     {
                //         extend: 'csv',
                //         exportOptions: {
                //             columns: [1, 2, 3, 4, 5, 6, 7, 8]
                //         }
                //     },
                //     {
                //         extend: 'pdf',
                //         exportOptions: {
                //             columns: [1, 2, 3, 4, 5, 6, 7, 8]
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
                    url: route,
                    data: function (d) {
                        d.role = $('#roles').val();
                    }
                },
                columns: [
                    {data: "DT_RowIndex", name: 'DT_RowIndex', "orderable": false, "searchable": false},
                    // {data: "id", name: 'id', "orderable": false},
                    {data: "first_name", name: 'first_name'},
                    {data: "last_name", name: 'last_name'},
                    {data: "email", name: "email"},
                    {data: "confirmed_label", name: "confirmed_label"},
                    {data: "roles_label", name: "roles.name"},
                    {data: "permissions_label", name: "permissions.name"},
                    {data: "social_buttons", name: "social_accounts.provider", "searchable": false},
                    {data: "last_updated", name: "last_updated"},
                    {data: "actions", name: "actions", "searchable": false}
                ],

  initComplete: function () {
                     let $searchInput = $('#myTable_filter input[type="search"]');
    $searchInput
        .addClass('custom-search')
        .wrap('<div class="search-wrapper position-relative d-inline-block"></div>')
        .after('<i class="fa fa-search search-icon"></i>');

    $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
                },

                createdRow: function (row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
                language: {
                    url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{$locale_full_name}}.json",
                    buttons: {
                        colvis: '{{trans("datatable.colvis")}}',
                        pdf: '{{trans("datatable.pdf")}}',
                        csv: '{{trans("datatable.csv")}}',
                    },
                   search:"",
                }
            });


            $(document).on('change', '#roles', function (e) {
                myTable.draw();
                e.preventDefault();
            });
        });

    </script>

@endpush
