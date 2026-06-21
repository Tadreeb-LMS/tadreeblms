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
                                    <a href="javascript:void(0)"
                                        class="js-delete-question"
                                        data-url="{{ route('admin.questions.destroy', $value->id) }}">
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
<script>
    $(document).on('click', '.js-delete-question', function(e) {
        e.preventDefault();

        let url = $(this).data('url');

        Swal.fire({
            title: 'Are you sure?',
            text: "This record will be deleted permanently.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Record deleted successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Something went wrong.'
                        });
                    }
                });
            }
        });
    });
</script>
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
        const exportColumns = [
            @json(__('user_feedback.feedback_questions.id')),
            @json(__('user_feedback.feedback_questions.question_text')),
            @json(__('user_feedback.feedback_questions.question_type'))
        ];
        const exportFilename = 'feedback-questions';

        function stripHtml(value) {
            return $('<div>').html(value || '').text().trim();
        }

        function getExportRows(table) {
            return table.rows({ search: 'applied' }).data().toArray().map(function(row) {
                return [
                    stripHtml(row[0]),
                    stripHtml(row[1]),
                    stripHtml(row[2])
                ];
            });
        }

        function downloadCsv(rows) {
            const csvRows = [exportColumns].concat(rows).map(function(row) {
                return row.map(function(value) {
                    return '"' + String(value).replace(/"/g, '""') + '"';
                }).join(',');
            });
            const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = url;
            link.download = exportFilename + '.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }

        function downloadPdf(rows) {
            if (typeof pdfMake === 'undefined') {
                alert('PDF export is unavailable. Please refresh and try again.');
                return;
            }

            pdfMake.createPdf({
                pageOrientation: 'landscape',
                content: [
                    { text: @json(__('user_feedback.feedback_questions.title')), style: 'header' },
                    {
                        table: {
                            headerRows: 1,
                            widths: ['auto', '*', 'auto'],
                            body: [exportColumns].concat(rows)
                        }
                    }
                ],
                styles: {
                    header: {
                        fontSize: 14,
                        bold: true,
                        margin: [0, 0, 0, 10]
                    }
                }
            }).download(exportFilename + '.pdf');
        }

        function exportFeedbackQuestions(table, type) {
            const rows = getExportRows(table);

            if (!rows.length) {
                alert('No feedback questions available to export.');
                return;
            }

            if (type === 'csv') {
                downloadCsv(rows);
                return;
            }

            downloadPdf(rows);
        }

        const table = $('#myTable').DataTable({
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
                            text: '{{ trans('datatable.csv') }}',
                            action: function() {
                                exportFeedbackQuestions(table, 'csv');
                            }
                        },
                        {
                            text: '{{ trans('datatable.pdf') }}',
                            action: function() {
                                exportFeedbackQuestions(table, 'pdf');
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

$(document).on('click', '.js-delete-question', function (e) {

    e.preventDefault();

    let url = $(this).data('url');

    Swal.fire({
        title: 'Are you sure?',
        text: 'This record will be deleted permanently.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },

                success: function (response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Question deleted successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                },

                error: function () {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong.'
                    });
                }
            });
        }
    });

});

</script>
@endpush
