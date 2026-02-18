@extends('backend.layouts.app')
@section('title', __('labels.backend.courses.title').' | '.app_name())

@section('content')

<form method="POST" action="{{ route('admin.courses.update', $course->id) }}" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4 class="">@lang('labels.backend.courses.edit')</h4>
    <div class="">
        <a href="{{ route('admin.courses.index') }}" class="btn add-btn">@lang('labels.backend.courses.view')</a>
    </div>
</div>
<div class="card">

    <div class="card-body">

        @if (Auth::user()->isAdmin())
        <div class="row">

            <div class="col-10 form-group">
                <label for="teachers" class="control-label">{{ trans('labels.backend.courses.fields.teachers') }}</label>
                <select class="form-control select2" multiple="multiple" required="required" name="teachers[]">
                    @foreach($teachers as $id => $teacher)
                        <option value="{{ $id }}" {{ in_array($id, (old('teachers') ? old('teachers') : $course->teachers->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $teacher }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-2 d-flex form-group flex-column">
                OR <a target="_blank" class="btn btn-primary mt-auto" href="{{route('admin.teachers.create')}}">{{trans('labels.backend.courses.add_teachers')}}</a>
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-10 form-group">
                <label for="category_id" class="control-label">{{ trans('labels.backend.courses.fields.category') }}</label>
                <select class="form-control select2 js-example-placeholder-single" required="required" name="category_id">
                    @foreach($categories as $id => $category)
                        <option value="{{ $id }}" {{ ($id == old('category_id', $course->category_id)) ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-2 d-flex form-group flex-column">
                OR <a target="_blank" class="btn btn-primary mt-auto" href="{{route('admin.categories.index').'?create'}}">{{trans('labels.backend.courses.add_categories')}}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-6 form-group">
                <label for="title" class="control-label">{{ trans('labels.backend.courses.fields.title') }} *</label>
                <input class="form-control" placeholder="" required="" name="title" type="text" value="{{ old('title', $course->title) }}">
            </div>
            <div class="col-12 col-lg-6 form-group">
                <label for="slug" class="control-label">{{ trans('labels.backend.courses.fields.slug') }}</label>
                <input class="form-control" placeholder="{{ trans('labels.backend.courses.slug_placeholder') }}" name="slug" type="text" value="{{ old('slug', $course->slug) }}">
            </div>

        </div>

        <div class="row">
            <div class="col-12 form-group">
                <label for="description" class="control-label">{{ trans('labels.backend.courses.fields.description') }}</label>
                <textarea class="form-control editor" placeholder="{{ trans('labels.backend.courses.fields.description') }}" name="description" cols="50" rows="10">{{ old('description', $course->description) }}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-4 form-group">
                <label for="price" class="control-label">{{ trans('labels.backend.courses.fields.price').' (in '.$appCurrency["symbol"].')' }}</label>
                <input class="form-control" placeholder="{{ trans('labels.backend.courses.fields.price') }}" step="any" pattern="[0-9]" name="price" type="number" value="{{ old('price', $course->price) }}">
            </div>
            {{-- <div class="col-12 col-lg-4 form-group">
                    {!! Form::label('strike', trans('labels.backend.courses.fields.strike').' (in '.$appCurrency["symbol"].')', ['class' => 'control-label']) !!}
                    {!! Form::number('strike', old('strike'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.courses.fields.strike') ,'step' => 'any', 'pattern' => "[0-9]"]) !!}
                </div> --}}
            <div class="col-12 col-lg-4 form-group">

                <label for="course_image" class="control-label">{{ trans('labels.backend.courses.fields.course_image') }}</label>
                <input class="form-control" name="course_image" type="file" accept="image/jpeg,image/gif,image/png">
                <input name="course_image_max_size" type="hidden" value="8">
                <input name="course_image_max_width" type="hidden" value="4000">
                <input name="course_image_max_height" type="hidden" value="4000">
                @if ($course->course_image)
                <a href="{{ asset('storage/uploads/'.$course->course_image) }}" target="_blank"><img height="50px" src="{{ asset('storage/uploads/'.$course->course_image) }}" class="mt-1"></a>
                @endif
            </div>
            <div class="col-12 col-lg-4 form-group">
                <label for="start_date" class="control-label">{{ trans('labels.backend.courses.fields.start_date').' (yyyy-mm-dd)' }}</label>
                <input class="form-control date" pattern="(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))" placeholder="{{ trans('labels.backend.courses.fields.start_date').' (Ex . 2019-01-01)' }}" name="start_date" type="text" value="{{ old('start_date', $course->start_date) }}">
                <p class="help-block"></p>
                @if($errors->has('start_date'))
                <p class="help-block">
                    {{ $errors->first('start_date') }}
                </p>
                @endif
            </div>
            @if (Auth::user()->isAdmin())
            <div class="col-12 col-lg-4 form-group">
                <label for="expire_at" class="control-label">{{ trans('labels.backend.courses.fields.expire_at').' (yyyy-mm-dd)' }}</label>
                <input class="form-control date" pattern="(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))" placeholder="{{ trans('labels.backend.courses.fields.expire_at').' (Ex . 2019-01-01)' }}" autocomplete="off" name="expire_at" type="text" value="{{ old('expire_at', $course->expire_at) }}">
                <p class="help-block"></p>
                @if($errors->has('expire_at'))
                <p class="help-block">
                    {{ $errors->first('expire_at') }}
                </p>
                @endif
            </div>
            @endif


        </div>
        <div class="row">
            <label class="col-md-2 form-control-label" for="first_name">Select Department</label>

            <div class="col-md-10">
                <select name="department_id" class="form-control">
                    <option value=""> Select One </option>
                    @foreach($departments as $row)
                    @if(isset($course->department_id) && $row->id == $course->department_id)
                    <?php
                    $sel = 'selected';
                    ?>
                    @else
                    <?php
                    $sel = '';
                    ?>
                    @endif
                    <option <?php echo $sel ?> value="{{ $row->id }}"> {{ $row->title }} </option>
                    @endforeach
                </select>
            </div>
            <!--col-->
        </div>
        <div class="row">
            <div class="col-md-12 form-group">
                <label for="add_video" class="control-label">{{ trans('labels.backend.lessons.fields.add_video') }}</label>
                <select class="form-control" id="media_type" name="media_type">
                    <option value="" selected="selected">Select One</option>
                    <option value="youtube" {{ (($course->mediavideo) ? $course->mediavideo->type : null) == 'youtube' ? 'selected' : '' }}>Youtube</option>
                    <option value="vimeo" {{ (($course->mediavideo) ? $course->mediavideo->type : null) == 'vimeo' ? 'selected' : '' }}>Vimeo</option>
                    <option value="upload" {{ (($course->mediavideo) ? $course->mediavideo->type : null) == 'upload' ? 'selected' : '' }}>Upload</option>
                    <option value="embed" {{ (($course->mediavideo) ? $course->mediavideo->type : null) == 'embed' ? 'selected' : '' }}>Embed</option>
                </select>


                <input class="form-control mt-3 d-none" placeholder="{{ trans('labels.backend.lessons.enter_video_url') }}" id="video" name="video" type="text" value="{{ ($course->mediavideo) ? $course->mediavideo->url : null }}">

                <input class="form-control mt-3 d-none" placeholder="{{ trans('labels.backend.lessons.enter_video_url') }}" id="video_file" accept="video/mp4" name="video_file" type="file">
                <input type="hidden" name="old_video_file" value="{{($course->mediavideo && $course->mediavideo->type == 'upload') ? $course->mediavideo->url  : ""}}">
                @if($course->mediavideo != null)
                <div class="form-group">
                    <a href="#" data-media-id="{{$course->mediaVideo->id}}" class="btn btn-xs btn-danger my-3 delete remove-file">@lang('labels.backend.lessons.remove')</a>
                </div>
                @endif



                @if($course->mediavideo && ($course->mediavideo->type == 'upload'))
                <video width="300" class="mt-2 d-none video-player" controls>
                    <source src="{{($course->mediavideo && $course->mediavideo->type == 'upload') ? $course->mediavideo->url  : ""}}" type="video/mp4">
                    Your browser does not support HTML5 video.
                </video>

                @endif

                @lang('labels.backend.lessons.video_guide')
            </div>
        </div>

        <div class="row">
            <div class="col-12 form-group">
                <div class="checkbox d-inline mr-4">
                    <input name="published" type="hidden" value="0">
                    <input name="published" type="checkbox" value="1" {{ old('published', $course->published) ? 'checked' : '' }}>
                    <label for="published" class="checkbox control-label font-weight-bold">{{ trans('labels.backend.courses.fields.published') }}</label>
                </div>

                @if (Auth::user()->isAdmin())

                <div class="checkbox d-inline mr-4">
                    <input name="featured" type="hidden" value="0">
                    <input name="featured" type="checkbox" value="1" {{ old('featured', $course->featured) ? 'checked' : '' }}>
                    <label for="featured" class="checkbox control-label font-weight-bold">{{ trans('labels.backend.courses.fields.featured') }}</label>
                </div>

                <div class="checkbox d-inline mr-4">
                    <input name="trending" type="hidden" value="0">
                    <input name="trending" type="checkbox" value="1" {{ old('trending', $course->trending) ? 'checked' : '' }}>
                    <label for="trending" class="checkbox control-label font-weight-bold">{{ trans('labels.backend.courses.fields.trending') }}</label>
                </div>

                <div class="checkbox d-inline mr-4">
                    <input name="popular" type="hidden" value="0">
                    <input name="popular" type="checkbox" value="1" {{ old('popular', $course->popular) ? 'checked' : '' }}>
                    <label for="popular" class="checkbox control-label font-weight-bold">{{ trans('labels.backend.courses.fields.popular') }}</label>
                </div>
                @endif
                <div class="checkbox d-inline mr-4">
                    <input name="free" type="hidden" value="0">
                    <input name="free" type="checkbox" value="1" {{ old('free', $course->free) ? 'checked' : '' }}>
                    <label for="free" class="checkbox control-label font-weight-bold">{{ trans('labels.backend.courses.fields.free') }}</label>
                </div>

            </div>
        </div>

        {{-- <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('meta_title',trans('labels.backend.courses.fields.meta_title'), ['class' => 'control-label']) !!}
                    {!! Form::text('meta_title', old('meta_title'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.courses.fields.meta_title')]) !!}

                </div>
                <div class="col-12 form-group">
                    {!! Form::label('meta_description',trans('labels.backend.courses.fields.meta_description'), ['class' => 'control-label']) !!}
                    {!! Form::textarea('meta_description', old('meta_description'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.courses.fields.meta_description')]) !!}
                </div>
                <div class="col-12 form-group">
                    {!! Form::label('meta_keywords',trans('labels.backend.courses.fields.meta_keywords'), ['class' => 'control-label']) !!}
                    {!! Form::textarea('meta_keywords', old('meta_keywords'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.courses.fields.meta_keywords')]) !!}
                </div>

            </div> --}}

        <div class="row">
            <div class="col-12  text-center form-group">
                <button class="btn btn-danger" type="submit">{{ trans('strings.backend.general.app_update') }}</button>
            </div>
        </div>
    </div>
</div>

</form>
@stop

@push('after-scripts')
<script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script>
<script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/adapters/jquery.js')}}"></script>
<script src="{{asset('/vendor/laravel-filemanager/js/lfm.js')}}"></script>
<script>
    $('.editor').each(function() {

        CKEDITOR.replace($(this).attr('id'), {
            filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
            filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token={{csrf_token()}}',
            filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
            filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token={{csrf_token()}}',
            extraPlugins: 'smiley,lineutils,widget,codesnippet,prism,flash,colorbutton,colordialog',
        });

    });

    $(document).ready(function() {
        $('#start_date').datepicker({
            autoclose: true,
            dateFormat: "{{ config('app.date_format_js') }}"
        });
        var dateToday = new Date();
        $('#expire_at').datepicker({
            autoclose: true,
            minDate: dateToday,
            dateFormat: "{{ config('app.date_format_js') }}"
        });

        $(".js-example-placeholder-single").select2({
            placeholder: "{{trans('labels.backend.courses.select_category')}}",
        });

        $(".js-example-placeholder-multiple").select2({
            placeholder: "{{trans('labels.backend.courses.select_teachers')}}",
        });
    });
    $(document).on('change', 'input[type="file"]', function() {
        var $this = $(this);
        $(this.files).each(function(key, value) {
            if (value.size > 50000000) {
                alert('"' + value.name + '"' + 'exceeds limit of maximum file upload size')
                $this.val("");
            }
        })
    });

    $(document).ready(function() {
        $(document).on('click', '.delete', function(e) {
            e.preventDefault();
            var parent = $(this).parent('.form-group');
            var confirmation = confirm("{{trans('strings.backend.general.are_you_sure')}}")
            if (confirmation) {
                var media_id = $(this).data('media-id');
                $.post("{{route('admin.media.destroy')}}", {media_id: media_id,_token: '{{csrf_token()}}'},
                    function(data, status) {
                        if (data.success) {
                            parent.remove();
                            $('#video').val('').addClass('d-none').attr('required', false);
                            $('#video_file').attr('required', false);
                            $('#media_type').val('');
                            @if ($course->mediavideo && $course->mediavideo->type == 'upload')
                            $('.video-player').addClass('d-none');
                            $('.video-player').empty();
                            @endif


                        } else {
                            alert('Something Went Wrong')
                        }
                    });
            }
        })
    });


    @if($course->mediavideo)
    @if($course->mediavideo->type != 'upload')
    $('#video').removeClass('d-none').attr('required', true);
    $('#video_file').addClass('d-none').attr('required', false);
    $('.video-player').addClass('d-none');
    @elseif($course->mediavideo->type == 'upload')
    $('#video').addClass('d-none').attr('required', false);
    $('#video_file').removeClass('d-none').attr('required', false);
    $('.video-player').removeClass('d-none');
    @else
    $('.video-player').addClass('d-none');
    $('#video_file').addClass('d-none').attr('required', false);
    $('#video').addClass('d-none').attr('required', false);
    @endif
    @endif

    $(document).on('change', '#media_type', function() {
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

@endpush