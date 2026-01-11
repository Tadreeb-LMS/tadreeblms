@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')
@section('title', __('User Feedback Answers') . ' | ' . app_name())
@push('after-styles')
 <style>
      
    </style>
@endpush

@section('content')

<div>
    <div class="grow pb-3">
                <h4 class="text-20"> @lang('User Feedback Answer')</h4>
              </div>
              <div class="card">
                   
                  <!-- <div class="card-header">
                      <h3 class="page-title d-inline">@lang('User Feedback Answer')</h3>
                  </div> -->
                  <div class="card-body">
                      <div class="">
                          <table id="myTable"
                              class="table dt-select custom-teacher-table table-striped @can('lesson_delete') @if (request('show_deleted') != 1) dt-select @endif @endcan">
                              <thead>
                                  <tr>
                                      <th>S. No.</th>
                                      <th>User Name</th>
                                      <th>Course Name</th>
                                      <th>Detail</th>
                                  </tr>
                              </thead>
                              <tbody>
                              </tbody>
                          </table>
              
                      </div>
              
                  </div>
              </div>
</div>


<div class="modal fade" id="feedbackModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Feedback Detail</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <p id="feedback-content">Loading...</p>
        </div>
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
                retrieve: true,
                dom: "<'table-controls'lf>" +
                     "<'table-responsive't>" +
                     "<'d-flex justify-content-between align-items-center mt-3'ip><'actions'>",
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
                    url: '{{ route("admin.user-feedback-answers.index") }}',
                    beforeSend: function() {
                        $("#loader").removeClass("d-none");
                    },
                    complete: function() {
                        $("#loader").addClass("d-none");
                    },
                    data: function(d) {
                        d.course_id = course_id; // Pass the course_id parameter to the server
                    }
                },
                columns: [{
                        data: "DT_RowIndex",
                        name: 'DT_RowIndex',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: "user_name",
                        name: 'user_name'
                    },
                    {
                        data: "course_name",
                        name: 'course_name'
                    },
                    {
                        data: "question_answers",
                        name: 'question_answers'
                    }
                ],
                language:{
                    search:"",
    //                               paginate: {
    //     previous: '<i class="fa fa-angle-left"></i>',
    //     next: '<i class="fa fa-angle-right"></i>'
    // },
       
                },
  createdRow: function (row, data, dataIndex) {
    $(row).find('td table').addClass('inner-description-table');
},
                initComplete: function () {
                   let $searchInput = $('#myTable_filter input[type="search"]');
                $searchInput
                    .addClass('custom-search')
                    .wrap('<div class="search-wrapper position-relative d-inline-block"></div>')
                    .after('<i class="fa fa-search search-icon"></i>');

                $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
                },
                 drawCallback: function () {
    $('.dataTables_paginate .paginate_button.previous, .dataTables_paginate .paginate_button.next').css({
        'border-radius': '20px',
        'padding': '6px 15px',
        'font-weight': '500',
        
        'color': 'white',
        'border': 'none',
        'margin': '0 5px'
    });
    $('.dataTables_paginate .paginate_button').not('.previous, .next').css({
        'background-color': '#f0f0f0',
        'color': '#333',
        'border': '1px solid #ccc',
        'border-radius': '7px',
        'padding': '6px 12px',
        'margin': '0 4px',
        'font-weight': '500'
    });

    // Style current/active page
    $('.dataTables_paginate .paginate_button.current').css({
        'background-color': '#0d6efd',
        'color': 'white',
        'border': 'none',
        'font-weight': 'bold'
    });
},
            });

        });
    </script>

    <script>
        
        $(document).on("click", ".feedback-detail", function (e) {
            e.preventDefault(); // prevent link from reloading page
            $("#feedback-content").html("loading ...");
            // get data-id attribute
            var id = $(this).data("id"); // or $(this).attr("data-id")
            var url = "{{ route('admin.feedback.detail', ':id') }}";
            url = url.replace(':id', id);
            // show modal
            $("#feedbackModal").modal("show");

            // Example: load content via AJAX
            $.ajax({
                url:  url,
                type: "GET",
                success: function (response) {
                    $("#feedback-content").html(response.html);
                },
                error: function () {
                    $("#feedback-content").html("Error loading details.");
                }
            });
        });

    </script>
@endpush
