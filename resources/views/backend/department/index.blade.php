@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')

@section('title', __('menus.backend.sidebar.department').' | '.app_name())
@push('after-styles')
    <link rel="stylesheet" href="{{ asset('assets/css/colors/switch.css') }}">
       <style>
        .dropdown-menu{
            padding: 5px;
        }
      .dropdown-item{
        border-bottom: none;
      }
   
    </style>
@endpush

@section('content')

<div>

    <div class="pb-3">
        <div class="d-flex justify-content-between">
            <h4>@lang('menus.backend.sidebar.department')</h4>

            @can('blog_create')
                <div>
                    <a href="{{ route('admin.department.create') }}" class="btn add-btn">
                        @lang('Add User Group')
                    </a>
                </div>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <div class="row">

                <!-- Import Internal Trainee -->
                <div class="col-6 mb-2">
                    <h6>@lang('Import Internal Trainee')</h6>

                    <form method="POST"
                          action="{{ route('admin.department.add.import') }}"
                          enctype="multipart/form-data">

                        @csrf

                        <div class="d-flex">

                            <div class="custom-file-upload-wrapper" style="margin-top:18px;">
                                <input type="file"
                                       name="file"
                                       id="importFile"
                                       class="custom-file-input"
                                       required>

                                <label for="importFile" class="custom-file-label">
                                    <i class="fa fa-upload mr-1"></i> Choose a file
                                </label>
                            </div>

                            <button type="submit"
                                    class="btn btn-primary ml-3"
                                    name="submit"
                                    value="submit">
                                @lang('Import')
                            </button>

                        </div>

                    </form>
                </div>

                <!-- List Table -->
                <div class="col-12 mt-4">

                    <div class="d-block">
                        <ul class="list-inline">
                            <li class="list-inline-item">
                                <a href="{{ route('admin.department.index') }}"
                                   style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">
                                    {{ trans('labels.general.all') }}
                                </a>
                            </li>
                            |
                            <li class="list-inline-item">
                                <a href="{{ route('admin.department.index') }}?show_deleted=1"
                                   style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">
                                    {{ trans('labels.general.trash') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <table id="myTable" class="table custom-teacher-table table-striped">
                        <thead>
                        <tr>
                            @can('lesson_delete')
                                @if(request('show_deleted') != 1)
                                    <th style="text-align:center;">
                                        <input type="checkbox" id="select-all" class="mass">
                                    </th>
                                @endif
                            @endcan

                            <th>@lang('labels.general.sr_no')</th>
                            <th>@lang('labels.backend.pages.fields.title')</th>
                            <th>@lang('labels.backend.pages.fields.status')</th>
                            <th>@lang('labels.backend.pages.fields.created')</th>

                            <th style="text-align:center;">
                                @lang('strings.backend.general.actions')
                            </th>
                        </tr>
                        </thead>

                        <tbody></tbody>
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
            var route = '{{route('admin.department.get_data')}}';

            @if(request('show_deleted') == 1)
                route = '{{route('admin.department.get_data',['show_deleted' => 1])}}';
            @endif
            $('#myTable').DataTable({
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
                ajax: route,
                columns: [
                        @if(request('show_deleted') != 1)
                    {
                        "data": function (data) {
                            return '<input type="checkbox" class="single" name="id[]" value="' + data.id + '" />';
                        }, "orderable": false, "searchable": false, "name": "id"
                    },
                        @endif
                    {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                    {data: "title", name: 'title'},
                    {data: "status", name: 'status'},
                    {data: "created", name: "created"},
                    {data: "actions", name: "actions"}
                ],
                @if(request('show_deleted') != 1)
                columnDefs: [
                    {"width": "5%", "targets": 0},
                    {"className": "text-center", "targets": [0]}
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

                createdRow: function (row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
                language:{
                    url : "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{$locale_full_name}}.json",
                    buttons :{
                        colvis : '{{trans("datatable.colvis")}}',
                        pdf : '{{trans("datatable.pdf")}}',
                        csv : '{{trans("datatable.csv")}}',
                    },
                    search:"",
    //                  paginate: {
    //     previous: '<i class="fa fa-angle-left"></i>',
    //     next: '<i class="fa fa-angle-right"></i>'
    // },
                }
            });


            @can('blog_delete')
            @if(request('show_deleted') != 1)
            $('.actions').html('<a href="' + '{{ route('admin.department.mass_destroy') }}' + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>');
            @endif
            @endcan

        });

    </script>
    <script>
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const label = input.nextElementSibling;
            const fileName = e.target.files.length > 0 ? e.target.files[0].name : 'Choose a file';
            label.innerHTML = '<i class="fa fa-upload mr-1"></i> ' + fileName;
        });
    });
</script>
@endpush