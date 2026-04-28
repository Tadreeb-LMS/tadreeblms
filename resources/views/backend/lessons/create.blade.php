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
                            title="{{ __('course_pages.admin_lessons_create.remove_lesson') }}"></i>

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
                                        {{ trans('labels.backend.lessons.max_file_size') }} (JPEG, PNG, GIF)
                                    </div>
                                    <div class="custom-file-upload-wrapper">
                                        <input type="file" name="lesson_image[]" class="custom-file-input">
                                        <label class="custom-file-label">
                                            <i class="fa fa-upload mr-1"></i> {{ __('course_pages.admin_lessons_create.choose_file') }}
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
                                            <i class="fa fa-upload mr-1"></i> {{ __('course_pages.admin_lessons_create.choose_file') }}
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
                                            <i class="fa fa-upload mr-1"></i> {{ __('course_pages.admin_lessons_create.choose_file') }}
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
                                            <i class="fa fa-upload mr-1"></i> {{ __('course_pages.admin_lessons_create.choose_file') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row addvideocol">
                            <div class="col-md-4 form-group parent_group mt-2">
                                <div class="videos-section">
                                    <h5>{{ __('course_pages.admin_lessons_create.lesson_videos') }}</h5>

                                    <div class="videos-wrapper"></div>

                                    <button type="button" class="btn btn-primary mt-2 addVideo">
                                        {{ __('course_pages.admin_lessons_create.add_video') }}
                                    </button>
                                </div>

                                <div class="video-template d-none">
                                    <div class="video-item card p-3 mb-3">
                                        <label>{{ __('course_pages.admin_lessons_create.video_title') }}</label>
                                        <input type="text" name="videos[INDEX][title]" class="form-control" disabled>

                                        <label>{{ __('course_pages.admin_lessons_create.type') }}</label>
                                        <select name="videos[INDEX][type]" class="form-control video-type" disabled>
                                            <option value="upload">{{ __('course_pages.admin_lessons_create.upload') }}</option>
                                            <option value="youtube">YouTube</option>
                                            <option value="vimeo">Vimeo</option>
                                            <option value="embed">{{ __('course_pages.admin_lessons_create.embed') }}</option>
                                        </select>

                                        <div class="video-url mt-2 d-none">
                                            <label>{{ __('course_pages.admin_lessons_create.video_url') }}</label>
                                            <input type="text" name="videos[INDEX][url]"
                                                class="form-control video-url-input" disabled>
                                        </div>

                                        <div class="video-file mt-2 d-none">
                                            <label>{{ __('course_pages.admin_lessons_create.upload_file') }}</label>
                                            <input type="file" name="videos[INDEX][file]"
                                                class="form-control video-file-input" disabled>
                                        </div>

                                        <label class="mt-2">
                                            <input type="checkbox" name="videos[INDEX][is_preview]" value="1" disabled>
                                            {{ __('course_pages.admin_lessons_create.preview_video') }}
                                        </label>

                                        <button type="button" class="removeVideo btn btn-danger btn-sm mt-2">
                                            {{ __('course_pages.admin_lessons_create.remove') }}
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
                                <div for="duration" class="form-control-label mb-2">{{ __('course_pages.admin_lessons_create.duration') }}</div>
                                <div>
                                    <input type="text" name="duration[]" class="form-control"
                                        placeholder="{{ __('course_pages.admin_lessons_create.duration_minutes') }}">
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12 start_date">
                                <div for="duration" class="form-control-label mb-2">{{ __('course_pages.admin_lessons_create.lesson_start_date') }}</div>
                                <div>
                                    <input class="form-control" type="date" name="lesson_start_date[]"
                                        id="lesson_start_date">
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <div class="checkbox" style="margin-top: 37px;">
                                    <input type="hidden" name="published[0]" value="0">
                                    <input type="checkbox" name="published[0]" value="1" id="published_0"
                                        class="checkbox published_checkbox">
                                    <label for="published_0" class="checkbox control-label font-weight-bold published_label">
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
                                class="btn btn-outline-info">{{ __('course_pages.admin_lessons_create.add_more_lesson') }}</button>
                        </div>
                        <div>
                            <button type="submit" class="btn cancel-btn frm_submit" id="doneBtn">
                                {{ __('course_pages.admin_lessons_create.save_as_draft') }}
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

    function renumberLessonFileInputs() {
        $('.lesson-box').each(function (index) {
            const pointer = index + 1;
            $(this).find('input[name^="downloadable_files_"]').attr('name', 'downloadable_files_' + pointer + '[]');
            $(this).find('input[name^="add_pdf_"]').attr('name', 'add_pdf_' + pointer + '[]');
            $(this).find('input[name^="add_audio_"]').attr('name', 'add_audio_' + pointer + '[]');
            $(this).find('input[name^="video_file_"]').attr('name', 'video_file_' + pointer + '[]');
            $(this).find('input[name^="media_type_"]').attr('name', 'media_type_' + pointer + '[]');

            $(this).find('input[name^="published["]').attr('name', 'published[' + index + ']');
            $(this).find('.published_checkbox').attr('id', 'published_' + index);
            $(this).find('.published_label').attr('for', 'published_' + index);
        });
    }

    window.videoIndex = window.videoIndex || 0;

    $(document).ready(function () {
        initEditors($(document));
        renumberLessonFileInputs();

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
            const fileName = e.target.files.length > 0
                ? e.target.files[0].name
                : '{{ __('course_pages.admin_lessons_create.choose_file') }}';

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

        function parseIniSizeToBytes(sizeText) {
            if (!sizeText) return 0;
            const value = String(sizeText).trim();
            const unit = value.slice(-1).toUpperCase();
            const num = parseFloat(value);

            if (isNaN(num)) return 0;
            if (unit === 'G') return Math.round(num * 1024 * 1024 * 1024);
            if (unit === 'M') return Math.round(num * 1024 * 1024);
            if (unit === 'K') return Math.round(num * 1024);
            return Math.round(num);
        }

        const phpPostMax = parseIniSizeToBytes('{{ ini_get('post_max_size') }}');
        const phpUploadMax = parseIniSizeToBytes('{{ ini_get('upload_max_filesize') }}');

        let totalBytes = 0;
        let singleTooLarge = false;

        $('#addLesson input[type="file"]').each(function () {
            if (!this.files || !this.files.length) {
                return;
            }

            for (let idx = 0; idx < this.files.length; idx++) {
                const f = this.files[idx];
                totalBytes += f.size;

                if (phpUploadMax > 0 && f.size > phpUploadMax) {
                    singleTooLarge = true;
                }
            }
        });

        if (singleTooLarge) {
            const maxMb = Math.floor(phpUploadMax / (1024 * 1024));
            alert('One file exceeds upload_max_filesize (' + maxMb + 'MB). Please reduce file size or increase PHP limits.');
            return;
        }

        if (phpPostMax > 0 && totalBytes > phpPostMax) {
            const maxMb = Math.floor(phpPostMax / (1024 * 1024));
            alert('Total upload exceeds post_max_size (' + maxMb + 'MB). Please reduce files or increase PHP limits.');
            return;
        }

        $('.loading').text('{{ __('course_pages.admin_lessons_create.processing_please_wait') }}');
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

                if (!res || res.status !== 'success') {
                    $('#nextBtn,#doneBtn').prop('disabled', false);

                    const fallbackMessage = '{{ __('course_pages.admin_lessons_create.something_went_wrong') }}';
                    let msg = (res && (res.clientmsg || res.message))
                        ? (res.clientmsg || res.message)
                        : fallbackMessage;

                    if (typeof res === 'string' && res.indexOf('POST Content-Length') !== -1) {
                        msg = 'Upload is too large for current server limits (post_max_size/upload_max_filesize). Increase PHP limits and try again.';
                    }

                    alert(msg);
                    return;
                }

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

                let message = '{{ __('course_pages.admin_lessons_create.something_went_wrong') }}';

                if (xhr.responseJSON && xhr.responseJSON.clientmsg) {
                    message = xhr.responseJSON.clientmsg;
                } else if (xhr.status === 413) {
                    message = 'Upload is too large for server limits. Please reduce the video size and try again.';
                } else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                    if (firstKey && xhr.responseJSON.errors[firstKey] && xhr.responseJSON.errors[firstKey][0]) {
                        message = xhr.responseJSON.errors[firstKey][0];
                    }
                } else if (xhr.responseText && xhr.responseText.indexOf('POST Content-Length') !== -1) {
                    message = 'Upload is too large for current server limits (post_max_size/upload_max_filesize). Increase PHP limits and try again.';
                }

                alert(message);
            }
        });
    });

    var i = 1;

    $("#dynamic-ar").on('click', function () {
        ++i;
        $("#dynamicAddRemove").append(
            '<tr>' +
                '<td><input type="text" name="addMoreInputFields[' + i + '][subject]" placeholder="{{ __('course_pages.admin_lessons_create.enter_subject') }}" class="form-control" /></td>' +
                '<td><button type="button" class="btn btn-outline-danger remove-input-field">{{ __('course_pages.admin_lessons_create.delete') }}</button></td>' +
            '</tr>'
        );
    });

    $(document).on('click', '.remove-input-field', function () {
        $(this).parents('tr').remove();
    });

    $("#addmorebtn").on('click', function () {
        let clone = $('.lesson-template').first().clone(false);

        clone.find('input:not([type="checkbox"], [type="hidden"]), textarea').val('');
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
        renumberLessonFileInputs();
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
        renumberLessonFileInputs();
    }
</script>
@endpush