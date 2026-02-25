@extends('backend.layouts.app')
@section('title', __('labels.backend.courses.title') . ' | ' . app_name())
@push('after-styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet" />
@endpush
@section('content')
    <style>

        .float-right.gap-20 {
            gap: 20px;
            justify-content: right;
        }

        span.course-type-desc {
            padding: 0 0 0 20px;
            font-size: 12px;
            font-weight: bold;
            font-style: italic;
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
        }

        .create_done.next {
            background: #4dbd74;
        }
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

.select2-container--default .select2-selection--single {
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow b{
    display: none;
}
.select2-container .select2-selection--single .select2-selection__rendered {
    display: block;
    padding-left: 10px;
    padding-right: 20px;
    padding-top: 1px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.select2-container .select2-selection--single {
    box-sizing: border-box;
    cursor: pointer;
    display: block;
    height: 32px;
    user-select: none;
    -webkit-user-select: none;
}
    </style>

    <div id="main-flow">
        @include('backend.includes.partials.course-steps', ['step' => 1])
    </div>
    <div id="online-flow" style="display: none;">
        @include('backend.includes.partials.course-steps-online', ['step' => 1])
    </div>


    <form method="POST" action="{{ route('admin.courses.store') }}" id="addCourse" enctype="multipart/form-data">
    @csrf

    <div class="">
        <div class="pb-3 d-flex justify-content-between addcourseheader">

            
             <h5 >
                 @lang('labels.backend.courses.create')
             </h5>
            
                 <div class="">
                     <a href="{{ route('admin.courses.index') }}" class="btn btn-primary">@lang('labels.backend.courses.view') </a>
        
                 </div>
         
        </div>
        <div class="card coursesteps">
        {{-- <div class="card-header">
            <h3 class="page-title float-left">@lang('labels.backend.courses.create')</h3>
            <div class="float-right">
                <a href="{{ route('admin.courses.index') }}" class="btn btn-success">@lang('labels.backend.courses.view')</a>
            </div>
        </div> --}}

        <div class="card-body">
            @if (Auth::user()->isAdmin())


            <div class="row">
                <div class="col-md-6 col-12 form-group frmbm10">
                <div class="row">
                    <div class="col-md-8 col-12 form-group">
                        <div> 
                            Teachers
                        </div>
                        <div class="custom-select-wrapper mt-2">
    <select name="teachers[]" class="form-control custom-select-box select2 js-example-placeholder-multiple" multiple>
        @foreach($teachers as $id => $teacher)
            <option value="{{ $id }}" @if(in_array($id, old('teachers', []))) selected @endif>
                {{ $teacher }}
            </option>
        @endforeach
    </select>
    <span class="custom-select-icon">
        <i class="fa fa-chevron-down"></i>
    </span>
</div>
                    </div>
                    <div class="col-md-1 col-12 d-flex form-group flex-column"><span class="ortext">
                        OR
                       </span></div>
                    <div class="col-md-3 col-12 d-flex form-group flex-column">
                         <a target="_blank" class="btn btn-primary mt-auto"
                            href="{{ url('user/teachers/create?teacher') }}">{{ trans('labels.backend.courses.add_teachers') }}</a>
                    </div>
                </div>
            @endif

            @if (Auth::user()->isAdmin())
                {{-- <div class="row">
                        <div class="col-10 form-group">
                            <label for="internal_students" class="control-label">
                                {{ trans('labels.backend.courses.fields.internal_students') }}
                            </label>
                            <input class="form-control" placeholder="{{ trans('labels.backend.courses.fields.internal_students') }}" name="internal_students" type="text" value="{{ old('internal_students') }}">
                        </div>
                    </div> --}}
            @endif

            @if (Auth::user()->isAdmin())
                {{-- <div class="row">
            <div class="col-10 form-group">
               {!! Form::label('external_students',trans('labels.backend.courses.fields.external_students'), ['class' => 'control-label']) !!}
               {!! Form::select('externalStudents[]', $externalStudents, old('externalStudents'), ['class' => 'form-control select2 js-example-external-student-placeholder-multiple', 'multiple' => 'multiple', 'required' => false]) !!}
            </div>
        </div> --}}
            @endif

            <div class="row">
                <div class="col-md-8 col-12 form-group">
                    <div>Category</div>
                   <div class="custom-select-wrapper mt-2">
    <select name="category_id" class="form-control custom-select-box select2 js-example-placeholder-single">
        <option value="">Select Category</option>
        @foreach($categories as $id => $category)
            <option value="{{ $id }}" @if(old('category_id') == $id) selected @endif>
                {{ $category }}
            </option>
        @endforeach
    </select>
    <span class="custom-select-icon">
        <i class="fa fa-chevron-down"></i>
    </span>
</div>
                </div> <div class="col-md-1 col-12 d-flex form-group flex-column">
                <span class="ortext">
                        OR
                       </span>
                       </div>
                <div class="col-md-3 col-12 d-flex form-group flex-column">
                     <a target="_blank" class="btn btn-primary mt-auto"
                        href="{{ route('admin.categories.create') . '?create' }}">{{ trans('labels.backend.courses.add_categories') }}</a>
                </div>
            </div>

            <div class="row">

                <div class="col-sm-12 col-lg-12 col-md-12 form-group">
                    <label for="course_code" class="control-label">Course Code *</label>
                    <input class="form-control" placeholder="Course code" name="course_code" type="text" value="{{ old('course_code') }}">
                </div>
                <div class="col-md-12 col-lg-12 form-group">
                    <div>
                        <label for="slug" class="control-label">{{ trans('Course Language') }}</label>
                    </div>
                    <div class="custom-select-wrapper">

                        <select name="course_lang" class="form-control custom-select-box">
                            <option value="english">English</option>
                            <option value="arabic">Arabic</option>
                        </select>
                        <span class="custom-select-icon">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                </div>
                
                <div class="col-sm-12 col-lg-12 col-md-12 form-group">
                    <label for="title" class="control-label">{{ trans('labels.backend.courses.fields.title') }} *</label>
                    <input class="form-control" placeholder="{{ trans('labels.backend.courses.fields.title') }}" name="title" type="text" value="{{ old('title') }}">
                </div>
                {{-- <div class="col-sm-12 col-lg-4 col-md-12 form-group">
                    {!! Form::label('arabic_title', trans('Title In Arabic') . ' *', ['class' => 'control-label']) !!}
                    {!! Form::text('arabic_title', old('arabic_title'), [
                        'class' => 'form-control',
                        'placeholder' => trans('Arabic Title'),
                    ]) !!}

                </div> --}}
                {{-- <div class="col-md-12 col-lg-6 form-group">
                    {!! Form::label('slug', trans('labels.backend.courses.fields.slug'), ['class' => 'control-label']) !!}
                    {!! Form::text('slug', old('slug'), [
                        'class' => 'form-control',
                        'placeholder' => trans('labels.backend.courses.slug_placeholder'),
                    ]) !!}

                </div> --}}
                
            </div>
</div>

<div class="col-md-6 col-12 form-group">
 <div class="form-group">
                    <label for="description" class="control-label">{{ trans('labels.backend.courses.fields.description') }}</label>
                    <textarea class="form-control editor" placeholder="{{ trans('labels.backend.courses.fields.description') }}" name="description" cols="50" rows="10">{{ old('description') }}</textarea>

                </div>
 </div>
</div>

 
            <div class="row">
                {{-- <div class="col-sm-12 col-lg-2 col-md-12 form-group">
                    {!! Form::label('price', trans('labels.backend.courses.fields.price'), [
                        'class' => 'control-label',
                    ]) !!}
                    {!! Form::number('price', old('price'), [
                        'class' => 'form-control',
                        'placeholder' => trans('labels.backend.courses.fields.price'),
                        'step' => 'any',
                        'pattern' => '[0-9]',
                    ]) !!}
                </div> --}}
                
                {{-- <div class="col-12 col-lg-4 form-group">
                                {!! Form::label(
                                    'strike',
                                    trans('labels.backend.courses.fields.strike') . ' (in ' . $appCurrency['symbol'] . ')',
                                    ['class' => 'control-label'],
                                ) !!}
                                {!! Form::number('strike', old('strike'), [
                                    'class' => 'form-control',
                                    'placeholder' => trans('labels.backend.courses.fields.strike'),
                                    'step' => 'any',
                                    'pattern' => '[0-9]',
                                ]) !!}
                            </div> --}}
                <div class="col-sm-12 col-lg-4 col-md-12 form-group">
                    <div style="margin-bottom: 8px;">
                        Course Image
                    </div>

                   <div class="custom-file-upload-wrapper">
    <input type="file" name="course_image" id="customFileInput" class="custom-file-input">
    <label for="customFileInput" class="custom-file-label">
        <i class="fa fa-upload mr-1"></i> Choose a file
    </label>
</div>

                </div>
                <div class="col-sm-12 col-lg-4 col-md-12  form-group">
                    <label for="start_date" class="control-label">{{ trans('labels.backend.courses.fields.start_date') }} (yyyy-mm-dd) <span class="date-required-star" style="display:none">*</span></label>

                   <input class="form-control" id="start_date" autocomplete="off" placeholder="yyyy-mm-dd" name="start_date" type="text" value="{{ old('start_date') }}">
                   </div>

                @if (Auth::user()->isAdmin())
                    <div class="col-sm-12 col-lg-4 col-md-12 form-group">
                        <label for="expire_at" class="control-label">{{ trans('labels.backend.courses.fields.expire_at') }} (yyyy-mm-dd) <span class="date-required-star" style="display:none">*</span></label>
                        <input class="form-control date" id="expire_at" pattern="(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))" placeholder="{{ trans('labels.backend.courses.fields.expire_at') }} (Ex . 2019-01-01)" autocomplete="off" name="expire_at" type="text" value="{{ old('expire_at') }}">

                    </div>
                @endif
            </div>

            {{-- <div class="row">
                        <label class="col-md-2 form-control-label" for="first_name">Select Department</label>

                        <div class="col-md-10">
                            <select name="department_id" class="form-control">
                                <option value=""> Select One </option>
                                @foreach ($departments as $row)
    <option value="{{ $row->id }}"> {{ $row->title }} </option>
    @endforeach
                            </select>
                        </div>
             </div> --}}

            <div class="row">
                <div class="col-md-12 form-group">
                    <input class="course-type mr-2 " type="radio" checked name="course_type" value="Online" /> E-Learning
                    <input class="course-type ml-2 mr-2" type="radio" name="course_type" value="Offline" /> Live-Online
                    <input class="course-type ml-2 mr-2" type="radio" name="course_type" value="Live-Classroom" /> Live-Classroom
                </div>
                <span class="course-type-desc">
                    <span id="e-learning">
                        E-Learning type course is a course which can be taken online.
                    </span>
                    <span id="live-online" style="display: none;">
                        Live-Online type course is a course can be done on goole meet/Zoom link.
                    </span>
                    <span id="live-classroom" style="display: none;">
                        Live-Classroom type course is a course can be happen on a specific classroom location.
                    </span>
                </span>
            </div>
            
            

            {{-- <div class="row" id="online-course-material">
                <div class="col-md-12 form-group">

                   <div class="mt-2 custom-select-wrapper">
                    <select name="media_type" class="form-control custom-select-box" id="media_type">
                        <option value="">Select One</option>
                        <option value="youtube" @if(old('media_type') == 'youtube') selected @endif>Youtube</option>
                        <option value="vimeo" @if(old('media_type') == 'vimeo') selected @endif>Video</option>
                        <option value="upload" @if(old('media_type') == 'upload') selected @endif>Upload</option>
                        <option value="embed" @if(old('media_type') == 'embed') selected @endif>Embed</option>
                    </select>
                    <span class="custom-select-icon">
                        <i class="fa fa-chevron-down"></i>
                    </span>
                </div>

                <!-- Video URL Input (YouTube, Vimeo, Embed) -->
                <input type="text" name="video" id="video"
                    value="{{ old('video') }}"
                    class="form-control mt-3 d-none"
                    placeholder="{{ trans('labels.backend.lessons.enter_video_url') }}">

                    <!-- Video Upload Input -->
                    <input type="file" name="video_file" id="video_file"
                        class="form-control mt-3 d-none"
                        accept="video/mp4"
                        placeholder="{{ trans('labels.backend.lessons.enter_video_url') }}">
            </div> --}}
            {{--     <div class="col-md-12 form-group d-none" id="video_subtitle_box">

                {!! Form::label('add_subtitle', trans('labels.backend.lessons.fields.add_subtitle'), ['class' => 'control-label']) !!}

                {!! Form::file('video_subtitle', ['class' => 'form-control', 'placeholder' => trans('labels.backend.lessons.video_subtitle'),'id'=>'video_subtitle'  ]) !!}

                </div>
                <div class="col-md-12 form-group">

                    @lang('labels.backend.lessons.video_guide')
                </div> --}}
                
            </div>
             <div class="btmbtns">
            <div class="row">
                
                <div class="col-12 d-flex float-right gap-20">
                    {{-- <div class="col-12 text-center form-group">
                                {!! Form::submit(trans('strings.backend.general.app_save'), ['class' => 'btn btn-lg btn-danger']) !!}
                            </div> --}}
                            <div class="">
                                <input class="btn add-btn frm_submit" id="doneBtn" type="submit" value="{{ trans('Save As Draft') }}">
                            </div>
                            <div class="">
                                <input class="btn cancel-btn frm_submit" id="nextBtn" type="submit" value="{{ trans('Next') }}">
                            </div>
    
                </div>
            </div>
            </div> 
        </div>
        <input type="hidden" id="course_index" value="{{ route('admin.courses.index') }}">
        <input type="hidden" id="lesson" value="{{ route('admin.lessons.create') }}">
        <input type="hidden" id="new-assisment" value="{{ route('admin.assessment_accounts.new-assisment') }}">
    </div>
    </div>
    
    </form>
@stop

@push('after-scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <script src="{{ asset('/vendor/laravel-filemanager/js/lfm.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="/js/helpers/form-submit.js"></script>
    <script>
        
    // Validate total weightage <= 100
function validateWeightage() {
    let total = 0;
    document.querySelectorAll('.sm-input').forEach(function(input) {
        let val = parseInt(input.value) || 0;
        total += val;
    });

    if (total > 100) {
        toastr.remove();
        toastr.error('Total module weightage cannot exceed 100%.');
        return false;
    }
    return true;
}

document.querySelectorAll('.sm-input').forEach(function(input) {
    input.addEventListener('input', function() {
        let total = 0;
        document.querySelectorAll('.sm-input').forEach(function(i) {
            total += parseInt(i.value) || 0;
        });
        if (total > 100) {
            input.value = '';
            toastr.remove();
            toastr.error('Total module weightage cannot exceed 100%');
        }
    });
});




        $(document).ready(function() {
    var dateToday = new Date();

$('#start_date').datepicker({
    autoclose: true,
    startDate: dateToday,
    format: "yyyy-mm-dd"
}).on('changeDate', function(e) {
    $('#expire_at').datepicker('setStartDate', e.date);
});

$('#expire_at').datepicker({
    autoclose: true,
    startDate: dateToday,
    format: "yyyy-mm-dd"
});


    $(".js-example-placeholder-single").select2({
        placeholder: "{{ trans('labels.backend.courses.select_category') }}",
    });

    $(".js-example-placeholder-multiple").select2({
        placeholder: "{{ trans('labels.backend.courses.select_teachers') }}",
    });

    $(".js-example-internal-student-placeholder-multiple").select2({
        placeholder: "{{ trans('labels.backend.courses.select_internal_students') }}",
    });

    $(".js-example-external-student-placeholder-multiple").select2({
        placeholder: "{{ trans('labels.backend.courses.select_external_students') }}",
    });
});


        var uploadField = $('input[type="file"]');

        $(document).on('change', 'input[type="file"]', function() {
            var $this = $(this);
            $(this.files).each(function(key, value) {
                // if (value.size > 100000000) {
                //     alert('"' + value.name + '"' + 'exceeds limit of maximum file upload size')
                //     $this.val("");
                // }
            })
        })


        $(document).on('change', '.course-type', function () {
    const type = $(this).val();

    if (type === 'Live-Classroom') {
        $('#e-learning').hide();
        $('#live-online').hide();
        $('#live-classroom').show();

        $('#main-flow').hide();
        $('#online-flow').show();

        // Start Date REQUIRED
        $('#startDateWrapper').show();
        $('#start_date').prop('required', true);

    } else if (type === 'Offline') { // Live-Online
        $('#e-learning').hide();
        $('#live-online').show();
        $('#live-classroom').hide();

        $('#main-flow').hide();
        $('#online-flow').show();

        // Start Date REQUIRED
        $('#startDateWrapper').show();
        $('#start_date').prop('required', true);

    } else {
        // E-Learning
        $('#e-learning').show();
        $('#live-online').hide();
        $('#live-classroom').hide();

        $('#main-flow').show();
        $('#online-flow').hide();

        // Start Date NOT required
        $('#startDateWrapper').hide();
        $('#start_date').val('').prop('required', false);
    }

    // Toggle date required asterisks based on course type
    if (type === 'Online') {
        $('.date-required-star').hide();
    } else {
        $('.date-required-star').show();
    }
});



$(document).ready(function () {
    $('.course-type:checked').trigger('change');
});


        $(document).on('change', '#media_type', function() {
            if ($(this).val()) {
                if ($(this).val() != 'upload') {
                    $('#video').removeClass('d-none').attr('required', true)
                    $('#video_file').addClass('d-none').attr('required', false)
                    //                    $('#video_subtitle_box').addClass('d-none').attr('required', false)

                } else if ($(this).val() == 'upload') {
                    $('#video').addClass('d-none').attr('required', false)
                    $('#video_file').removeClass('d-none').attr('required', true)
                    //                    $('#video_subtitle_box').removeClass('d-none').attr('required', true)
                }
            } else {
                $('#video_file').addClass('d-none').attr('required', false)
                //                $('#video_subtitle_box').addClass('d-none').attr('required', false)
                $('#video').addClass('d-none').attr('required', false)
            }
        })
    </script>

    <script>
        var nxt_url_val = '';
        $('.frm_submit').on('click', function() {
            nxt_url_val = $(this).val();
        });
        $('#addCourse').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);

            function enableButtons() {
                $form.find('input[type=submit], button[type=submit]').removeAttr('disabled').prop('disabled', false);
            }

            function clearInlineErrors() {
                $form.find('.inline-error').remove();
                $form.find('.is-invalid').removeClass('is-invalid');
            }

            function showInlineError(field, message) {
                var $field = $form.find(field);
                $field.addClass('is-invalid');
                $field.closest('.form-group').find('.inline-error').remove();
                $field.after('<span class="text-danger inline-error w-100 d-block mt-1">' + message + '</span>');
            }

            clearInlineErrors();

            // Validate weightage
            if (!validateWeightage()) {
                enableButtons();
                setTimeout(enableButtons, 0);
                return false;
            }

            var startDateVal = $('#start_date').val();
            var expireDateVal = $('#expire_at').val();
            var courseType = $('input[name="course_type"]:checked').val();
            var hasError = false;

            if (courseType !== 'Online') {
                if (!startDateVal) {
                    showInlineError('#start_date', 'Start Date is required.');
                    hasError = true;
                }
                if (!expireDateVal) {
                    showInlineError('#expire_at', 'Expire Date is required.');
                    hasError = true;
                }
            }

            if (startDateVal && expireDateVal && expireDateVal < startDateVal) {
                showInlineError('#expire_at', 'Expire Date cannot be earlier than Start Date.');
                hasError = true;
            }

            var today = new Date().toISOString().slice(0, 10);

            if (startDateVal && startDateVal < today) {
                showInlineError('#start_date', 'Start Date cannot be earlier than today.');
                hasError = true;
            }

            if (hasError) {
                enableButtons();
                setTimeout(enableButtons, 0);
                scrollToClass('inline-error');
                return false;
            }

            hrefurl = $(location).attr("href");
            last_part = hrefurl.substr(hrefurl.lastIndexOf('/') + 8)
            // alert(last_part);
            setTimeout(() => {
                //let data = $('#addCourse').serialize();
                var form = $('#addCourse')[0];
                var data = new FormData(form);
                let url = '{{ route('admin.courses.store') }}'
                let val = $('#nextBtn').val();
                let valDone = $('#doneBtn').val();
                var redirect_url = $("#lesson").val()
                var redirect_url_course = $("#course_index").val()
                var redirect_url_assi = $("#new-assisment").val()
                const obj = $(this);

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: data,
                    datatype: "json",
                    enctype: 'multipart/form-data',
                    processData: false,
                    contentType: false,
                    cache: false,
                    timeout: 600000,
                    success: function(res) {
                        //console.log(res.redirect_url)
                        //alert(res.clientmsg)
                        redirect_url = res.redirect_url;

                        if (last_part == null || last_part == undefined || last_part == '') {
                            if (nxt_url_val == 'Next') {
                                window.location.href = redirect_url + '&uuid=' + res.temp_id;
                                return;
                            }
                            if (nxt_url_val == 'Done') {
                                window.location.href = redirect_url_course;
                                return;
                            } else {
                                window.location.href = redirect_url_course;
                                return;
                            }
                        }

                        if (nxt_url_val == 'Done' && last_part == 'course_new') {
                            window.location.href = redirect_url_assi;
                            return;
                        } else {
                            window.location.href = redirect_url_course;
                            return;
                        }

                    },
                    error: function(xhr, status, error) {
                        console.log(xhr)
                        res = JSON.parse(xhr.responseText)
                        //alert(res.clientmsg);
                        let submitbtn = obj.find("[type=submit]");
                        submitbtn.prop("disabled", false);
                        showErrorMessage(obj, xhr)
                    }
                })
            }, 100);
        });
    </script>
    <script>
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const label = input.nextElementSibling;
            const fileName = e.target.files.length > 0 ? e.target.files[0].name : 'Choose a file';
            label.innerHTML = '<i class="fa fa-upload mr-1"></i> ' + fileName;
        });
    });
</script>
@endpush
