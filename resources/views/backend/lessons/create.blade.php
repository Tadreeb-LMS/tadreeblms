@extends('backend.layouts.app')
@section('title', __('labels.backend.lessons.title').' | '.app_name())

@push('after-styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/jquery.datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="{{asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.css')}}">
<style>
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

@include('backend.includes.partials.course-steps', ['step' => 2, 'course_id' => $course->id, 'course' => $course ])

<form method="POST" id="addLesson" enctype="multipart/form-data" autocomplete="off">
@csrf()


@if($courses_all)
<input type="hidden" name="category_id" value="{{ $courses_all }}" id="category_id">

@endif
<div class="pb-3 d-flex justify-content-between align-items-center">
       <h4>
          @lang('labels.backend.lessons.create')
       </h4>
       <div class="">
           <a href="{{ route('admin.courses.index') }}" class="btn add-btn">@lang('labels.backend.lessons.view')</a>
       </div>
     
   </div>

<div class="card">
    <!-- <div class="card-header">
        <h3 class="page-title float-left mb-0">@lang('labels.backend.lessons.create')</h3>
        <div class="float-right">
            <a href="{{ route('admin.lessons.index') }}" class="btn btn-success">@lang('labels.backend.lessons.view')</a>
        </div>
    </div> -->

    <div class="card-body">
        <div class="row">
            <div class="col-md-12 col-lg-6 form-group">
                <div for="course_id" class="form-control-label">{{ trans('labels.backend.lessons.fields.course') }}</div>
                <div class="mt-2 custom-select-wrapper">

                    <select name="course_id" class="form-control custom-select-box course_id select2">
                        @foreach($courses as $key => $course)
                            <option value="{{ $key }}" {{ (old('course_id') == $key || request('course_id') == $key) ? 'selected' : '' }}>
                                {{ $course }}
                            </option>
                        @endforeach
                    </select>
                    <span class="custom-select-icon">
                        <i class="fa fa-chevron-down"></i>
                    </span>
                </div>

                
            </div>
            <div class="col-md-12 col-lg-6 form-group">
                <div for="lesson_image" class="control-label mb-2">
                    {{ trans('labels.backend.lessons.fields.lesson_image') }} {{ trans('labels.backend.lessons.max_file_size') }}
                </div>
                <div class="custom-file-upload-wrapper">
                            <input type="file" name="image" id="customFileInput" class="custom-file-input">
                            <label for="customFileInput" class="custom-file-label">
                            <i class="fa fa-upload mr-1"></i> Choose a file
                            </label>
                        </div>
                <!-- <input type="file" name="lesson_image[]" class="form-control" accept="image/jpeg,image/gif,image/png" />
                <input type="hidden" name="lesson_image_max_size" value="8" />
                <input type="hidden" name="lesson_image_max_width" value="4000" />
                <input type="hidden" name="lesson_image_max_height" value="4000" /> -->
                

            </div>

        </div>

        <div class="row">
        <div class="col-md-12 col-lg-6 form-group">
                
            <label for="title" class="control-label">
                {{ trans('labels.backend.lessons.fields.title') }} *
            </label>
            <input type="text" name="title[]" value="{{ old('title') }}" class="form-control" placeholder="{{ trans('labels.backend.lessons.fields.title') }}" required />
            
        </div>

        {{-- <div class="col-12 col-lg-6 form-group">
                
            <label for="title" class="control-label">
                {{ trans('Arabic Title') }}*
            </label>
            <input type="text" name="arabic_title[]" value="{{ old('arabic_title') }}" class="form-control" placeholder="{{ trans('Arabic Title') }}" required />
            
        </div> --}}

        {{-- <div class="col-12 col-lg-12 form-group">
                <label for="slug" class="control-label">
                    {{ trans('labels.backend.lessons.fields.slug') }}
                </label>
                <input type="text" name="slug[]" value="{{ old('slug') }}" class="form-control" placeholder="{{ trans('labels.backend.lessons.slug_placeholder') }}" />
                
         </div> --}}
        </div>



        <div class="row">
            <div class="col-12 form-group">
                <label for="short_text" class="control-label">
                    {{ trans('labels.backend.lessons.fields.short_text') }}
                </label>
                <textarea name="short_text[]" class="form-control" placeholder="{{ trans('labels.backend.lessons.short_description_placeholder') }}" style="height: 100px;">{{ old('short_text') }}</textarea>
                

            </div>
        </div>
        <div class="row">
            <div class="col-12 form-group">
                <label for="full_text" class="control-label">
                    {{ trans('labels.backend.lessons.fields.full_text') }}
                </label>
                <textarea name="full_text[]" class="form-control editor" placeholder="" id="editor">
                    {{ old('full_text') }}
                </textarea>
                

            </div>
        </div>
        <div class="row">
            <div class="col-12 form-group">
                <div for="downloadable_files" class="control-label mb-2">
                    {{ trans('labels.backend.lessons.fields.downloadable_files') }} {{ trans('labels.backend.lessons.max_file_size') }}
                </div>
                <!-- <input type="file" name="downloadable_files_1[]" multiple class="form-control file-upload" id="downloadable_files" accept="image/jpeg,image/gif,image/png,application/msword,audio/mpeg,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-powerpoint,application/pdf,video/mp4">
                
                <div class="photo-block">
                    <div class="files-list"></div>
                </div> -->
                <div class="custom-file-upload-wrapper">
                            <input type="file" name="downloadable_files_1[]" id="customFileInput" class="custom-file-input">
                            <label for="customFileInput" class="custom-file-label">
                            <i class="fa fa-upload mr-1"></i> Choose a file
                            </label>
                        </div>

            </div>
        </div>
        <div class="row">
            <div class="col-12 form-group mt-2">
                <div for="add_pdf" class="control-label mb-2">
                    {{ trans('labels.backend.lessons.fields.add_pdf') }}
            </div>
              <div class="custom-file-upload-wrapper">
                            <input type="file" name="add_pdf_1[]" id="customFileInput" class="custom-file-input">
                            <label for="customFileInput" class="custom-file-label">
                            <i class="fa fa-upload mr-1"></i> Choose a file
                            </label>
                        </div>
                
            </div>
        </div>

        <div class="row">
            <div class="col-12 form-group mt-2">
                <div for="add_audio" class="control-label mb-2">
                    {{ trans('labels.backend.lessons.fields.add_audio') }}
                </div>
                <div class="custom-file-upload-wrapper">
                            <input type="file" name="add_audio_1[]" id="customFileInput" class="custom-file-input">
                            <label for="customFileInput" class="custom-file-label">
                            <i class="fa fa-upload mr-1"></i> Choose a file
                            </label>
                        </div>
                
            </div>
        </div>


        <div class="row">
            <div class="col-md-12 form-group parent_group mt-2">
                <label for="add_video" class="control-label">
                    {{ trans('labels.backend.lessons.fields.add_video') }}
                </label>
                
                <select name="media_type_1[]" class="form-control media_type" id="media_type">
                    <option value="" disabled selected>Select One</option>
                    <option value="youtube">Youtube</option>
                    <option value="vimeo">Vimeo</option>
                    <option value="upload">Upload</option>
                    <option value="embed">Embed</option>
                </select>
                
                <input type="text" name="video" value="{{ old('video') }}" class="form-control mt-3 d-none video" placeholder="{{ trans('labels.backend.lessons.enter_video_url') }}" id="video">
                
                <input type="file" name="video_file_1[]" class="form-control mt-3 d-none video_file" placeholder="{{ trans('labels.backend.lessons.enter_video_url') }}" id="video_file">
                
                <p>@lang('labels.backend.lessons.video_guide')</p>
                

            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-4 col-sm-12">
                <div for="duration" class="form-control-label mb-2">Duration</div>
               
                    

                    <div class="">
                        
                        <input type="text" name="duration[]" class="form-control" placeholder="Duration [minutes]">

                    </div>
               
            
            </div>
            <div class="col-md-4 col-sm-12">
                <div for="duration" class="form-control-label mb-2">Lesson Start Date</div>
                


                <div class="">
                    <input class="form-control" type="date" name="lesson_start_date" id="lesson_start_date" placeholder="12/09/2022">
                </div>
                
            </div>
            <div class="col-md-4 col-sm-12">
            <div class="checkbox" style="margin-top: 30px;">
                <input type="hidden" name="published" value="0">
                <input type="checkbox" name="published" value="1" id="published" class="checkbox">
                <label for="published" class="checkbox control-label font-weight-bold">
                    {{ trans('labels.backend.lessons.fields.published') }}
                </label>

            </div>
                    </div>
        </div>

        

        <div class="row">
            
        </div>
        <div class="mo_create"></div>

        <div class="d-flex justify-content-between form-group">
            <div>

                <button type="button" name="addmorebtn" id="addmorebtn" class="btn btn-outline-info ">Add More Lesson</button>
            </div>
            <div>

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
<script src="{{asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js"></script>

