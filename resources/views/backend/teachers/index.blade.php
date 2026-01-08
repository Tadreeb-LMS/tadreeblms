@extends('backend.layouts.app')
@section('title', __('labels.backend.teachers.title').' | '.app_name())
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

/* Column visibility dropdown checkbox styles */
.dt-button-collection .dt-button {
    padding: 0 !important;
    background: transparent !important;
}

.dt-button-collection .dt-button:hover {
    background: #f5f5f5 !important;
}

.dt-button-collection .dt-button label {
    cursor: pointer;
    display: block;
    padding: 8px 12px;
    margin: 0;
    width: 100%;
    user-select: none;
}

.dt-button-collection .dt-button input[type="checkbox"] {
    margin-right: 8px;
    cursor: pointer;
}
    </style>
    
@endpush
@section('content')

<div class="">
    <div
              class="d-flex justify-content-between pb-3 align-items-center"
            >
        <h4 class="">Trainers</h4>
               @can('course_create')
                <div>
                    <a href="{{ route('admin.teachers.create') }}"
                    >
                    <button
                               type="button"
                               class="add-btn"
                             >
                               Add
                             </button>
                    </a>
                </div>
            @endcan
            
            </div>

    <div class="card" style="border: none;">
         <div class="card-body">
            <div class="table-responsive">

                <div class="d-block">
                            <ul class="list-inline">
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.teachers.index') }}"
                                       style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">{{trans('labels.general.all')}}</a>
                                </li>
                                |
                                <li class="list-inline-item">
                                    <a href="{{ route('admin.teachers.index') }}?show_deleted=1"
                                       style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">{{trans('labels.general.trash')}}</a>
                                </li>
                            </ul>
                </div>

                     

                            <table id="myTable" class="table custom-teacher-table table-striped" style="">
                                <thead>
                                    <tr>
                   
                                        @can('category_delete')
                                        @if ( request('show_deleted') != 1 )
                                        <th style="text-align:start;"><input type="checkbox" class="mass"
                                                id="select-all" />
                                        </th>@endif
                                        @endcan
                   
                                        {{-- <th>#</th> --}}
                                        <th>@lang('Id ')</th>
                                        <th class="p-2">@lang('labels.backend.teachers.fields.first_name')</th>
                                        <th>@lang('labels.backend.teachers.fields.last_name')</th>
                                        <th>@lang('labels.backend.teachers.fields.email')</th>
                                        <th>@lang('labels.backend.teachers.fields.status')</th>
                                        @if( request('show_deleted') == 1 )
                                        <th>@lang('strings.backend.general.actions')</th>
                                        @else
                                        <th>@lang('strings.backend.general.actions')</th>
                                        @endif
                                    </tr>
                                </thead>
                            
                   
                            </table>
            </div>
                      
             
         </div>
     </div>
</div>


@endsection

@push('after-scripts')

    <script>

        $(document).ready(function () {
  var route = '{{route('admin.teachers.get_data')}}';

            @if(request('show_deleted') == 1)
                route = '{{route('admin.teachers.get_data',['show_deleted' => 1])}}';
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
    className: '',
    columns: ':not(:first-child)',
    columnText: function ( dt, idx, title ) {
        var column = dt.column( idx );
        return '<label style="cursor: pointer; display: block; padding: 5px 10px; margin: 0;">' +
               '<input type="checkbox" style="margin-right: 8px;" ' + (column.visible() ? 'checked' : '') + '> ' +
               title +
               '</label>';
    }
},
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
                    // {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable:false},
                    {data: "id", name: 'id'},
                    {data: "first_name", name: 'first_name'},
                    {data: "last_name", name: 'last_name'},
                    {data: "email", name: 'email'},
                    {data: "status", name: 'status'},
                    {data: "actions", name: 'actions'}
                ],
                @if(request('show_deleted') != 1)
                columnDefs: [
                    {"width": "5%", "targets": 0},
                    {"className": "text-start", "targets": [0]},
                    {"className": "text-center", "targets": [6]}
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
            @if(auth()->user()->isAdmin())
            $('.actions').html('<a href="' + '{{ route('admin.teachers.mass_destroy') }}' + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>');
            @endif



        });
        $(document).on('click', '.switch-input', function (e) {
            //alert("hi")
            var id = $(this).data('id');
            $.ajax({
                type: "POST",
                url: "{{ route('admin.teachers.status') }}",
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