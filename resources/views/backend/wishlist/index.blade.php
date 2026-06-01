@extends('backend.layouts.app')

@section('title', __('labels.backend.wishlist.title').' | '.app_name())

@section('content')

    <div class="card">

    <div class="userheading">

    <h4 class=""> <span>@lang('Wishlist')</span> </h4>


</div>
 
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">


                        <table id="myTable"
                               class="table table-bordered table-striped ">
                            <thead>
                            <tr>

                                <th>@lang('labels.general.sr_no')</th>
                                <th>@lang('Id')</th>
                                <th>@lang('CourseTitle')</th>
                                <th>@lang('strings.backend.general.actions')</th>
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
            var route = '{{route('admin.wishlist.get_data')}}';

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
                        titleAttr: '{{ trans("datatable.export") }}',
                        buttons: [
                            {
                                extend: 'csv',
                                text: '{{ trans("datatable.csv") }}',
                                titleAttr: '{{ trans("datatable.export_csv") }}',
                                exportOptions: {
                                    columns: ':visible',
                                }
                            },
                            {
                                extend: 'pdf',
                                text: '{{ trans("datatable.pdf") }}',
                                titleAttr: '{{ trans("datatable.export_pdf") }}',
                                exportOptions: {
                                    columns: ':visible',
                                }
                            },
                            {
                                extend: 'print',
                                text: '{{ trans("datatable.print") }}',
                                titleAttr: '{{ trans("datatable.print") }}',
                                exportOptions: {
                                    columns: ':visible',
                                }
                            }
                        ]
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-eye icon-styles" aria-hidden="true"></i>',
                        titleAttr: '{{ trans("datatable.colvis") }}',
                    }
                ],
                ajax: route,
                columns: [

                    {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable:false},
                    {data: "id", name: 'id'},
                    {data: "course.title", name: 'course.title'},
                    {data: "actions", name: 'actions'},
                ],

                createdRow: function (row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
                initComplete: function () {
                    let $searchInput = $('#myTable_filter input[type="search"]');
                    $searchInput
                        .addClass('custom-search')
                        .wrap('<div class="search-wrapper position-relative d-inline-block"></div>')
                        .after('<i class="fa fa-search search-icon"></i>');

                    $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
                },
                language:{
                    url : "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{$locale_full_name}}.json",
                    buttons :{
                        colvis : '{{trans("datatable.colvis")}}',
                        print : '{{trans("datatable.print")}}',
                        pdf : '{{trans("datatable.pdf")}}',
                        csv : '{{trans("datatable.csv")}}',
                    },
                    lengthMenu: '{{ trans("datatable.length_menu") }}',
                    search: "",
                }
            });

        });

    </script>

@endpush