<script src="{{asset('/vendor/laravel-filemanager/js/lfm.js')}}"></script>
<script>
    $(document).ready(function() {

        $('#lesson_start_date_').datetimepicker({
            format: 'Y-m-d H:00',
        });

        $('.custom-date-picker').datetimepicker({
            format: 'Y-m-d H:00',
        });
    });

    

    var uploadField = $('input[type="file"]');

    $(document).on('change', 'input[name="lesson_image"]', function() {
        var $this = $(this);
        $(this.files).each(function(key, value) {
            // if (value.size > 5000000) {
            //     alert('"' + value.name + '"' + 'exceeds limit of maximum file upload size')
            //     $this.val("");
            // }
        })
    });

    jQuery(document).on('change', '#media_type', function() {
        console.log('change');
        if ($(this).val()) {
            if ($(this).val() != 'upload') {
                
                    $(this).parent().closest('.parent_group').children('.video').removeClass('d-none').attr('required', true);

                //$('#video').removeClass('d-none').attr('required', true)
                //$('#video_file').addClass('d-none').attr('required', false)
                $(this).parent().closest('.parent_group').children('.video_file').addClass('d-none').attr('required', false);
            } else if ($(this).val() == 'upload') {
                $(this).parent().closest('.parent_group').children('.video').addClass('d-none').attr('required', false);
                //$('#video').addClass('d-none').attr('required', false)
                $(this).parent().closest('.parent_group').children('.video_file').removeClass('d-none').attr('required', true);
                //$('#video_file').removeClass('d-none').attr('required', true)
            }
        } else {
            $(this).parent().closest('.parent_group').children('.video_file').addClass('d-none').attr('required', false);
            $(this).parent().closest('.parent_group').children('.video').addClass('d-none').attr('required', false);
            //$('#video_file').addClass('d-none').attr('required', false)
            //$('#video').addClass('d-none').attr('required', false)
        }
    })

    $('#course_id').on('change', function() {
        $.ajax({
            url: "{{ route('lessons.course.check') }}",
            method: "GET",
            data: {
                id: $(this).val()
            },
            dataType: "json",
            beforeSend: function() {},
            success: function(data) {
                if (data.success && data.category == 'Internal') {
                    $('.start_date').hide();
                } else {
                    $('.start_date').show();
                }
            }
        });

    })
