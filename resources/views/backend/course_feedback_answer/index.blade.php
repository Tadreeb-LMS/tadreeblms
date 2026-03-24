@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')
@section('title', __('User Feedback Answers') . ' | ' . app_name())
@push('after-styles')
<style>
    .feedback-filters {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .feedback-filters-title {
        font-size: 15px;
        font-weight: 700;
        color: #1f2937;
    }

    .feedback-filters .control-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #4b5563;
        margin-bottom: 6px;
    }

    .feedback-filters .form-control,
    .feedback-filters .select2-container--default .select2-selection--single,
    .feedback-filters .select2-container--default .select2-selection--multiple {
        border: 1px solid #cfd8e3;
        border-radius: 10px;
        min-height: 42px;
    }

    .feedback-filters .select2-container--default .select2-selection--multiple {
        padding: 4px 8px;
    }

    .feedback-filters .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: #e8f1ff;
        border: 1px solid #b7d2ff;
        color: #1d4ed8;
        border-radius: 999px;
        padding: 2px 8px;
        font-size: 12px;
        margin-top: 3px;
    }

    .feedback-filters .form-control:focus,
    .feedback-filters .select2-container--default.select2-container--focus .select2-selection--single,
    .feedback-filters .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
    }

    .btn-reset-filters {
        background: #C1902D;
        border: 1px solid #C1902D;
        color: #fff;
        border-radius: 999px;
        font-weight: 600;
        padding: 0.42rem 1rem;
    }

    .btn-reset-filters:hover,
    .btn-reset-filters:focus {
        background: #A67921;
        border-color: #9C701E;
        color: #fff;
    }

    .btn-export-feedback {
        background: #1f7a45;
        border: 1px solid #1f7a45;
        color: #fff;
        border-radius: 999px;
        font-weight: 600;
        padding: 0.42rem 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        line-height: 1.2;
        white-space: nowrap;
    }

    .btn-export-feedback:hover,
    .btn-export-feedback:focus {
        background: #186338;
        border-color: #155a33;
        color: #fff;
    }

    .btn-export-feedback-icon {
        width: 18px;
        height: 18px;
        display: block;
        flex-shrink: 0;
    }

    .feedback-filter-actions {
        gap: 10px;
    }
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
                      <div class="feedback-filters">
                          <div class="d-flex align-items-center mb-3">
                              <h5 class="feedback-filters-title mb-0">Filters</h5>
                          </div>

                          <div class="row mb-2">
                              <div class="col-12 col-lg-4 form-group">
                                  <label for="filter_course_ids" class="control-label">Courses</label>
                                  <select name="filter_course_ids[]" id="filter_course_ids" class="form-control custom-select-box js-example-placeholder-courses select2" multiple>
                                      @foreach($courses as $courseId => $courseName)
                                          <option value="{{ $courseId }}">{{ $courseName }}</option>
                                      @endforeach
                                  </select>
                              </div>

                              <div class="col-12 col-lg-4 form-group">
                                  <label for="filter_user_ids" class="control-label">Users</label>
                                  <select name="filter_user_ids[]" id="filter_user_ids" class="form-control custom-select-box js-example-placeholder-multiple select2" multiple>
                                      @foreach($users as $userId => $userName)
                                          <option value="{{ $userId }}">{{ $userName }}</option>
                                      @endforeach
                                  </select>
                              </div>

                              <div class="col-6 col-lg-2 form-group">
                                  <label for="filter_date_from" class="control-label">From Date</label>
                                  <input type="date" id="filter_date_from" class="form-control" autocomplete="off">
                              </div>

                              <div class="col-6 col-lg-2 form-group">
                                  <label for="filter_date_to" class="control-label">To Date</label>
                                  <input type="date" id="filter_date_to" class="form-control" autocomplete="off">
                              </div>
                          </div>

                          <div class="row mb-0">
                              <div class="col-12 d-flex justify-content-end feedback-filter-actions">
                                  <a href="{{ route('admin.user-feedback-answers.export') }}" id="export-feedback-answers" class="btn btn-export-feedback">
                                      <svg class="btn-export-feedback-icon" viewBox="0 0 20 20" aria-hidden="true" focusable="false" fill="none">
                                          <path d="M7.25 2.25h5.1L16 5.9v10.6a1.25 1.25 0 0 1-1.25 1.25h-7.5A1.25 1.25 0 0 1 6 16.5V3.5a1.25 1.25 0 0 1 1.25-1.25Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                          <path d="M12.25 2.5V5.75H15.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                      </svg>
                                      <span>Download as Excel</span>
                                  </a>
                                  <button type="button" id="reset-filters" class="btn btn-reset-filters">Reset Filters</button>
                              </div>
                          </div>
                      </div>

                      <div class="">
                          <table id="myTable"
                              class="table dt-select custom-teacher-table table-striped @can('lesson_delete') @if (request('show_deleted') != 1) dt-select @endif @endcan">
                              <thead>
                                  <tr>
                                      <th>S. No.</th>
                                      <th>User Name</th>
                                      <th>Course Name</th>
                                      <th>Submitted On</th>
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
            let isResettingFilters = false;
            const exportBaseUrl = '{{ route("admin.user-feedback-answers.export") }}';

            const appendArrayParams = function (params, key, values) {
                if (!Array.isArray(values)) {
                    return;
                }

                values.filter(Boolean).forEach(function (value) {
                    params.append(key + '[]', value);
                });
            };

            const updateExportUrl = function () {
                const params = new URLSearchParams();
                const searchValue = $('#myTable_filter input[type="search"]').val();

                appendArrayParams(params, 'course_ids', $('#filter_course_ids').val() || []);
                appendArrayParams(params, 'user_ids', $('#filter_user_ids').val() || []);

                if ($('#filter_date_from').val()) {
                    params.set('date_from', $('#filter_date_from').val());
                }

                if ($('#filter_date_to').val()) {
                    params.set('date_to', $('#filter_date_to').val());
                }

                if (searchValue) {
                    params.set('search', searchValue);
                }

                const queryString = params.toString();

                $('#export-feedback-answers').attr('href', queryString ? exportBaseUrl + '?' + queryString : exportBaseUrl);
            };

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
                        d.course_ids = $('#filter_course_ids').val();
                        d.user_ids = $('#filter_user_ids').val();
                        d.date_from = $('#filter_date_from').val();
                        d.date_to = $('#filter_date_to').val();
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
                        data: "submitted_on",
                        name: 'submitted_on'
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

                $searchInput.on('input', function () {
                    updateExportUrl();
                });

                $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
                updateExportUrl();
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

            $(".js-example-placeholder-courses").select2({
                placeholder: "Select courses",
                allowClear: true,
                width: "100%"
            });

            $(".js-example-placeholder-multiple").select2({
                placeholder: "Select users",
                allowClear: true,
                width: "100%"
            });

            $('#filter_course_ids, #filter_user_ids, #filter_date_from, #filter_date_to').on('change', function () {
                if (isResettingFilters) {
                    return;
                }

                updateExportUrl();
                dtTable.draw();
            });

            $(document).on('click', '#reset-filters', function () {
                isResettingFilters = true;

                $('#filter_course_ids').val(null).trigger('change');
                $('#filter_user_ids').val(null).trigger('change');
                $('#filter_date_from').val('');
                $('#filter_date_to').val('');

                isResettingFilters = false;
                updateExportUrl();
                dtTable.draw();
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
