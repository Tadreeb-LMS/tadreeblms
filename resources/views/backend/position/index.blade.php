@extends('backend.layouts.app')

@section('title', __('position_pages.index.title') . ' | ' . app_name())

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="page-title d-inline">{{ __('position_pages.index.title') }}</h3>
            @can('blog_create')
                <div class="float-right">
                    <a href="{{ route('admin.position.create') }}" class="btn btn-success">{{ __('position_pages.index.add_position') }}</a>
                </div>
            @endcan

            {{-- <form method="POST" action="{{ route('admin.position.add.import') }}" enctype="multipart/form-data" class="form-horizontal upload_widgets">
                @csrf
                <div class="form-group">
                    <div class="form-group float-left file-upload">
                        <label for="exampleInputFile" style="display:block">Import Position</label>
                        <input type="file" name="file" id="file" size="250" required>
                    </div>
                    <button type="submit" class="btn btn-primary add-price-btn-import float-right" name="submit" value="submit">Import</button>
                </div>
            </form> --}}

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <div class="d-block">
                    <ul class="list-inline">
                        <li class="list-inline-item">
                            <a href="{{ route('admin.position.index') }}" style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">{{ __('position_pages.index.all') }}</a>
                        </li>
                        |
                        <li class="list-inline-item">
                            <a href="{{ route('admin.position.index') }}?show_deleted=1" style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">{{ __('position_pages.index.trash') }}</a>
                        </li>
                    </ul>
                </div>

                <table id="myTable" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        @can('lesson_delete')
                            @if (request('show_deleted') != 1)
                                <th style="text-align:center;">
                                    <input class="mass" type="checkbox" id="select-all"/>
                                </th>
                            @endif
                        @endcan
                        <th>{{ __('position_pages.index.sr_no') }}</th>
                        <th>{{ __('position_pages.index.table_title') }}</th>
                        <th>{{ __('position_pages.index.description') }}</th>
                        <th>{{ __('position_pages.index.status') }}</th>
                        <th>{{ __('position_pages.index.created') }}</th>
                        @if(request('show_deleted') == 1)
                            <th>{{ __('position_pages.index.actions') }}</th>
                        @else
                            <th>{{ __('position_pages.index.actions') }}</th>
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
        $(document).ready(function () {
            var route = '{{ route('admin.position.get_data') }}';

            @if(request('show_deleted') == 1)
                route = '{{ route('admin.position.get_data', ['show_deleted' => 1]) }}';
            @endif

            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: 'lfBrtip<"actions">',
                buttons: [
                    {
                        extend: 'csv',
                        exportOptions: {
                            columns: [1, 2, 3, 4]
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: [1, 2, 3, 4]
                        }
                    },
                    'colvis'
                ],
                ajax: route,
                columns: [
                    @if(request('show_deleted') != 1)
                        {
                            "data": function (data) {
                                return '<input type="checkbox" class="single" name="id[]" value="' + data.id + '" />';
                            },
                            "orderable": false,
                            "searchable": false,
                            "name": "id"
                        },
                    @endif
                    {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                    {data: "title", name: 'title'},
                    {data: "content", name: 'content'},
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

                createdRow: function (row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
                language: {
                    url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{$locale_full_name}}.json",
                    lengthMenu: '{{ trans('datatable.length_menu') }}',
                    search: '',
                    buttons: {
                        colvis: '{{ trans('datatable.colvis') }}',
                        pdf: '{{ trans('datatable.pdf') }}',
                        csv: '{{ trans('datatable.csv') }}',
                    },
                    emptyTable: '{{ __('position_pages.index.no_data_available') }}'
                }
            });

            @can('blog_delete')
                @if(request('show_deleted') != 1)
                    $('.actions').html('<a href="' + '{{ route('admin.position.mass_destroy') }}' + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">{{ __('position_pages.index.delete_selected') }}</a>');
                @endif
            @endcan

        });
    </script>
@endpush
