@extends('backend.layouts.app')
@section('title', __('labels.backend.lessons.title') . ' | ' . app_name())

@push('after-styles')
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/jquery.datetimepicker.min.css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.css') }}">
    <style>
        .lesson-box {
            border: 1px solid #e4e6ef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            background: #fff;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
            position: relative;
        }

        .remove_less_slug {
            position: absolute;
            top: -12px;
            right: -12px;
            background: #fff;
            border-radius: 50%;
            color: red;
            font-size: 10px;
            padding: 2px;
            font-weight: bold;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            z-index: 10;
            line-height: 1;
        }

        span.loading {
            font-style: italic;
            color: green;
            display: inline;
        }

        .select2-container--default .select2-selection--single {
            height: 35px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 35px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 35px;
        }

        .bootstrap-tagsinput {
            width: 100% !important;
            display: inline-block;
        }

        .bootstrap-tagsinput .tag {
            line-height: 1;
            margin-right: 2px;
            background-color: #2f353a;
            color: white;
            padding: 3px;
            border-radius: 3px;
        }

        .create_done {
            padding: 10px 40px;
            font-size: 16px;
            font-weight: 500;
            background: #20a8d8;
            border: none;
            outline: none;
            float: right;
            margin: 0 15px 0 0;
            color: white;
        }

        .create_done.next {
            background: #4dbd74;
        }

        .multiple_lesson {
            margin-left: 17px;
        }

        .form-control {
            height: auto;
        }

        @media screen and (max-width: 768px) {
            .create_done {
                padding: 5px 20px;
            }

            .multiple_lesson {
                margin-left: 0px;
            }
        }
    </style>
@endpush

