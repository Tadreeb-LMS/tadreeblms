@extends('backend.layouts.app')
@section('title', __('Create Learning Pathway') . ' | ' . app_name())
@push('after-styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet" />
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
    <style>
        .select2-container .select2-search--inline .select2-search__field {
    box-sizing: border-box;
    border: none;
    font-size: 100%;
    margin-top: 5px;
    padding-left: 8px;
}

.select2-container--default .select2-selection--multiple:focus {
    outline: none !important;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important;
    border-color: #007bff !important;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
     outline: none !important;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important;
    border-color: #007bff !important;
}
.select2-container--default .select2-selection--multiple{
    border: 1px solid #ccc !important;
}

    </style>
@endpush
@section('content')
<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4 >@lang('Create Learning Pathway')</h4>
    <div >
        <a href="{{ route('admin.learning-pathways.index') }}" class="add-btn">@lang('View Learning Pathways')</a>
    </div>
</div>
    <div class="card">

        <div class="card-body">
            <form class="ajax" action="{{ route("admin.learning-pathways.store") }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="course_with_order">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="required">@lang('Title')</label>
                            <input class="form-control" name="title" type="text" placeholder="@lang('Title')">
                        </div>
                    </div>
                    <div class="col-md-6">
                        
                            <div class="required">@lang('Add Courses to Pathway')</div>
                            <!-- <select class="select2" name="course_id" multiple>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                                @endforeach
                            </select> -->
                        
                        <div class="custom-select-wrapper mt-2">
                            <select name="course_id" class="form-control custom-select-box select2 js-example-questions-placeholder-multiple" multiple required>
                            @foreach ($courses as $course)
                                                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                                                    @endforeach
                            </select>
                            <span class="custom-select-icon">
                                <i class="fa fa-chevron-down"></i>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label>@lang('Description')</label>
                            <textarea class="form-control" cols="30" rows="10" name="description"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>@lang('Pathway courses (Drag and drop to reorder)')</label>
                        <div id="pathwayCourses" class="list-group col">
                            <div class="list-group-item" id="pathwayCourses-placeholder">Selected courses will appear here
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" name="in_sequence">
                            <label class="form-check-label" for="in_sequence">
                                @lang('In Sequence')
                            </label>
                        </div>
                    </div>
                    {{-- <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label>@lang('Assign Users to Pathway')</label>
                            <select class="select2" name="user_ids[]" multiple>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->full_name }}({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div> --}}
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-info text-uppercase px-4" type="submit">
                        @lang('Create')
                    </button>
                </div>
            </form>
        </div>
        <input type="hidden" id="course_index" value="{{ route('admin.courses.index') }}">
        <input type="hidden" id="lesson" value="{{ route('admin.lessons.create') }}">
        <input type="hidden" id="new-assisment" value="{{ route('admin.assessment_accounts.new-assisment') }}">
    </div>
@stop

@push('after-scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.6/Sortable.min.js"></script>
    <script src="{{ asset('/js/helpers/form-submit.js') }}"></script>
    <script>
        new Sortable(pathwayCourses, {
            animation: 150,
            onEnd: function(evt) {
                setCoursePositionFormData();
            }
        });

        $('[name="course_id"]').on('select2:select', function(e) {
            const selectedData = e.params.data; // The selected item data

            if (!$('#pathwayCourses-placeholder').hasClass('d-none')) {
                $('#pathwayCourses-placeholder').addClass('d-none');
            }

            const newItem = $('<div></div>')
                .addClass('list-group-item')
                .text(selectedData.text) // Display the text
                .attr('data-value', selectedData.id); // Store the value for later removal

            // Append the item to the sortable container
            $('#pathwayCourses').append(newItem);

            setCoursePositionFormData();
        });

        $('[name="course_id"]').on('select2:unselect', function(e) {
            const removed = e.params.data;
            $(`#pathwayCourses .list-group-item[data-value="${removed.id}"]`).remove();

            if ($('#pathwayCourses .list-group-item').length == 1) {
                $('#pathwayCourses-placeholder').removeClass('d-none');
            }

            setCoursePositionFormData();
        });

        function setCoursePositionFormData() {
            const order = [];
            $('#pathwayCourses .list-group-item:not(#pathwayCourses-placeholder)').each(function() {
                const value = $(this).attr('data-value'); // Get the data-value attribute
                order.push(value);
            });
            
            $('[name="course_with_order"]').val(JSON.stringify(order))
        }
    </script>
@endpush