</script>

<script>
    var nxt_url_val = '';

    $('.frm_submit').on('click', function() {
        let clickedButtonId = $(this).attr('id');
        $('#btn_clicked').val(clickedButtonId);
    });
    $(document).on('submit', '#addLesson', function(e) {
        e.preventDefault();

        $('.loading').text('processing please wait...');

        setTimeout(() => {
            //let data = $('#addLesson').serialize();
            var form = $('#addLesson')[0];
            var data = new FormData(form);
            let url = '{{route('admin.lessons.store')}}';
            var redirect_url = $("#ass_index").val();
            var redirect_url_course = $("#lesson_index").val();

            var redirect_question_url = $("#add_question_url").val();
            var temp_id = $('#temp_id').val();
            var course_id = $(".course_id").val();

            data.append('btn_clicked', $('#btn_clicked').val())
            nxt_url_val = $('#btn_clicked').val()
            //return false;
            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                datatype: "json",
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                cache: false,
                timeout: 6000000,
                success: function(res) {
                    $('.loading').text('');
                    //alert(nxt_url_val)
                    if (nxt_url_val == 'nextBtn') {
                        //window.location.href = redirect_url + "&course_id=" + course_id;
                        window.location.href = redirect_question_url + "/" + course_id + "/" + res.temp_id;
                        return;
                    } 
                    if (nxt_url_val == 'doneBtn') {
                        //alert(redirect_url_course)
                        window.location.href = redirect_url_course;
                        return;
                    }

                    
                },
                error: function(xhr, status, error) {
                    //alert("someting went wrong")
                    $('.loading').text('');
                    if (xhr?.responseJSON?.clientmsg) {
                        alert(xhr?.responseJSON?.clientmsg);
                        $('#nextBtn,#doneBtn').prop('disabled',false);
                        return;
                   }
                    res = JSON.parse(xhr.responseText);
                    
                }
            })
        }, 100);
    })
</script>

<script>
    var i = 1;
    $("#dynamic-ar").click(function() {
        ++i;
        $("#dynamicAddRemove").append('<tr><td><input type="text" name="addMoreInputFields[' + i +
            '][subject]" placeholder="Enter subject" class="form-control" /></td><td><button type="button" class="btn btn-outline-danger remove-input-field">Delete</button></td></tr>'
        );
    });
    $(document).on('click', '.remove-input-field', function() {
        $(this).parents('tr').remove();
    });
</script>

