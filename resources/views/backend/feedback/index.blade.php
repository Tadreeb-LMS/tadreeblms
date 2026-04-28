@extends('backend.layouts.app')
@section('title', __('user_feedback.feedback_questions.title') . ' | ' . app_name())
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


    .dataTables_paginate.paging_simple_numbers {
        width: 44% !important;
    }
    .dropdown-item {
    position: relative;
    padding: 10px 20px;
    border-bottom: none;
}
</style>

@endpush
@section('content')
<div>
    <div
        class="d-flex justify-content-between pb-3 align-items-center">
        <div class="grow">
            <h5 class="text-20">{{ __('user_feedback.feedback_questions.title') }}</h5>
        </div>
        @can('course_create')
        <div class="">
            <a href="{{ route('admin.feedback.feedback-question-multiple') }}"
                class="btn btn-primary">@lang('strings.backend.general.app_add_new')</a>

        </div>
        @endcan

    </div>
    <div class="card" style="border: none;">
        <div class="card-body">
            <div class="">

                <table id="myTable" class="table custom-teacher-table table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('user_feedback.feedback_questions.id') }}</th>
                            <th>{{ __('user_feedback.feedback_questions.question_text') }}</th>
                            <th>{{ __('user_feedback.feedback_questions.question_type') }}</th>
                            <th style="text-align:center;">{{ __('user_feedback.feedback_questions.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($test_questions as $key => $value)
                        <tr>
                            <td>{{ $value->id }}</td>
                            <td>{!! $value->question !!}</td>
                            <td>
                                @if ($value->question_type == 1)
                                {{ __('user_feedback.feedback_questions.single_choice') }}
                                @elseif ($value->question_type == 2)
                                {{ __('user_feedback.feedback_questions.multiple_choice') }}
                                @else
                                {{ __('user_feedback.feedback_questions.short_answer') }}
                                @endif
                            </td>
                            <td>
                                
                                <div class="action-pill">
                                    
                              
                                        <a title="{{ __('user_feedback.feedback_questions.edit') }}" class="" href="{{ route('admin.feedback_question.edit', ['id' => $value->id]) }}">
                                            <i class="fa fa-edit" aria-hidden="true"></i>
                                        </a>
                                        <a title="{{ __('user_feedback.feedback_questions.delete') }}" class="" href="#" onclick="delete_client('{{ $value->id }}')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                               
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>


                </table>
            </div>
        </div>
    </div>
</div>



@endsection

@push('after-scripts')
<!-- <script>
        $(document).ready(function() {



            var route = '{{ route('admin.feedback_question.get_data') }}';

            @if (request('show_deleted') == 1)
                route = '{{ route('admin.feedback_question.get_data', ['show_deleted' => 1]) }}';
            @endif

            var table = $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: 'lfBrtip<"actions">',
                buttons: [{
                        extend: 'csv',
                        exportOptions: {
                            columns: [1, 2, 3]
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: [1, 2, 3],
                        }
                    },
                    'colvis'
                ],
                ajax: route,
                columns: [
                    @if (request('show_deleted') != 1)
                        {
                            "data": function(data) {
                                return '<input type="checkbox" class="single" name="id[]" value="' +
                                    data.id + '" />';
                            },
                            "orderable": false,
                            "searchable": false,
                            "name": "id"
                        },
                    @endif
                    // {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable:false},
                    {
                        data: "id",
                        name: 'id'
                    },
                    {
                        data: "question",
                        name: 'question'
                    },
                    {
                        data: "question_type",
                        name: 'question_type'
                    },
                    {
                        data: "actions",
                        name: 'actions'
                    }
                ],
                @if (request('show_deleted') != 1)
                    columnDefs: [{
                            "width": "5%",
                            "targets": 0
                        },
                        {
                            "className": "text-center",
                            "targets": [0]
                        }
                    ],
                @endif

                createdRow: function(row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
                language: {
                    url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{ $locale_full_name }}.json",
                    buttons: {
                        colvis: '{{ trans('datatable.colvis') }}',
                        pdf: '{{ trans('datatable.pdf') }}',
                        csv: '{{ trans('datatable.csv') }}',
                    }
                }

            });
            @if (auth()->user()->isAdmin())
                $('.actions').html('<a href="' + '{{ route('admin.teachers.mass_destroy') }}' +
                    '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>'
                );
            @endif



        });
    </script> -->

<script>
    $(document).ready(function() {
        $('#myTable').dataTable({
            "paginate": true,
            "sort": true,
            "language": {
                "emptyTable": "{{ __('user_feedback.feedback_questions.no_data_available') }}",
                "lengthMenu": "{{ trans('datatable.length_menu') }}",
                search: ""
                //                 paginate: {
                //     previous: '<i class="fa fa-angle-left"></i>',
                //     next: '<i class="fa fa-angle-right"></i>'
                // },
            },
            "order": [
                [0, "desc"]
            ],
            dom: "<'table-controls'lfB>" +
                "<'table-responsive't>" +
                "<'d-flex justify-content-between align-items-center mt-3'ip><'actions'>",
            buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download icon-styles"></i>',
                    className: '',
                    buttons: [{
                            extend: 'csv',
                            text: '{{ trans('datatable.csv') }}',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'pdf',
                            text: '{{ trans('datatable.pdf') }}',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5]
                            }
                        }
                    ]
                },
                {
                    extend: 'colvis',
                    text: '<i class="fa fa-eye icon-styles" aria-hidden="" ></i>',
                },
            ],
            // buttons: [{
            //         extend: 'csv',
            //         exportOptions: {
            //             columns: [1, 2, 3, 4]
            //         }
            //     },
            //     {
            //         extend: 'pdf',
            //         exportOptions: {
            //             columns: [1, 2, 3, 4]
            //         }
            //     },
            //     'colvis'
            // ],
            initComplete: function() {
                let $searchInput = $('#myTable_filter input[type="search"]');
                $searchInput
                    .addClass('custom-search')
                    .wrap('<div class="search-wrapper position-relative d-inline-block"></div>')
                    .after('<i class="fa fa-search search-icon"></i>');

                $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
            },


        });
    });
</script>

<script>
    function delete_client(id) {
        $.ajax({
            type: 'post',
            url: "{{ route('admin.feedback.feedback-question-multiple-delete') }}",
            data: ({
                id: id,
                _token: "{{ csrf_token() }}"
            }),
            success: function(response) {
                window.location.replace("{{ route('admin.feedback_question.index') }}");
            },
            error: function(error) {
                console.log(error);
            }
        })

    }
</script>
@endpush