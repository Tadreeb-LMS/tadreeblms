@extends('backend.layouts.app')

@section('title', __('labels.backend.categories.title').' | '.app_name())
@push('after-styles')
    <link rel="stylesheet" href="{{asset('assets/css/colors/switch.css')}}">
    
    <style>

 .dropdown-menu {
        min-width: 160px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        padding: 5px;
    }
    .dropdown-item{
        border-bottom: none;
    }

    </style>
@endpush

@section('content')
    <div class="pb-3 d-flex justify-content-between align-items-center">
            <h4>
              @lang('labels.backend.categories.title')
            </h4>
 <div class="">
                <a href="{{ route('admin.categories.create') }}"
                   class="btn add-btn">@lang('strings.backend.general.app_add_new')</a>
            </div>
        </div>

    <div class="card">
        <!-- <div class="card-header">

            <h3 class="page-title d-inline">@lang('labels.backend.categories.title')</h3>
            <div class="float-right">
                <a href="{{ route('admin.categories.create') }}"
                   class="btn btn-success">@lang('strings.backend.general.app_add_new')</a>
            </div>

        </div> -->
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="">
                        <div class="d-block">
                            <ul class="list-inline">
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.categories.index') }}"
                                       style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">{{trans('labels.general.all')}}</a>
                                </li>
                                |
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.categories.index') }}?show_deleted=1"
                                       style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">{{trans('labels.general.trash')}}</a>
                                </li>
                            </ul>
                        </div>


                        <table id="myTable"
                               class="table custom-teacher-table table-striped @can('category_delete') @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                            <thead>
                            <tr>

                                @can('category_delete')
                                    @if ( request('show_deleted') != 1 )
                                        <th style="text-align:center;">
                                            <input type="checkbox" class="mass" id="select-all"/>
                                        </th>
                                    @endif
                                @endcan

                                {{-- <th>@lang('labels.general.sr_no')</th> --}}
                                {{-- <th>@lang('labels.general.id')</th> --}}
                                <th>@lang('labels.backend.categories.fields.name')</th>
                                <th>@lang('labels.backend.categories.fields.slug')</th>
                                <!-- <th>@lang('labels.backend.categories.fields.icon')</th> -->
                                <th>@lang('labels.backend.categories.fields.courses')</th>
                                <th>@lang('labels.backend.categories.fields.blog')</th>
                                @if( request('show_deleted') == 1 )
                                    <th style="text-align:center;">@lang('strings.backend.general.actions')</th>
                                @else
                                    <th style="text-align:center;width:120px">@lang('strings.backend.general.actions')</th>
                                @endif
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

@stop

@push('after-scripts')
    <script>
        $(document).ready(function () {
            var route = '{{route('admin.categories.get_data')}}';

            @if(request('show_deleted') == 1)
                route = '{{route('admin.categories.get_data',['show_deleted' => 1])}}';
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
                        @can('category_delete')
                        @if(request('show_deleted') != 1)
                    {
                        "data": function (data) {
                            return '<input type="checkbox" class="single" name="id[]" value="' + data.id + '" />';
                        }, "orderable": false, "searchable": false, "name": "id"
                    },
                        @endif
                        @endcan
                    // {data: "DT_RowIndex", name: 'DT_RowIndex',searchable: false, orderable: false},
                    // {data: "id", name: 'id'},
                    {data: "name", name: 'name'},
                    {data: "slug", name: 'slug'},
                    // {data: "icon", name: 'icon'},
                    {data: "courses", name: "courses"},
                    {data: "blogs", name: "blogs"},
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
                language: {
                    url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{$locale_full_name}}.json",
                    buttons: {
                        colvis: '{{trans("datatable.colvis")}}',
                        pdf: '{{trans("datatable.pdf")}}',
                        csv: '{{trans("datatable.csv")}}',
                    },
                    search:"",
    //                   paginate: {
    //     previous: '<i class="fa fa-angle-left"></i>',
    //     next: '<i class="fa fa-angle-right"></i>'
    // },
                }
            });
            @can('category_access')
            @if(request('show_deleted') != 1)
            $('.actions').html('<a href="' + '{{ route('admin.categories.mass_destroy') }}' + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>');
            @endif
            @endcan

            $(document).on('click', '.delete_warning', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const message = $(this).attr('data-delete-message');

                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        title: message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert(message);
                }

                return false;
            });
        });
    </script>
@endpush