@section('content')
    <form method="POST" id="addLesson" enctype="multipart/form-data" autocomplete="off">
        @csrf()

        @if ($courses_all)
            <input type="hidden" name="category_id" value="{{ $courses_all }}" id="category_id">
        @endif

        <div class="pb-3 d-flex justify-content-between align-items-center addcourseheader">
            <h4>@lang('labels.backend.lessons.create')</h4>
            <div>
                <a href="{{ route('admin.courses.index') }}" class="btn add-btn">@lang('labels.backend.lessons.view')</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="lesson-template">
                    <div class="position-relative lesson-box">
                        <i class="fa fa-times remove_less_slug"
                            onclick="removeLesslug(this)"
                            style="position:absolute; top:-10px; right:-10px; color:red; font-size:18px; cursor:pointer; display:none;"
                            title="Remove Lesson"></i>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div for="course_id" class="form-control-label">
                                        {{ trans('labels.backend.lessons.fields.course') }}
                                    </div>
                                    <div class="mt-2 custom-select-wrapper">
                                        <select id="course_id" name="course_id"
                                            class="form-control custom-select-box course_id select2">
                                            @foreach ($courses as $key => $course)
                                                <option value="{{ $key }}"
                                                    {{ old('course_id') == $key || request('course_id') == $key ? 'selected' : '' }}>
                                                    {{ $course }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="custom-select-icon">
                                            <i class="fa fa-chevron-down"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div for="lesson_image" class="control-label mb-2">
                                        {{ trans('labels.backend.lessons.fields.lesson_image') }}
                                        {{ trans('labels.backend.lessons.max_file_size') }}
                                    </div>
                                    <div class="custom-file-upload-wrapper">
                                        <input type="file" name="lesson_image[]" class="custom-file-input">
                                        <label class="custom-file-label">
                                            <i class="fa fa-upload mr-1"></i> Choose a file
                                        </label>
                                    </div>
                                </div>

                                <div class="ltitle">
                                    <label for="title" class="control-label">
                                        {{ trans('labels.backend.lessons.fields.title') }} *
                                    </label>
                                    <input type="text" name="title[]" value="{{ old('title') }}" class="form-control"
                                        placeholder="{{ trans('labels.backend.lessons.fields.title') }}" required />
                                </div>

                                <div class="shortext">
                                    <label for="short_text" class="control-label">
                                        {{ trans('labels.backend.lessons.fields.short_text') }}
                                    </label>
                                    <textarea name="short_text[]" class="form-control"
                                        placeholder="{{ trans('labels.backend.lessons.short_description_placeholder') }}" style="height: 100px;">{{ old('short_text') }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group notextarea fillh180">
                                    <label for="full_text" class="control-label">
                                        {{ trans('labels.backend.lessons.fields.full_text') }}
                                    </label>
                                    <textarea name="full_text[]" class="form-control editor" placeholder="">{{ old('full_text') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-1">
                            <div class="col-md-4">
                                <div class="form-group ">
                                    <div for="downloadable_files" class="control-label mb-2">
                                        {{ trans('labels.backend.lessons.fields.downloadable_files') }}
                                        {{ trans('labels.backend.lessons.max_file_size') }}
                                    </div>

                                    <div class="custom-file-upload-wrapper">
                                        <input type="file" name="downloadable_files_1[]" class="custom-file-input">
                                        <label class="custom-file-label">
                                            <i class="fa fa-upload mr-1"></i> Choose a file
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group ">
                                    <div for="add_pdf" class="control-label mb-2">
                                        {{ trans('labels.backend.lessons.fields.add_pdf') }}
                                    </div>
                                    <div class="custom-file-upload-wrapper">
                                        <input type="file" name="add_pdf_1[]" class="custom-file-input">
                                        <label class="custom-file-label">
                                            <i class="fa fa-upload mr-1"></i> Choose a file
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group ">
                                    <div for="add_audio" class="control-label mb-2">
                                        {{ trans('labels.backend.lessons.fields.add_audio') }}
                                    </div>
                                    <div class="custom-file-upload-wrapper">
                                        <input type="file" name="add_audio_1[]" class="custom-file-input">
                                        <label class="custom-file-label">
                                            <i class="fa fa-upload mr-1"></i> Choose a file
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row addvideocol">
                            <div class="col-md-4 form-group parent_group mt-2">
                                <div class="videos-section">
                                    <h5>Lesson Videos</h5>

                                    <div class="videos-wrapper"></div>

                                    <button type="button" class="btn btn-primary mt-2 addVideo">
                                        Add Video
                                    </button>
                                </div>

                                <div class="video-template d-none">
                                    <div class="video-item card p-3 mb-3">
                                        <label>Video Title</label>
                                        <input type="text" name="videos[INDEX][title]" class="form-control" disabled>

                                        <label>Type</label>
                                        <select name="videos[INDEX][type]" class="form-control video-type" disabled>
                                            <option value="upload">Upload</option>
                                            <option value="youtube">YouTube</option>
                                            <option value="vimeo">Vimeo</option>
                                            <option value="embed">Embed</option>
                                        </select>

                                        <div class="video-url mt-2 d-none">
                                            <label>Video URL</label>
                                            <input type="text" name="videos[INDEX][url]"
                                                class="form-control video-url-input" disabled>
                                        </div>

                                        <div class="video-file mt-2 d-none">
                                            <label>Upload File</label>
                                            <input type="file" name="videos[INDEX][file]"
                                                class="form-control video-file-input" disabled>
                                        </div>

                                        <label class="mt-2">
                                            <input type="checkbox" name="videos[INDEX][is_preview]" value="1" disabled>
                                            Preview Video
                                        </label>

                                        <button type="button" class="removeVideo btn btn-danger btn-sm mt-2">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8 mt-2">
                                <p>@lang('labels.backend.lessons.video_guide')</p>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-4 col-sm-12">
                                <div for="duration" class="form-control-label mb-2">Duration</div>
                                <div>
                                    <input type="text" name="duration[]" class="form-control"
                                        placeholder="Duration [minutes]">
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12 start_date">
                                <div for="duration" class="form-control-label mb-2">Lesson Start Date</div>
                                <div>
                                    <input class="form-control" type="date" name="lesson_start_date[]"
                                        id="lesson_start_date">
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <div class="checkbox" style="margin-top: 37px;">
                                    <input type="hidden" name="published" value="0">
                                    <input type="checkbox" name="published" value="1" id="published"
                                        class="checkbox">
                                    <label for="published" class="checkbox control-label font-weight-bold">
                                        {{ trans('labels.backend.lessons.fields.published') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row"></div>
                <div class="mo_create"></div>

                <div class="btmbtns">
                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="button" name="addmorebtn" id="addmorebtn"
                                class="btn btn-outline-info">Add More Lesson</button>
                        </div>
                        <div>
                            <button type="submit" class="btn cancel-btn frm_submit" id="doneBtn">
                                Save As Draft
                            </button>
                            <button type="submit" class="btn add-btn frm_submit next" id="nextBtn">
                                Next
                            </button>

                            <span class="loading"></span>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="add_question_url" value="{{ route('admin.test_questions.create') }}">
            <input type="hidden" id="ass_index" value="{{ url('user/assignments/create?assis_new') }} ">
            <input type="hidden" id="lesson_index" value="{{ route('admin.lessons.index') }}">
            <input type="hidden" id="temp_id" name="temp_id" value="{{ $temp_id }}">
            <input type="hidden" name="btn_clicked" id="btn_clicked" />
        </div>
    </form>
@stop

@push('after-scripts')
<script src="{{ asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js"></script>

<script>
    function generateEditorId() {
        return 'editor_' + Date.now() + '_' + Math.floor(Math.random() * 10000);
    }

    function initEditors(container) {
        container.find('textarea.editor').each(function () {
            const $textarea = $(this);

            let id = $textarea.attr('id');
            if (!id || CKEDITOR.instances[id]) {
                id = generateEditorId();
                $textarea.attr('id', id);
            }

            if ($textarea.data('ckeditorInitialized')) {
                return;
            }

            if (CKEDITOR.instances[id]) {
                return;
            }

            CKEDITOR.replace(id);
            $textarea.data('ckeditorInitialized', true);
        });
    }

    function toggleVideoFields($videoItem) {
        const type = ($videoItem.find('.video-type').val() || '').toLowerCase();

        const $urlBox = $videoItem.find('.video-url');
        const $fileBox = $videoItem.find('.video-file');
        const $urlInput = $videoItem.find('.video-url-input');
        const $fileInput = $videoItem.find('.video-file-input');

        $urlBox.addClass('d-none');
        $fileBox.addClass('d-none');

        $urlInput.prop('required', false).prop('disabled', true);
        $fileInput.prop('required', false).prop('disabled', true);

        if (type === 'upload') {
            $fileBox.removeClass('d-none');
            $fileInput.prop('required', true).prop('disabled', false);
            $urlInput.val('');
        } else if (type === 'youtube' || type === 'vimeo' || type === 'embed') {
            $urlBox.removeClass('d-none');
            $urlInput.prop('required', true).prop('disabled', false);
            $fileInput.val('');
        }
    }

    window.videoIndex = window.videoIndex || 0;

    $(document).ready(function () {
        initEditors($(document));

        $(document).on('click', '.addVideo', function () {
            const $parent = $(this).closest('.parent_group');
            let template = $parent.find('.video-template').first().html();

            template = template.replace(/INDEX/g, videoIndex);

            const $newVideo = $(template);
            $newVideo.find('input, select, textarea').prop('disabled', false);

            $parent.find('.videos-wrapper').first().append($newVideo);

            toggleVideoFields($newVideo);
            videoIndex++;
        });

        $(document).on('change', '.video-type', function () {
            toggleVideoFields($(this).closest('.video-item'));
        });

        $(document).on('click', '.removeVideo', function () {
            $(this).closest('.video-item').remove();
        });

        $(document).on('change', '.custom-file-input', function (e) {
            const label = this.nextElementSibling;
            const fileName = e.target.files.length > 0 ? e.target.files[0].name : 'Choose a file';

            if (label) {
                label.innerHTML = '<i class="fa fa-upload mr-1"></i> ' + fileName;
            }
        });

        $(document).on('change', 'input[name="lesson_image[]"]', function () {
            const $this = $(this);

            $(this.files).each(function (key, value) {
                if (value.size > 50000000) {
                    alert('"' + value.name + '" exceeds limit of maximum file upload size (50MB)');
                    $this.val('');
                }
            });
        });

        $(document).on('change', '.course_id', function () {
            const $currentSelect = $(this);
            const $currentLesson = $currentSelect.closest('.lesson-box');

            $.ajax({
                url: "{{ route('lessons.course.check') }}",
                method: "GET",
                data: {
                    id: $currentSelect.val()
                },
                dataType: "json",
                success: function (data) {
                    if (data.success && data.category == 'Internal') {
                        $currentLesson.find('.start_date').hide();
                    } else {
                        $currentLesson.find('.start_date').show();
                    }
                }
            });
        });

        $('.videos-wrapper .video-item').each(function () {
            toggleVideoFields($(this));
        });
    });

    $('.frm_submit').on('click', function () {
        let clickedButtonId = $(this).attr('id');
        $('#btn_clicked').val(clickedButtonId);
    });

    $(document).on('submit', '#addLesson', function (e) {
        e.preventDefault();

        $('.loading').text('processing please wait...');
        $('#nextBtn,#doneBtn').prop('disabled', true);

        var form = $('#addLesson')[0];
        var data = new FormData(form);

        let url = '{{ route('admin.lessons.store') }}';
        let redirect_url_course = $("#lesson_index").val();
        let redirect_question_url = $("#add_question_url").val();
        let course_id = $(".course_id").first().val();

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            processData: false,
            contentType: false,

            success: function (res) {
                $('.loading').text('');

                let clicked = $('#btn_clicked').val();

                if (clicked === 'nextBtn') {
                    window.location.href = redirect_question_url + "/" + course_id + "/" + res.temp_id;
                }

                if (clicked === 'doneBtn') {
                    window.location.href = redirect_url_course;
                }
            },

            error: function (xhr) {
                $('.loading').text('');
                $('#nextBtn,#doneBtn').prop('disabled', false);

                console.log(xhr.responseText);
                alert('Something went wrong');
            }
        });
    });

    var i = 1;

    $("#dynamic-ar").on('click', function () {
        ++i;
        $("#dynamicAddRemove").append(
            '<tr>' +
                '<td><input type="text" name="addMoreInputFields[' + i + '][subject]" placeholder="Enter subject" class="form-control" /></td>' +
                '<td><button type="button" class="btn btn-outline-danger remove-input-field">Delete</button></td>' +
            '</tr>'
        );
    });

    $(document).on('click', '.remove-input-field', function () {
        $(this).parents('tr').remove();
    });

    $("#addmorebtn").on('click', function () {
        let clone = $('.lesson-template').first().clone(false);

        clone.find('input, textarea').val('');
        clone.find('input[type="checkbox"]').prop('checked', false);

        clone.find('.cke').remove();

        clone.find('textarea.editor').each(function () {
            $(this)
                .removeAttr('data-ckeditor-initialized')
                .removeAttr('style')
                .removeAttr('aria-hidden')
                .show()
                .attr('id', generateEditorId());
        });

        clone.find('.videos-wrapper').empty();
        clone.find('.video-url').addClass('d-none');
        clone.find('.video-file').addClass('d-none');
        clone.find('.video-url-input').val('').prop('required', false).prop('disabled', true);
        clone.find('.video-file-input').val('').prop('required', false).prop('disabled', true);
        clone.find('.video-type').val('upload');

        clone.find('.remove_less_slug').show();

        $(".mo_create").append(clone);

        initEditors(clone);
    });

    function removeLesslug(el) {
        let box = $(el).closest('.lesson-box').parent();

        box.find('textarea.editor').each(function () {
            let id = $(this).attr('id');
            if (id && CKEDITOR.instances[id]) {
                CKEDITOR.instances[id].destroy(true);
            }
        });

        box.remove();
    }
</script>
@endpush