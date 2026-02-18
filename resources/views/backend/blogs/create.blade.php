@extends('backend.layouts.app')
@section('title', __('labels.backend.blogs.title').' | '.app_name())

@push('after-styles')
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.css')}}">
    <style>
        .select2-container--default .select2-selection--single {
            border: 1px solid rgb(228, 231, 234);
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
    <form method="POST" action="{{ route('admin.blogs.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="pb-3 d-flex justify-content-between align-items-center">
        <h4 >@lang('labels.backend.blogs.create')</h4>
        <div >
            <a href="{{ route('admin.blogs.index') }}"
               class="add-btn">@lang('labels.backend.blogs.view')</a>
        </div>
    </div>
    <div class="card">

        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-6 form-group">
                    <label for="title" class="control-label">{{ trans('labels.backend.blogs.fields.title') }}</label>
                    <input class="form-control" placeholder="{{ trans('labels.backend.blogs.fields.title') }}" name="title" type="text" value="{{ old('title') }}" id="title">
                </div>

                <div class="col-12 col-lg-6 form-group">
                    <label for="category" class="control-label">{{ trans('labels.backend.blogs.fields.category') }}</label>
                    <select class="form-control select2" name="category" id="category">
                        @foreach($category as $key => $value)
                            <option value="{{ $key }}" {{ (old('category') == $key) ? 'selected' : '' }}>{{ $value }}</option>
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
                    <input class="form-control" placeholder="{{ trans('labels.backend.lessons.slug_placeholder') }}" name="slug" type="text" value="{{ old('slug') }}" id="slug">

                </div>
                <div class="col-12 col-lg-6 form-group">
                    <label for="featured_image" class="control-label">{{ trans('labels.backend.blogs.fields.featured_image').' '.trans('labels.backend.blogs.max_file_size') }}</label>

                                                               <div class="custom-file-upload-wrapper">
    <input type="file" name="image" id="customFileInput" class="custom-file-input">
    <label for="customFileInput" class="custom-file-label">
        <i class="fa fa-upload mr-1"></i> Choose a file
    </label>
</div>

                </div>
            </div>

            <div class="row">
                <div class="col-12 form-group">
                    <label for="content" class="control-label">{{ trans('labels.backend.blogs.fields.content') }}</label>
                    <textarea class="form-control editor" placeholder="" id="editor" name="content" cols="50" rows="10">{{ old('content') }}</textarea>

                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group">
                    <input class="form-control" data-role="tagsinput" placeholder="{{ trans('labels.backend.blogs.fields.tags_placeholder') }}" id="tags" name="tags" type="text" value="{{ old('tags') }}">

                </div>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    <label for="meta_title" class="control-label">{{ trans('labels.backend.blogs.fields.meta_title') }}</label>
                    <input class="form-control" placeholder="{{ trans('labels.backend.blogs.fields.meta_title') }}" name="meta_title" type="text" value="{{ old('meta_title') }}" id="meta_title">

                </div>
                <div class="col-12 form-group">
                    <label for="meta_description" class="control-label">{{ trans('labels.backend.blogs.fields.meta_description') }}</label>
                    <textarea class="form-control" placeholder="{{ trans('labels.backend.blogs.fields.meta_description') }}" name="meta_description" cols="50" rows="10" id="meta_description">{{ old('meta_description') }}</textarea>
                </div>
                <div class="col-12 form-group">
                    <label for="meta_keywords" class="control-label">{{ trans('labels.backend.blogs.fields.meta_keywords') }}</label>
                    <textarea class="form-control" placeholder="{{ trans('labels.backend.blogs.fields.meta_keywords') }}" name="meta_keywords" cols="50" rows="10" id="meta_keywords">{{ old('meta_keywords') }}</textarea>
                </div>
            </div>
            <div class="row">

                <div class="col-md-12 d-flex justify-content-between">
                    <div>

                        <button type="reset" class="cancel-btn ">
                           {{trans('labels.backend.blogs.fields.clear')}}
                        </button>
                    </div>
                    <div>

                        <button type="submit" class="add-btn ">
                           {{trans('labels.backend.blogs.fields.publish')}}
                        </button>
                    </div>
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
        var uploadField = $('input[type="file"]');

        $(document).on('change','input[type="file"]',function () {
            var $this = $(this);
            $(this.files).each(function (key,value) {
                if((value.size/1024) > 10240){
                    alert('"'+value.name+'"'+'exceeds limit of maximum file upload size' )
                    $this.val("");
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
