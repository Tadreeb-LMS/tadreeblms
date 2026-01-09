@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')
@section('title', __('Learning Pathways') . ' | ' . app_name())
@push('after-styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet" />
      <style>

    </style>
@endpush
@section('content')
<div class="pb-3 align-items-center d-flex justify-content-between">
    <h5 >@lang('Learning Pathways')</h5>
    <div >
         @can('learning_pathway_create')
        <a href="{{ route('admin.learning-pathways.create') }}" class="btn add-btn">@lang('strings.backend.general.app_add_new')</a>
        @endcan
    </div>
</div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="myTable" class="table dt-select custom-teacher-table table-striped">
                    <thead>
                        <tr>
                            <th>@lang('Name')</th>
                            <th>@lang('Courses')</th>
                            <th>@lang('Description')</th>
                            <th>@lang('In Sequence')</th>
                            <th>@lang('strings.backend.general.actions')</th>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('/js/pages/learning-pathway.js') }}"></script>
    <script src="{{ asset('/js/helpers/confirm-modal.js') }}"></script>
    <script src="{{ asset('/js/helpers/load-modal.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.6/Sortable.min.js"></script>
    <script src="{{ asset('/js/helpers/form-submit.js') }}"></script>
    <script>
      
    const canEditLearningPathway = @json(auth()->user()->can('learning_pathway_edit'));
    const editRouteTemplate = "{{ route('admin.learning-pathways.edit', ':id') }}";
</script>
    </script>
    <script>
        $(function () {
        const dt = $('#myTable').DataTable({
        processing: true,
        serverSide: true,
        iDisplayLength: 10,
        retrieve: true,
        ajax: "{{ route('admin.learning-pathways.index')}}",
        columns: [
            {
                data: "title",
                name: 'title'
            },
            {
                data: "courses",
                name: 'courses'
            },
            {
                data: "description",
                name: 'description'
            },
            {
                data: "in_sequence",
                name: 'in_sequence'
            },
            {
                data: "actions",
                render: function (data, type, row, meta) {

        if (!canEditLearningPathway) {
            return '';
        }

        const editUrl = editRouteTemplate.replace(':id', row.id);

        return `
            <div class="actions d-flex">
                <a class="btn btn-info me-2" href="${editUrl}">
                    <i class="fa fa-edit"></i>
                </a>
            </div>
        `;
    }
            },
        ],
        dom: "<'table-controls'lf>" +
                     "<'table-responsive't>" +
                     "<'d-flex justify-content-between align-items-center mt-3 pagination-responsive'ip><'actions'>",
        initComplete: function () {
                   let $searchInput = $('#myTable_filter input[type="search"]');
        $searchInput
            .addClass('custom-search')
            .wrap('<div class="search-wrapper position-relative d-inline-block"></div>')
            .after('<i class="fa fa-search search-icon"></i>');

        $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
                },
                   
        language:{
            search:""
        }
                
            });

        // <a class="btn btn-info loadModal" href="/user/learning-pathways/manage-users/${row.id}">Manage Users</i></a>   
        $(document).on("pathway_deleted", ".modal", function (event, params) {
            dt.draw();
        });
        });
    </script>
@endpush