<script>
    $("#addmorebtn").on('click', function() {
        ++i;
        

        html = `<div class="form-group checkingListC row position-relative mt-5">
                    <div class=" justify-content-end align-items-center mb-4 col-sm-6">
                            <label for="option" class="mr-4 flex-fill" >Title*</label>
                            <div class="flex-fill"><input class="form-control" name="title[]" id="lesson" type="text" autocomplete="off" required/></div>
                    </div>
                    <!--div class=" justify-content-end align-items-center mb-4 col-sm-6">
                            <label for="option" class="mr-4 flex-fill" >Arabic Title*</label>
                            <div class="flex-fill"><input class="form-control" name="arabic_title[]" id="lesson_arabic" type="text" autocomplete="off" required/></div>
                    </div-->
                    <div class=" justify-content-end align-items-center mb-4 col-sm-12">
                            <label for="option" class="mr-4 flex-fill" > Slug </label>
                            <div class="flex-fill"><input class="form-control" name="slug[]" id="slug" type="text" autocomplete="off"/></div>
                    </div>
                    <div class=" justify-content-end align-items-center mb-4 col-sm-6">
                            <label for="option" class="mr-4 flex-fill">Lesson Image (max file size 5MB)</label>
                            <div class="flex-fill"><input class="form-control" name="lesson_image[]" id="lesson" type="file" autocomplete="off"/></div>
                    </div>

                    <div class="col-12 form-group">
                <label for="short_text" class="control-label">Short Text</label>
                <textarea class="form-control " placeholder="Input short description of lesson" name="short_text[]" cols="50" rows="10" id="short_text"></textarea>
                     </div>

                     <div class="col-12 form-group">
                <label for="full_text" class="control-label">Full Text</label>
                <textarea class="form-control editor" placeholder="Input short description of lesson" name="full_text[]" cols="50" rows="10" id="short_text"></textarea>
                     </div>
                     <div class="col-12 form-group">
                <label for="downloadable_files" class="control-label">Downloadable Files (max file size 5MB)</label>
                <input multiple="" class="form-control file-upload" id="downloadable_files" accept="image/jpeg,image/gif,image/png,application/msword,audio/mpeg,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-powerpoint,application/pdf,video/mp4" name="downloadable_files_${i}[]" type="file">
                <div class="photo-block">
                    <div class="files-list"></div>
                </div>

            </div>

            <div class="col-12 form-group">
                <label for="pdf_files" class="control-label">Add PDF</label>
                <input class="form-control file-upload" id="add_pdf" accept="application/pdf" name="add_pdf_${i}[]" type="file">
            </div>
            <div class="col-12 form-group">
                <label for="audio_files" class="control-label">Add Audio</label>
                <input class="form-control file-upload" id="add_audio" accept="audio/mpeg3" name="add_audio_${i}[]" type="file">
            </div>
            <div class="col-md-12 form-group parent_group">
                <label for="add_video" class="control-label">Add Video</label>

                <select class="form-control" id="media_type" name="media_type_${i}[]"><option selected="selected" value="">Select One</option><option value="youtube">Youtube</option><option value="vimeo">Vimeo</option><option value="upload">Upload</option><option value="embed">Embed</option></select>

                <input class="form-control mt-3 d-none video" placeholder="Enter video data" id="video" name="video_${i}[]" type="text">


                <input class="form-control mt-3 d-none video_file" placeholder="Enter video data" id="video_file" name="video_file_${i}[]" type="file">
            @lang('labels.backend.lessons.video_guide')
            </div>
            <div class="form-group row">
            <label class="col-md-4 form-control-label" for="duration">Duration</label>

            <div class="col-md-8">
                <input class="form-control" type="text" name="duration[]" id="duration" placeholder="Duration [minutes]">
            </div>
        </div>
        
        <div class="form-group row start_date" style="">
            <label class="col-md-4 form-control-label" for="duration">Lesson Start Date</label>
            
            <div class="col-md-8">
                <input class="form-control date custom-date-picker" type="date" name="lesson_start_date" id="lesson_start_date" placeholder="12/09/2022">
            </div>
        </div>
        <div class="col-md-4" style="">
            <div class="checkbox">
                        <input name="published" type="hidden" value="0">
                        <input name="published" type="checkbox" value="1">
                        <label for="published" class="checkbox control-label font-weight-bold">Published</label>
            </div>
        </div>
                            <i class="fa fa-times position-absolute remove_less_slug" onclick="removeLesslug(this)" style="top:0px; right:27px; color:red;font-size:1rem;"  aria-hidden="true"></i>
                        </div>`;


        $(".mo_create").append(html);
        // }

    });
    function removeLesslug(dis) {
        $(dis).parent('div').remove();
    }

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