@extends('backend.layouts.app')
@section('title', 'Employee'.' | '.app_name())
@push('after-styles')
    <link rel="stylesheet" href="{{asset('assets/css/colors/switch.css')}}">
     <style>
        .switch.switch-3d.switch-lg {
    width: 40px;
    height: 20px;
}
.switch.switch-3d.switch-lg .switch-handle {
    width: 20px;
    height: 20px;
}


.dataTables_paginate.paging_simple_numbers{
width: 44% !important;
}
  #myTable {
        table-layout: fixed !important;
        width: 100% !important;
    }

    </style>
@endpush
@section('content')
<div>
    <div>
 <div
        class="d-flex justify-content-between pb-3 align-items-center">
        <div class="grow">
            <h4 class="text-20">Trainee</h4>
        </div>
        @can('trainee_create')
        <div>
            <a href="{{ route('admin.employee.external.create') }}">

                <button
                    type="button"
                    class="add-btn">
                    @lang('strings.backend.general.app_add_new')
                </button>

            </a>

        </div>
        @endcan


    </div>
    </div>
    <div class="card" style="border: none;">
        <div class="card-body">
           

                
                           <div class="">
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

                            <table id="myTable" class="table custom-teacher-table table-striped" style="width: 1300px;">
                                 <thead>
                                <tr>
    
                                    @can('category_delete')
                                        @if ( request('show_deleted') != 1 )
                                            <th style="text-align:center;"><input type="checkbox" class="mass"
                                                                                  id="select-all"/>
                                            </th>@endif
                                    @endcan
    
                                    {{-- <th>#</th> --}}
                                    <th style="width: 80px;">@lang('ID')</th>
                                    <th >@lang('labels.backend.teachers.fields.first_name')</th>
                                    <th>@lang('labels.backend.teachers.fields.last_name')</th>
                                    <th>@lang('labels.backend.teachers.fields.email')</th>
                                    <th>@lang('Classification Number')</th>
                                    <!--th>Position</th> -->
                                    <th>@lang('labels.backend.teachers.fields.status')</th>
                                    @if( request('show_deleted') == 1 )
                                        <th style="text-align:center;">@lang('strings.backend.general.actions')</th>
                                    @else
                                        <th style="text-align:center;">@lang('strings.backend.general.actions')</th>
                                    @endif
                                </tr>
                                </thead>
    
                                <tbody>
                                </tbody>
            </table>
    
    
                            <!-- <table id="myTable"
                                   class="table dt-select custom-teacher-table @if(auth()->user()->isAdmin()) @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                                <thead>
                                <tr>
    
                                    @can('category_delete')
                                        @if ( request('show_deleted') != 1 )
                                            <th style="text-align:center;"><input type="checkbox" class="mass"
                                                                                  id="select-all"/>
                                            </th>@endif
                                    @endcan
    
                                    {{-- <th>#</th> --}}
                                    <th>@lang('ID')</th>
                                    <th>@lang('labels.backend.teachers.fields.first_name')</th>
                                    <th>@lang('labels.backend.teachers.fields.last_name')</th>
                                    <th>@lang('labels.backend.teachers.fields.email')</th>
                                    <th>@lang('Classification Number')</th>
                                 
                                    <th>@lang('labels.backend.teachers.fields.status')</th>
                                    @if( request('show_deleted') == 1 )
                                        <th>&nbsp; @lang('strings.backend.general.actions')</th>
                                    @else
                                        <th>&nbsp; @lang('strings.backend.general.actions')</th>
                                    @endif
                                </tr>
                                </thead>
    
                                <tbody>
                                </tbody>
                            </table> -->
                        </div>
            

        </div>
    </div>
</div>

   

@endsection

@push('after-scripts')
    <script>

        $(document).ready(function () {



            var route = '{{route('admin.employee.get_external_data')}}';

            @if(request('show_deleted') == 1)
                route = '{{route('admin.employee.get_external_data',['show_deleted' => 1])}}';
            @endif

           var table = $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: "<'table-controls'lfB>" +
                     "<'table-responsive't>" +
                     "<'d-flex justify-content-between flex-wrap mt-3'ip><'actions'>",
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
    text: '<i class="fa fa-eye icon-styles" aria-hidden="" ></i>',
    },
],
                // buttons: [
                //     {
                //         extend: 'csv',
                //         exportOptions: {
                //             columns: [ 1, 2, 3, 4,5]
                //         }
                //     },
                //     {
                //         extend: 'pdf',
                //         exportOptions: {
                //             columns: [ 1, 2, 3, 4,5],
                //         }
                //     },
                //     'colvis'
                // ],
                ajax: route,
                columns: [
                        
                    // {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable:false},
                    {data: "id", name: 'id'},
                    {data: "first_name", name: 'first_name'},
                    {data: "last_name", name: 'last_name'},
                    {data: "email", name: 'email'},
                    {data: "classfi_number", name: 'classfi_number'},
                    // {data: "position", name: 'position'},
                    {data: "status", name: 'status'},
                    {data: "actions", name: 'actions'}
                ],
                
initComplete: function () {
                    let $searchInput = $('#myTable_filter input[type="search"]');
    $searchInput
        .addClass('custom-search')
        .wrap('<div class="search-wrapper position-relative d-inline-block"></div>')
        .after('<i class="fa fa-search search-icon"></i>');

    $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
                },
//                   drawCallback: function () {
//     $('.dataTables_paginate .paginate_button.previous, .dataTables_paginate .paginate_button.next').css({
//         'border-radius': '5px',
//         'padding': '6px 15px',
//         'font-weight': '500',
        
//         'color': 'white',
//         'border': 'none',
//         'margin': '0 5px'
//     });
//     $('.dataTables_paginate .paginate_button').not('.previous, .next').css({
//         'background-color': '#f0f0f0',
//         'color': '#333',
//         'border': '1px solid #ccc',
//         'border-radius': '7px',
//         'padding': '6px 12px',
//         'margin': '0 4px',
//         'font-weight': '500'
//     });

//     // Style current/active page
//     $('.dataTables_paginate .paginate_button.current').css({
//         'background-color': '#0d6efd',
//         'color': 'white',
//         'border': 'none',
//         'font-weight': 'bold'
//     });
// },
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
    //                              paginate: {
    //     previous: '<i class="fa fa-angle-left"></i>',
    //     next: '<i class="fa fa-angle-right"></i>'
    // },
                }

            });
            @if(auth()->user()->isAdmin())
            $('.actions').html('<a href="' + '{{ route('admin.teachers.mass_destroy') }}' + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>');
            @endif



        });
        $(document).on('click', '.status-toggle', function (e) {
            var id = $(this).data('id');
            $.ajax({
                type: "POST",
                url: "{{ route('admin.employee.status') }}",
                data: {
                    _token:'{{ csrf_token() }}',
                    id: id,
                },
            }).done(function() {
                var table = $('#myTable').DataTable();
                table.ajax.reload();
            });
        })

    </script>

@endpush