@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')

@section('title', __('labels.backend.subscription.title').' | '.app_name())

@section('content')

<div class="userheading">

    <h4 class=""> <span>@lang('Subscription')</span> </h4>


</div>

<div class="pb-3 d-flex justify-content-between align-items-center">
  <!--  <h4 class="">@lang('Subscription')</h4> -->
    @can('blog_create')
        <div class="">
            <!--a href="{{ route('admin.department.create') }}" class="btn btn-success">Add Department</a-->
        </div>
    @endcan
</div>
<div class="card">
    <div class="card-body">

        <div class="table-responsive">
            <div class="d-block">
                <ul class="list-inline">
                    <li class="list-inline-item">
                        <a href="{{ route('user.subscriptions') }}"
                           style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">{{trans('labels.general.all')}}</a>
                    </li>
                    |
                    <li class="list-inline-item">
                        <a href="{{ route('user.subscriptions') }}?show_deleted=1"
                           style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">{{trans('labels.general.trash')}}</a>
                    </li>
                </ul>
            </div>


            <table id="myTable"
                   class="table custom-teacher-table table-striped">
                <thead>
                <tr>
                    @can('lesson_delete')
                        @if ( request('show_deleted') != 1 )
                            <th style="text-align:center;"><input class="mass" type="checkbox" id="select-all"/>
                            </th>@endif
                    @endcan
                    <th>@lang('labels.general.sr_no')</th>
                    <th>@lang('User Name')</th>
                    <th>@lang('Email')</th>
                    <th>@lang('Course Name')</th>
                    <th>@lang('labels.backend.pages.fields.status')</th>
                    <th>@lang('labels.backend.pages.fields.created')</th>
                    @if( request('show_deleted') == 1 )
                        <th>@lang('strings.backend.general.actions') &nbsp;</th>
                    @else
                        <th>@lang('strings.backend.general.actions') &nbsp;</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>

        </div>

    </div>
</div>
@stop

@push('after-scripts')
    <script>

        $(document).ready(function () {
            var route = '{{route('user.subscriptions.getdata')}}';

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
                            columns: ':visible',
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':visible',
                        }
                    },
                    'colvis'
                ],
                ajax: route,
                columns: [

                    {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable:false},
                    {data: "user_name", name: 'user_name'},
                    {data: "email", name: 'email'},
                    {data: "course_id", name: 'course_id'},
                    {data: "status", name: 'status'},
                    {data: "created", name: "created"},
                    {data: "actions", name: "actions"}
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
                language:{
                    url : "{{ asset("js/datatables/i18n/{$locale_full_name}.json") }}",
                    buttons :{
                        colvis : '{{trans("datatable.colvis")}}',
                        pdf : '{{trans("datatable.pdf")}}',
                        csv : '{{trans("datatable.csv")}}',
                    },
                    search:""
                }
            });

        });

    </script>

@endpush
