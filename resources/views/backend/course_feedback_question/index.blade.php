@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')
@section('title', __('labels.backend.lessons.title') . ' | ' . app_name())
@push('after-styles')
 <style>

.dataTables_paginate.paging_simple_numbers{
width: 58% !important;
}


  #custom-loader .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
    }
     .dropdown-item {
    position: relative;
    padding: 10px 20px;
    border-bottom: none !important;
}

    </style>
@endpush
@section('content')

<div class="container-fluid">
    <div class="grow pb-3">
                <h4 class="text-20">@lang('Course Feedback Questions')</h4>
              </div>
               <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-12 col-lg-6 form-group ">
                    {!! Form::label('course_id', trans('labels.backend.lessons.fields.course'), ['class' => 'control-label']) !!}
                    <div class="custom-select-wrapper">

                        {!! Form::select('course_id', $courses, request('course_id') ? request('course_id') : old('course_id'), [
                            'class' => 'form-control custom-select-box js-example-placeholder-single select2',
                            'id' => 'course_id',
                            
                        ]) !!}
                        <span class="custom-select-icon">
            <i class="fa fa-chevron-down"></i>
        </span>
                    </div>
                </div>
            </div>

            <!-- <div id="custom-loader" style="display: none;">
    <div class="loader-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div> -->

           <table id="myTable" class="table custom-teacher-table table-striped @can('lesson_delete') @if (request('show_deleted') != 1) dt-select @endif @endcan">
                                 <thead>
                        <tr>
                            <th>S. No.</th>
                            <th>Course Name</th>
                            <th>Question</th>
                            <th class="text-center">Actions</th>
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
    <script src="{{ asset('js/modal/confirm-modal.js') }}"></script>
    <script>
        $(document).ready(function() {
            let course_id;
            const dtTable = $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                dom:"<'table-controls'lf>" +
                     "<'table-responsive't>" +
                     "<'d-flex justify-content-between align-items-center flex-wrap mt-3'ip><'actions'>",
                retrieve: true,
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
                ajax: {
                    url: '{{ route("admin.course-feedback-questions.index") }}',
                    data: function(d) {
                        d.course_id = course_id; // Pass the course_id parameter to the server
                    },
        //             beforeSend: function () {
        //     $('#custom-loader').show();
        // },
        // complete: function () {
        //     $('#custom-loader').hide();
        // }
                },
                language:{search:""
    //                               paginate: {
    //     previous: '<i class="fa fa-angle-left"></i>',
    //     next: '<i class="fa fa-angle-right"></i>'
    // },
       
                },
                columns: [{
                        data: "DT_RowIndex",
                        name: 'DT_RowIndex',
                        searchable: false,
                        orderable: false
                    }, {
                        data: "course_name",
                        name: 'course_name'
                    },
                    {
                        data: "question",
                        name: 'question'
                    },
                    {
                        data: "actions",
                    }
                ],
                initComplete: function () {
                    let $searchInput = $('#myTable_filter input[type="search"]');
                $searchInput
                    .addClass('custom-search')
                    .wrap('<div class="search-wrapper position-relative d-inline-block"></div>')
                    .after('<i class="fa fa-search search-icon"></i>');

                $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
                },
//                  drawCallback: function () {
//     $('.dataTables_paginate .paginate_button.previous, .dataTables_paginate .paginate_button.next').css({
//         'border-radius': '20px',
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
            });

            $(document).on('change', '#course_id', function(e) {
                course_id = $(this).val();
                dtTable.draw();
            });

            $(document).on('hidden.bs.modal', '#confirmModal', function(e) {
                dtTable.draw();
            });

            


        });
    </script>
@endpush
