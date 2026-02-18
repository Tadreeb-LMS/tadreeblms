@extends('backend.layouts.app')

@section('title', 'news & update'.' | '.app_name())

@push('after-styles')
<link rel="stylesheet" href="{{asset('assets/css/colors/switch.css')}}">
 <style>
       

    
    </style>

@endpush

@section('content')

<div class="pb-3 d-flex justify-content-between align-items-center">

        <h4 >@lang('News & Update')</h4>
        <div >
            <a href="{{ route('admin.news.create') }}"
               class="add-btn">@lang('strings.backend.general.app_add_new')</a>
        </div>

</div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                       
                        <table id="myTable"
                               class="table dt-select custom-teacher-table table-striped @can('category_delete') @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                            <thead>
                            <tr>

                                @can('category_delete')
                                    @if ( request('show_deleted') != 1 )
                                        <th style="text-align:center;">
                                            <input type="checkbox" class="mass" id="select-all"/>
                                        </th>
                                    @endif
                                @endcan

                                <th>@lang('labels.general.sr_no')</th>
                                <th>@lang('Id')</th>
                                <th>@lang('labels.backend.reasons.fields.title')</th>
                                <th>@lang('labels.backend.reasons.fields.icon')</th>
                                <th>@lang('labels.backend.reasons.fields.content')</th>
                                <th>@lang('labels.backend.reasons.fields.status')</th>
                                @if( request('show_deleted') == 1 )
                                    <th>&nbsp; @lang('strings.backend.general.actions')</th>
                                @else
                                    <th class="text-center" style="width: 100px;">@lang('strings.backend.general.actions')</th>
                                @endif
                            </tr>
                            </thead>

                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>
            <hr>
            {{-- <h4>@lang('labels.backend.reasons.note')</h4>
            <img src="{{asset('images/reasons.jpg')}}" width="100%"  > --}}


        </div>
    </div>

@stop

@push('after-scripts')
    <script>

        $(document).ready(function () {
            var route = '{{route('admin.news.get_data')}}';

            @if(request('show_deleted') == 1)
                route = '{{route('admin.news.get_data',['show_deleted' => 1])}}';
            @endif

            $('#myTable').DataTable({
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
                //             columns: [ 1, 2, 3,5]
                //         }
                //     },
                //     {
                //         extend: 'pdf',
                //         exportOptions: {
                //             columns: [ 1, 2, 3,5]
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
                ajax: route,
                columns: [
                        @if(request('show_deleted') != 1)
                    {
                        "data": function (data) {
                            return '<input type="checkbox" class="single" name="id[]" value="' + data.id + '" />';
                        }, "orderable": false, "searchable": false, "name": "id"
                    },
                        @endif
                    {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false},
                    {data: "id", name: 'id'},
                    {data: "title", name: 'title'},
                    {data: "icon", name: 'icon'},
                    {data: "content", name: 'content'},
                    {data: "status", name: 'Status'},
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
                }
            });

        });

        $(document).on('click', '.switch-input', function (e) {
            var id = $(this).data('id');
            $.ajax({
                type: "POST",
                url: "{{ route('admin.news.status') }}",
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