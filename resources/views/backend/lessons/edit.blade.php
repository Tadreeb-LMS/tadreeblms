@extends('backend.layouts.app')
@section('title', __('labels.backend.lessons.title').' | '.app_name())

@push('after-styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/jquery.datetimepicker.min.css" />
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.css')}}">
    <style>
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

    </style>

@endpush
@section('content')
    <form method="POST" action="{{ route('admin.lessons.update', $lesson->id) }}" enctype="multipart/form-data" autocomplete="off">
    @csrf
    @method('PUT')

    <div class="pb-3 d-flex justify-content-between align-items-center">
        <h4 class="">@lang('labels.backend.lessons.edit')</h4>
        <div class="">
            <a href="{{ route('admin.lessons.index') }}"
               class="btn btn-primary">@lang('labels.backend.lessons.view')</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 col-lg-6 form-group">
                  <div for="course_id" class="form-control-label">
        {{ trans('labels.backend.lessons.fields.course') }}
    </div>
    
    <div class="mt-2 custom-select-wrapper">
        <select name="course_id" id="course_id" class="form-control custom-select-box select2">
            <option value="">Select Course</option>
            @foreach($courses as $key => $course)
                <option value="{{ $key }}" {{ (old('course_id') == $key || request('course_id') == $key || $lesson->course_id == $key) ? 'selected' : '' }}>
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
                    <label for="title" class="control-label">{{ trans('labels.backend.lessons.fields.title') }}*</label>
                    <input type="text" name="title" value="{{ old('title', $lesson->title) }}" class="form-control" placeholder="{{ trans('labels.backend.lessons.fields.title') }}" required>

                </div>

                {{-- <div class="col-12 col-lg-6 form-group">
                
                    <label for="title" class="control-label">
                        {{ trans('Arabic Title') }} *
                    </label>
                    <input type="text" name="arabic_title[]" value="{{ old('arabic_title') }}" class="form-control" placeholder="{{ trans('Arabic Title') }}" required />
                    
                
                </div> --}}

            </div>

            <div class="row">
                <div class="col-md-12 col-lg-6 form-group">
                    <label for="slug" class="control-label">{{ trans('labels.backend.lessons.fields.slug') }}</label>
                    <input type="text" name="slug" value="{{ old('slug', $lesson->slug) }}" class="form-control" placeholder="{{ trans('labels.backend.lessons.slug_placeholder') }}">
                </div>
                @if ($lesson->lesson_image)

                    <div class="col-md-12 col-lg-5 form-group">

                        <label for="lesson_image" class="control-label">{{ trans('labels.backend.lessons.fields.lesson_image').' '.trans('labels.backend.lessons.max_file_size') }}</label>
                        <input type="file" name="lesson_image" class="form-control" accept="image/jpeg,image/gif,image/png" style="margin-top: 4px;">
                        <input type="hidden" name="lesson_image_max_size" value="8">
                        <input type="hidden" name="lesson_image_max_width" value="4000">
                        <input type="hidden" name="lesson_image_max_height" value="4000">
                    </div>
                    <div class="col-lg-1 col-12 form-group">
                        <a href="{{ asset('uploads/'.$lesson->lesson_image) }}" target="_blank"><img
                                    src="{{ asset('uploads/'.$lesson->lesson_image) }}" height="65px"
                                    width="65px"></a>
                    </div>
                @else
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
                    </div>
                @endif

            </div>

            <div class="row">
                <div class="col-12 form-group">
                    <label for="short_text" class="control-label">{{ trans('labels.backend.lessons.fields.short_text') }}</label>
                    <textarea name="short_text" class="form-control" placeholder="{{ trans('labels.backend.lessons.short_description_placeholder') }}">{{ old('short_text', $lesson->short_text) }}</textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                     <label for="full_text" class="control-label">{{ trans('labels.backend.lessons.fields.full_text') }}</label>
                     <textarea name="full_text" id="full_text" class="form-control" rows="10" placeholder="">{{ old('full_text', $lesson->full_text) }}</textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    <label for="downloadable_files" class="control-label">{{ trans('labels.backend.lessons.fields.downloadable_files').' '.trans('labels.backend.lessons.max_file_size') }}</label>
                     <div class="custom-file-upload-wrapper">
                            <input type="file" name="image" id="customFileInput" class="custom-file-input">
                            <label for="customFileInput" class="custom-file-label">
                            <i class="fa fa-upload mr-1"></i> Choose a file
                            </label>
                        </div>
                    {{-- {!! Form::file('downloadable_files[]', [
                        'multiple',
                        'class' => 'form-control file-upload',
                         'id' => 'downloadable_files',
                        'accept' => "image/jpeg,image/gif,image/png,application/msword,audio/mpeg,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-powerpoint,application/pdf,video/mp4", 'style' => 'padding: 3px;'

                        ]) !!} --}}
                    <div class="photo-block mt-3">
                        <div class="files-list">
                            @if(count($lesson->media) > 0)
                                @foreach($lesson->media as $media)
                                        @if($media->type == 'download_file')
                                            <p class="form-group">
                                                <a download href="{{ $media->url }}"
                                                target="_blank">{{ $media->file_name }}
                                                    ({{ $media->size }} KB)</a>
                                                <a href="#" data-media-id="{{$media->id}}"
                                                class="btn btn-xs btn-danger delete remove-file">@lang('labels.backend.lessons.remove')</a>
                                            </p>
                                        @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    <label for="pdf_files" class="control-label">{{ trans('labels.backend.lessons.fields.add_pdf') }}</label>
                    <div class="custom-file-upload-wrapper">
                            <input type="file" name="image" id="customFileInput" class="custom-file-input">
                            <label for="customFileInput" class="custom-file-label">
                            <i class="fa fa-upload mr-1"></i> Choose a file
                            </label>
                        </div>
                    <div class="photo-block mt-3">
                        <div class="files-list">
                            @if($lesson->media)
                                {{-- {{ dd($lesson->media) }} --}}
                                <p class="form-group">
                                    {{-- <a href="{{ asset('storage/uploads/'.$lesson->media->name) }}"
                                       target="_blank">{{ $lesson->media?->name }}
                                        ({{ $lesson->media->size }} KB)</a> --}}
                                    {{-- <a href="#" data-media-id="{{$lesson->media->id}}"
                                       class="btn btn-xs btn-danger delete remove-file">@lang('labels.backend.lessons.remove')</a> --}}
                                    @foreach($lesson->media as $media)
                                        @if($media->type == 'lesson_pdf')
                                        <iframe src="{{ $media->url }}" width="100%" height="500px"></iframe>
                                        @endif
                                    @endforeach
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 form-group">
                    <label for="pdf_files" class="control-label">{{ trans('labels.backend.lessons.fields.add_audio') }}</label>
                   <div class="custom-file-upload-wrapper">
                            <input type="file" name="image" id="customFileInput" class="custom-file-input">
                            <label for="customFileInput" class="custom-file-label">
                            <i class="fa fa-upload mr-1"></i> Choose a file
                            </label>
                        </div>
                    <div class="photo-block mt-3">
                        <div class="files-list">
                            @if($lesson->media)
                                    @foreach($lesson->media as $media)
                                        @if($media->type == 'lesson_audio')
                                            <p class="form-group">
                                                <a href="{{ $media->url }}"
                                                target="_blank">{{ $media->file_name }}
                                                    ({{ $media->size }} KB)</a>
                                                <a href="#" data-media-id="{{$media->id}}"
                                                class="btn btn-xs btn-danger delete remove-file">@lang('labels.backend.lessons.remove')</a>
                                                <audio id="player" controls>
                                                    <source src="{{ $media->url }}" type="audio/mp3" />
                                                </audio>
                                            </p>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group">
                     <label for="add_video" class="control-label">{{ trans('labels.backend.lessons.fields.add_video') }}</label>
                     
                     <select class="form-control" placeholder="Select One" id="media_type" name="media_type">
                        <option value="" disabled {{ $lesson->mediavideo ? '' : 'selected' }}>Select One</option>
                        <option value="youtube" {{ ($lesson->mediavideo && $lesson->mediavideo->type == 'youtube') ? 'selected' : '' }}>Youtube</option>
                        <option value="vimeo" {{ ($lesson->mediavideo && $lesson->mediavideo->type == 'vimeo') ? 'selected' : '' }}>Vimeo</option>
                        <option value="upload" {{ ($lesson->mediavideo && $lesson->mediavideo->type == 'upload') ? 'selected' : '' }}>Upload</option>
                        <option value="embed" {{ ($lesson->mediavideo && $lesson->mediavideo->type == 'embed') ? 'selected' : '' }}>Embed</option>
                     </select>


                    <input class="form-control mt-3 d-none" placeholder="{{ trans('labels.backend.lessons.enter_video_url') }}" id="video" name="video" type="text" value="{{ ($lesson->mediavideo) ? $lesson->mediavideo->url : null }}">



                    <input class="form-control mt-3 d-none" placeholder="{{ trans('labels.backend.lessons.enter_video_url') }}" id="video_file" accept="video/mp4" style="padding: 3px;" name="video_file" type="file">

                    <input type="hidden" name="old_video_file"
                           value="{{($lesson->mediavideo && $lesson->mediavideo->type == 'upload') ? $lesson->mediavideo->url  : ""}}">

                    
                      
                    @if(isset($lesson->mediavideo) && $lesson->mediavideo->type == 'youtube' )
                        <iframe width="300" height="200"
                            src="{{ $lesson->mediavideo->embed_url  }}"
                            title="YouTube video player"
                            frameborder="0"
                           >
                        </iframe>
                    @endif

                    @if($lesson->mediavideo && ($lesson->mediavideo->type == 'upload'))
                        <video width="300" class="mt-2 d-none video-player" controls>
                            <source src="{{($lesson->mediavideo && $lesson->mediavideo->type == 'upload') ? $lesson->mediavideo->url  : ""}}"
                                    type="video/mp4">
                            Your browser does not support HTML5 video.
                        </video>
                    @endif


                    @lang('labels.backend.lessons.video_guide')
                </div>
            </div>
            <div class="form-group row">
                <div class="col-lg-4 col-md-12">
                     <label for="duration" class="form-control-label">Duration</label>

                    <div class="">
                       <input class="form-control" placeholder="Duration [minutes]" name="duration" type="text" value="{{ old('duration', $lesson->duration) }}">
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                     <label for="lesson_start_date" class="form-control-label">Lesson Start Date</label>

                    <div class="">
                       
                       <input type="date" value="{{ !empty($lesson->lesson_start_date) ? date('Y-m-d',strtotime($lesson->lesson_start_date)) : '' }}" class="form-control" id="lesson_start_date" name="lesson_start_date"  >
                    </div>
                </div>

                <div class="col-lg-4 col-md-12  form-group" style="margin-top: 30px;">
                    <input type="hidden" name="published" value="0">
                    <input type="checkbox" name="published" value="1" {{ old('published', $lesson->published) ? 'checked' : '' }}>
                    <label for="published" class="control-label control-label font-weight-bold">{{ trans('labels.backend.lessons.fields.published') }}</label>
                </div>
            </div>

            

            <div class="row">
                <div class="col-12  text-right  form-group " >
                    <button type="submit" class="btn btn-primary pl-4 pr-4">{{ trans('strings.backend.general.app_update') }}</button>
                </div>
            </div>
        </div>
    </div>
    </form>
@stop

@push('after-scripts')
    <script src="{{asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js"></script>
    <script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script>
    <script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/adapters/jquery.js')}}"></script>
    <script src="{{asset('/vendor/laravel-filemanager/js/lfm.js')}}"></script>
    <script>
        $(document).ready(function () {
            //$.datetimepicker.setLocale('pt-BR');
            //$('#datetimepicker').datetimepicker();
           /* $('#lesson_start_date').datetimepicker({
                format:'Y-m-d H:00',
           }); */
          
       });

        $('.editor').each(function () {

            CKEDITOR.replace(this, {
                filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
                filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token={{csrf_token()}}',
                filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
                filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token={{csrf_token()}}',

                extraPlugins: 'smiley,lineutils,widget,codesnippet,prism,flash,colorbutton,colordialog',
            });

        });
        $(document).ready(function () {
            $(document).on('click', '.delete', function (e) {
                e.preventDefault();
                var parent = $(this).parent('.form-group');
                var confirmation = confirm('{{trans('strings.backend.general.are_you_sure')}}')
                if (confirmation) {
                    var media_id = $(this).data('media-id');
                    $.post('{{route('admin.media.destroy')}}', {media_id: media_id, _token: '{{csrf_token()}}'},
                        function (data, status) {
                            if (data.success) {
                                parent.remove();
                            } else {
                                alert('Something Went Wrong')
                            }
                        });
                }
            })
        });

        var uploadField = $('input[type="file"]');


        $(document).on('change', 'input[name="lesson_image"]', function () {
            var $this = $(this);
            $(this.files).each(function (key, value) {
                // if (value.size > 5000000) {
                //     alert('"' + value.name + '"' + 'exceeds limit of maximum file upload size')
                //     $this.val("");
                // }
            })
        });

        @if($lesson->mediavideo)
        @if($lesson->mediavideo->type !=  'upload')
        $('#video').removeClass('d-none').attr('required', true);
        $('#video_file').addClass('d-none').attr('required', false);
        $('.video-player').addClass('d-none');
        @elseif($lesson->mediavideo->type == 'upload')
        $('#video').addClass('d-none').attr('required', false);
        $('#video_file').removeClass('d-none').attr('required', false);
        $('.video-player').removeClass('d-none');
        @else
        $('.video-player').addClass('d-none');
        $('#video_file').addClass('d-none').attr('required', false);
        $('#video').addClass('d-none').attr('required', false);
        @endif
        @endif
        @if($mediavideo)
        @if($mediavideo->type !=  'upload')
        $('#video').removeClass('d-none').attr('required', true);
        $('#video_file').addClass('d-none').attr('required', false);
        $('.video-player').addClass('d-none');
        @elseif($mediavideo->type == 'upload')
        $('#video').addClass('d-none').attr('required', false);
        $('#video_file').removeClass('d-none').attr('required', false);
        $('.video-player').removeClass('d-none');
        @else
        $('.video-player').addClass('d-none');
        $('#video_file').addClass('d-none').attr('required', false);
        $('#video').addClass('d-none').attr('required', false);
        @endif
        @endif
        $(document).on('change', '#media_type', function () {
            if ($(this).val()) {
                if ($(this).val() != 'upload') {
                    $('#video').removeClass('d-none').attr('required', true);
                    $('#video_file').addClass('d-none').attr('required', false);
                    $('.video-player').addClass('d-none')
                } else if ($(this).val() == 'upload') {
                    $('#video').addClass('d-none').attr('required', false);
                    $('#video_file').removeClass('d-none').attr('required', true);
                    $('.video-player').removeClass('d-none')
                }
            } else {
                $('#video_file').addClass('d-none').attr('required', false);
                $('#video').addClass('d-none').attr('required', false)
            }
        })

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
