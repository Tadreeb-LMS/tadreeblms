@extends('backend.layouts.app')
@section('title', __('labels.backend.lessons.title').' | '.app_name())

@push('after-styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/jquery.datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="{{asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.css')}}">
<style>
    .lesson-box{
    border: 1px solid #e4e6ef;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 25px;   /* space between lessons */
    background: #fff;
    box-shadow: 0 3px 10px rgba(0,0,0,0.04);
    position: relative;
}
.remove_less_slug{
    position: absolute;
    top: -12px;
    right: -12px;
    background: #fff;
    border-radius: 50%;
    color: red;
    font-size: 10px;      /* ⬅ Bigger ❌ icon */
    padding: 2px;
    font-weight: bold;   /* ⬅ Thicker */
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
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


@if($courses_all)
<input type="hidden" name="category_id" value="{{ $courses_all }}" id="category_id">

@endif
<div class="pb-3 d-flex justify-content-between align-items-center addcourseheader">
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
        <div class = "lesson-template ">
            <div class="position-relative lesson-box">

    <i class="fa fa-times remove_less_slug"
   onclick="removeLesslug(this)"
   style="position:absolute; top:-10px; right:-10px; color:red; font-size:18px; cursor:pointer; display:none;"
   title="Remove Lesson"></i>

        <div class="row">
            <div class="col-md-6">
       
            <div class="form-group">
                <div for="course_id" class="form-control-label">{{ trans('labels.backend.lessons.fields.course') }}</div>
                <div class="mt-2 custom-select-wrapper">

                    <select id="course_id" name="course_id" class="form-control custom-select-box course_id select2">

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
            <div class="form-group">
                <div for="lesson_image" class="control-label mb-2">
                    {{ trans('labels.backend.lessons.fields.lesson_image') }} {{ trans('labels.backend.lessons.max_file_size') }}
                </div>
                <div class="custom-file-upload-wrapper">
                            <input type="file" class="custom-file-input">
            <label class="custom-file-label">
                            <i class="fa fa-upload mr-1"></i> Choose a file
                            </label>
                        </div>
                <!-- <input type="file" name="lesson_image[]" class="form-control" accept="image/jpeg,image/gif,image/png" />
                <input type="hidden" name="lesson_image_max_size" value="8" />
                <input type="hidden" name="lesson_image_max_width" value="4000" />
                <input type="hidden" name="lesson_image_max_height" value="4000" /> -->
                

            </div>
<div class="ltitle">
                
            <label for="title" class="control-label">
                {{ trans('labels.backend.lessons.fields.title') }} *
            </label>
            <input type="text" name="title[]" value="{{ old('title') }}" class="form-control" placeholder="{{ trans('labels.backend.lessons.fields.title') }}" required />
           </div> 
            <div class="shortext">
                <label for="short_text" class="control-label">
                    {{ trans('labels.backend.lessons.fields.short_text') }}
                </label>
                <textarea name="short_text[]" class="form-control" placeholder="{{ trans('labels.backend.lessons.short_description_placeholder') }}" style="height: 100px;">{{ old('short_text') }}</textarea> 
            </div>
     
        </div>
<div class="col-md-6">
<div class="form-group notextarea fillh180">
                <label for="full_text" class="control-label">
                    {{ trans('labels.backend.lessons.fields.full_text') }}
                </label>
                <textarea name="full_text[]" class="form-control editor" placeholder="" id="editor">
                    {{ old('full_text') }}
                </textarea>
                

            </div>




 </div>
  </div>


  <div class="row mt-1"> <div class="col-md-4">
    <div class="form-group ">
                <div for="downloadable_files" class="control-label mb-2">
                    {{ trans('labels.backend.lessons.fields.downloadable_files') }} {{ trans('labels.backend.lessons.max_file_size') }}
                </div>
                
                <div class="custom-file-upload-wrapper">
                            <input type="file" name="downloadable_files_1[]" id="customFileInput" class="custom-file-input">
                            <label for="customFileInput" class="custom-file-label">
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
                            <input type="file" name="add_pdf_1[]" id="customFileInput" class="custom-file-input">
                            <label for="customFileInput" class="custom-file-label">
                            <i class="fa fa-upload mr-1"></i> Choose a file
                            </label>
                        </div>
                
            </div></div>
            <div class="col-md-4">
<div class="form-group ">
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
  </div>


        <div class="row">
        

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

 


        <div class="row addvideocol">
            <div class="col-md-4 form-group parent_group mt-2">
                <label for="add_video" class="control-label blocklabel">
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
                
                
                

            </div>
            <div class="col-md-8 mt-2">
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
                    <input class="form-control" type="date" name="lesson_start_date" id="lesson_start_date">
                </div>
                
            </div>
            <div class="col-md-4 col-sm-12">
            <div class="checkbox" style="margin-top: 37px;">
                <input type="hidden" name="published" value="0">
                <input type="checkbox" name="published" value="1" id="published" class="checkbox">
                <label for="published" class="checkbox control-label font-weight-bold">
                    {{ trans('labels.backend.lessons.fields.published') }}
                </label>

            </div>
                    </div>
        </div>

        </div>
</div>

        <div class="row">
            
        </div>
        <div class="mo_create"></div>

        <div class="btmbtns">
        <div class="d-flex justify-content-between">
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

       /* $('#lesson_start_date').datetimepicker({
    format: 'Y-m-d',
    timepicker: false
}); */

        $('.custom-date-picker').datetimepicker({
            format: 'Y-m-d',
            timepicker: false
        });
    });

    

    var uploadField = $('input[type="file"]');

    $(document).on('change', 'input[name="lesson_image"]', function() {
        var $this = $(this);
        $(this.files).each(function(key, value) {
            if (value.size > 50000000) { // 50MB
                alert('"' + value.name + '"' + ' exceeds limit of maximum file upload size (50MB)')
                $this.val("");
            }
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
    $("#addmorebtn").on('click', function () {

    let clone = $('.lesson-template').first().clone(false);

    clone.find('input, textarea').val('');
    clone.find('input[type="checkbox"]').prop('checked', false);

    clone.find('.video, .video_file').addClass('d-none').prop('required', false);
    clone.find('.media_type').val('');

    clone.find('[id]').removeAttr('id');

    // show delete icon on clones
    clone.find('.remove_less_slug').show();

    $(".mo_create").append(clone);

    clone.find('.editor').each(function () {
        CKEDITOR.replace(this);
    });
});


    function removeLesslug(el) {
    let box = $(el).closest('.lesson-box').parent();

    // destroy CKEditor instance inside before removing
    box.find('.editor').each(function () {
        let name = this.name;
        if (CKEDITOR.instances[name]) {
            CKEDITOR.instances[name].destroy(true);
        }
    });

    box.remove();
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


