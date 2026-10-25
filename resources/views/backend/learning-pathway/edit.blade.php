@extends('backend.layouts.app')
@section('title', __('Edit Learning Pathway') . ' | ' . app_name())
@push('after-styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet" />

    <style>
        #pathwayCourses .pathway-course-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            cursor: grab;
        }

        #pathwayCourses .pathway-course-title {
            flex: 1;
        }

        #pathwayCourses .pathway-course-remove {
            border: 0;
            background: transparent;
            color: #dc3545;
            font-size: 22px;
            font-weight: 600;
            line-height: 1;
            padding: 0 4px;
            cursor: pointer;
            flex-shrink: 0;
        }

        #pathwayCourses .pathway-course-remove:hover {
            color: #a71d2a;
        }
    </style>
@endpush
@php 
    $oldCourseIds = $lp->learningPathwayCoursesOrdered->pluck('course_id')->toArray();
    // dd($oldCourseIds);
@endphp
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left">@lang('Edit Learning Pathway')</h3>
            <div class="float-right">
                <a href="{{ route('admin.learning-pathways.index') }}" class="btn btn-success">@lang('View Learning Pathways')</a>
            </div>
        </div>

        <div class="card-body">
            <form class="ajax" action="/user/learning-pathways/{{ $lp->id }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="course_with_order"
                    value="{{ json_encode($lp->learningPathwayCoursesOrdered->pluck('course_id')->toArray()) }}">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="required">@lang('Title')</label>
                            <input class="form-control" name="title" type="text" placeholder="@lang('Title')"
                                value="{{ $lp->title }}">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="required">@lang('Add Courses to Pathway')</label>
                            <select class="select2" name="course_id[]" id="course_id" multiple>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}"
                                        @if(in_array($course->id, $oldCourseIds)) selected data-locked="true" @endif>
                                        {{ $course->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label>@lang('Description')</label>
                            <textarea class="form-control" cols="30" rows="10" name="description">{{ $lp->description }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>@lang('Pathway courses (Drag and drop to reorder)')</label>
                        <div id="pathwayCourses" class="list-group col">
                            <div class="list-group-item d-none" id="pathwayCourses-placeholder">Selected courses will appear
                                here
                            </div>
                            @foreach ($lp->learningPathwayCoursesOrdered as $lpc)
                                <div class="list-group-item pathway-course-item" data-value="{{ $lpc->course_id }}">
                                    <span class="pathway-course-title">
                                        {{ $lpc->course->title }}
                                    </span>

                                    <button
                                        type="button"
                                        class="pathway-course-remove"
                                        aria-label="Remove {{ $lpc->course->title }}"
                                    >
                                        &times;
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" @if($lp->in_sequence) checked @endif type="checkbox" value="1" name="in_sequence">
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
                        @lang('Update')
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.6/Sortable.min.js"></script>
    <script src="/js/helpers/form-submit.js"></script>
    <script>
        $('[id="course_id"]').on('select2:select', function(e) {
            const selectedData = e.params.data;

            if (!$('#pathwayCourses-placeholder').hasClass('d-none')) {
                $('#pathwayCourses-placeholder').addClass('d-none');
            }

            const newItem = $('<div></div>')
                .addClass('list-group-item pathway-course-item')
                .attr('data-value', selectedData.id);

            $('<span></span>')
                .addClass('pathway-course-title')
                .text(selectedData.text)
                .appendTo(newItem);

            $('<button></button>')
                .attr({
                    type: 'button',
                    class: 'pathway-course-remove',
                    'aria-label': 'Remove ' + selectedData.text
                })
                .html('&times;')
                .appendTo(newItem);

            $('#pathwayCourses').append(newItem);

            setCoursePositionFormData();
        });

        $('[id="course_id"]').on('select2:unselect', function(e) {
            const removed = e.params.data;

            $(`#pathwayCourses .pathway-course-item[data-value="${removed.id}"]`).remove();

            if ($('#pathwayCourses .pathway-course-item').length === 0) {
                $('#pathwayCourses-placeholder').removeClass('d-none');
            }

            setCoursePositionFormData();
        });

        $('#pathwayCourses').on('pointerdown', '.pathway-course-remove', function(e) {
            e.stopPropagation();
        });

        $('#pathwayCourses').on('click', '.pathway-course-remove', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $item = $(this).closest('.pathway-course-item');
            const courseId = String($item.attr('data-value'));
            const $courseSelect = $('[id="course_id"]');

            const selectedValues = $courseSelect.val() || [];

            const updatedValues = selectedValues.filter(function(value) {
                return String(value) !== courseId;
            });

            $courseSelect.val(updatedValues).trigger('change');

            $item.remove();

            if ($('#pathwayCourses .pathway-course-item').length === 0) {
                $('#pathwayCourses-placeholder').removeClass('d-none');
            }

            setCoursePositionFormData();
        });

        function setCoursePositionFormData() {
            const order = [];

            $('#pathwayCourses .pathway-course-item').each(function() {
                const value = $(this).attr('data-value');
                order.push(value);
            });

            $('[name="course_with_order"]').val(JSON.stringify(order));
        }
    </script>

    <script>
        $(document).ready(function () {
            const $select = $('#course_id');

            $select.select2();

            // Prevent deselecting locked options
            $select.on('select2:unselecting', function (e) {
                const isLocked = $(e.params.args.data.element).data('locked');
                if (isLocked) {
                    e.preventDefault(); // Stop removal
                }
            });
        });
    </script>
@endpush
