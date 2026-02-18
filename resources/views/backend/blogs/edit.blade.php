@extends('backend.layouts.app')
@section('title', __('labels.backend.blogs.title').' | '.app_name())

@push('after-styles')
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
        .bootstrap-tagsinput{
            width: 100%!important;
            display: inline-block;
        }
        .bootstrap-tagsinput .tag{
            line-height: 1;
            margin-right: 2px;
            background-color: #2f353a ;
            color: white;
            padding: 3px;
            border-radius: 3px;
        }
        .select2-container .select2-selection--single .select2-selection__arrow {
    display: none !important;
}

    </style>

@endpush

@section('content')
    <form method="POST" action="{{ route('admin.blogs.update', $blog->id) }}" enctype="multipart/form-data">
    @method('PUT')
    @csrf

    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">@lang('labels.backend.blogs.edit')</h3>
            <div class="float-right">
                <a href="{{ route('admin.blogs.index') }}"
                   class="btn btn-success">@lang('labels.backend.blogs.view')</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-6 form-group">
                    <label for="title" class="control-label">{{ trans('labels.backend.blogs.fields.title') }}</label>
                    <input class="form-control" placeholder="{{ trans('labels.backend.blogs.fields.title') }}" name="title" type="text" value="{{ old('title', $blog->title) }}" id="title">
                </div>

                <div class="col-12 col-lg-6 form-group">
                    <label for="category" class="control-label">{{ trans('labels.backend.blogs.fields.category') }}</label>
                    <select class="form-control select2" name="category" id="category">
                        @foreach($category as $key => $value)
                            <option value="{{ $key }}" {{ (old('category', $blog->category_id) == $key) ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                           <span class="custom-select-icon" style="right: 23px; top:45px">
            <i class="fa fa-chevron-down"></i>
        </span>
                </div>

            </div>


            <div class="row">
                <div class="col-12 col-lg-6 form-group">
                    <label for="slug" class="control-label">{{ trans('labels.backend.blogs.fields.slug') }}</label>
                    <input class="form-control" placeholder="{{ trans('labels.backend.blogs.slug_placeholder') }}" name="slug" type="text" value="{{ old('slug', $blog->slug) }}" id="slug">
                </div>
                @if ($blog->image)
                    <div class="col-12 col-lg-5 form-group">
                        <label for="featured_image" class="control-label">{{ trans('labels.backend.blogs.fields.featured_image').' '.trans('labels.backend.blogs.max_file_size') }}</label>

                                                                   <div class="custom-file-upload-wrapper">
    <input type="file" name="image" id="customFileInput" class="custom-file-input">
    <label for="customFileInput" class="custom-file-label">
        <i class="fa fa-upload mr-1"></i> Choose a file
    </label>
</div>
                    </div>
                    <div class="col-lg-1 col-12 form-group">
                        <a href="{{ asset('storage/uploads/'.$blog->image) }}" target="_blank"><img
                                    src="{{ asset('storage/uploads/'.$blog->image) }}" height="65px"
                                    width="65px"></a>
                    </div>
                @else
                    <div class="col-12 col-lg-6 form-group">

                        <label for="featured_image" class="control-label">{{ trans('labels.backend.blogs.fields.featured_image').' '.trans('labels.backend.blogs.max_file_size') }}</label>
                        <input class="form-control" name="featured_image" type="file" id="featured_image">
                        <input name="featured_image_max_size" type="hidden" value="8">
                        <input name="featured_image_max_width" type="hidden" value="4000">
                        <input name="featured_image_max_height" type="hidden" value="4000">
                    </div>
                @endif

            </div>


            <div class="row">
                <div class="col-12 form-group">
                    <label for="content" class="control-label">{{ trans('labels.backend.blogs.fields.content') }}</label>
                    <textarea class="form-control editor" placeholder="" id="editor" name="content" cols="50" rows="10">{{ old('content', $blog->content) }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 form-group">
                    <input class="form-control" data-role="tagsinput" placeholder="{{ trans('labels.backend.blogs.fields.tags_placeholder') }}" id="tags" name="tags" type="text" value="{{ $tags }}">

                </div>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    <label for="meta_title" class="control-label">{{ trans('labels.backend.blogs.fields.meta_title') }}</label>
                    <input class="form-control" placeholder="{{ trans('labels.backend.blogs.fields.meta_title') }}" name="meta_title" type="text" value="{{ old('meta_title', $blog->meta_title) }}" id="meta_title">

                </div>
                <div class="col-12 form-group">
                    <label for="meta_description" class="control-label">{{ trans('labels.backend.blogs.fields.meta_description') }}</label>
                    <textarea class="form-control" placeholder="{{ trans('labels.backend.blogs.fields.meta_description') }}" name="meta_description" cols="50" rows="10" id="meta_description">{{ old('meta_description', $blog->meta_description) }}</textarea>
                </div>
                <div class="col-12 form-group">
                    <label for="meta_keywords" class="control-label">{{ trans('labels.backend.blogs.fields.meta_keywords') }}</label>
                    <textarea class="form-control" placeholder="{{ trans('labels.backend.blogs.fields.meta_keywords') }}" name="meta_keywords" cols="50" rows="10" id="meta_keywords">{{ old('meta_keywords', $blog->meta_keywords) }}</textarea>
                </div>
            </div>
            <div class="row">

            <div class="col-md-12 d-flex justify-content-between">
                <div class="mt-2">

                    <a href="{{route('admin.blogs.index')}}" class="cancel-btn">
                        {{trans('strings.backend.general.app_back_to_list')}}
                    </a>
                </div>
                <div>

                    <button type="submit" class="add-btn">
                        {{trans('labels.general.buttons.update')}}
                    </button>
                </div>
                </div>
            </div>
        </div>
    </div>
    </form>

@endsection


@push('after-scripts')
    <script src="{{asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.js')}}"></script>

    <script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script>
    <script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/adapters/jquery.js')}}"></script>
    <script src="{{asset('/vendor/laravel-filemanager/js/lfm.js')}}"></script>
    <script>
        $('.editor').each(function () {

            CKEDITOR.replace($(this).attr('id'), {
                filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
                filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token={{csrf_token()}}',
                filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
                filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token={{csrf_token()}}',

                extraPlugins: 'smiley,lineutils,widget,codesnippet,prism,flash',
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
                            }else{
                                alert('Something Went Wrong')
                            }
                        });
                }
            })
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
