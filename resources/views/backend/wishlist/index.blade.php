@extends('backend.layouts.app')

@section('title', __('labels.backend.wishlist.title').' | '.app_name())

@push('after-styles')
    <style>
        #myTable_wrapper .dt-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 10px 0;
        }

        #myTable_wrapper .dt-buttons .dt-button.wishlist-table-button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 34px;
            padding: 6px 12px !important;
            border: 1px solid #d8dde6 !important;
            border-radius: 4px !important;
            background: #fff !important;
            color: #233e74 !important;
            font-weight: 600;
            line-height: 1.2;
            box-shadow: none !important;
        }

        #myTable_wrapper .dt-buttons .dt-button.wishlist-table-button:hover,
        #myTable_wrapper .dt-buttons .dt-button.wishlist-table-button:focus {
            border-color: #233e74 !important;
            color: #fff !important;
            background: #233e74 !important;
        }

        #myTable_wrapper .wishlist-button-content {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
    </style>
@endpush

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
            var buttonLabels = {
                csv: @json(__('Export') . ' ' . trans('datatable.csv')),
                pdf: @json(__('Export') . ' ' . trans('datatable.pdf')),
                print: @json(__('Print')),
                colvis: @json(trans('datatable.colvis'))
            };
            var buttonText = function (iconClass, label) {
                return '<span class="wishlist-button-content"><i class="fa ' + iconClass + '" aria-hidden="true"></i><span>' + label + '</span></span>';
            };

            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: 'lfBrtip<"actions">',
                buttons: [
                    {
                        extend: 'csv',
                        text: buttonText('fa-file-text-o', buttonLabels.csv),
                        titleAttr: buttonLabels.csv,
                        className: 'wishlist-table-button',
                        exportOptions: {
                            columns: ':visible',
                        }
                    },
                    {
                        extend: 'pdf',
                        text: buttonText('fa-file-pdf-o', buttonLabels.pdf),
                        titleAttr: buttonLabels.pdf,
                        className: 'wishlist-table-button',
                        exportOptions: {
                            columns: ':visible',
                        }
                    },
                    {
                        extend: 'print',
                        text: buttonText('fa-print', buttonLabels.print),
                        titleAttr: buttonLabels.print,
                        className: 'wishlist-table-button',
                        exportOptions: {
                            columns: ':visible',
                        }
                    },
                    {
                        extend: 'colvis',
                        text: buttonText('fa-columns', buttonLabels.colvis),
                        titleAttr: buttonLabels.colvis,
                        className: 'wishlist-table-button'
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
                language:{
                    url : "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{$locale_full_name}}.json",
                    buttons :{
                        colvis : '{{trans("datatable.colvis")}}',
                        pdf : '{{trans("datatable.pdf")}}',
                        csv : '{{trans("datatable.csv")}}',
                    }
                }
            });

        });

    </script>

@endpush
