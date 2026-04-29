@extends('backend.layouts.app')
@section('title', __('labels.backend.pages.title') . ' | ' . app_name())

@push('after-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.css') }}">
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
    {!! Form::model($page, ['method' => 'PUT', 'route' => ['admin.pages.update', $page->id], 'files' => true]) !!}

    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">@lang('labels.backend.pages.edit')</h3>
            <div class="float-right">
                <a href="{{ route('admin.pages.index') }}" class="btn btn-success">@lang('labels.backend.pages.view')</a>
            </div>
        </div>
        <div class="card-body">
            <div class="col-md-3 mb-4">
                <select name="lang" id="change-lang" class="form-control">
                    <option value="en" @if (request()->lang == 'en') selected @endif>{{ locale_label('en') }}</option>
                    <option value="ar" @if (request()->lang == 'ar') selected @endif>{{ locale_label('ar') }}</option>
                </select>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('title', trans('labels.backend.pages.fields.title'), ['class' => 'control-label']) !!}
                    {!! Form::text('title', old('title'), [
                        'class' => 'form-control',
                        'placeholder' => trans('labels.backend.pages.fields.title'),
                    ]) !!}
                </div>
            </div>


            <div class="row">
                <div class="col-12 col-lg-6 form-group">
                    {!! Form::label('slug', trans('labels.backend.pages.fields.slug'), ['class' => 'control-label']) !!}
                    {!! Form::text('slug', request()->slug, [
                        'class' => 'form-control',
                        'placeholder' => '',
                        'readonly' => true,
                    ]) !!}
                </div>
                @if ($page->image)
                    <div class="col-12 col-lg-5 form-group">
                        {!! Form::label(
                            'featured_image',
                            trans('labels.backend.pages.fields.featured_image') . ' ' . trans('labels.backend.pages.max_file_size'),
                            ['class' => 'control-label'],
                        ) !!}
                        {!! Form::file('featured_image', ['class' => 'form-control', 'accept' => 'image/jpeg,image/gif,image/png']) !!}
                        {!! Form::hidden('featured_image_max_size', 8) !!}
                        {!! Form::hidden('featured_image_max_width', 4000) !!}
                        {!! Form::hidden('featured_image_max_height', 4000) !!}
                    </div>
                    <div class="col-lg-1 col-12 form-group">
                        <a href="{{ asset( $page->image) }}" target="_blank"><img
                                src="{{ asset( $page->image) }}" height="65px" width="65px"></a>
                    </div>
                @else
                    <div class="col-12 col-lg-6 form-group">

                        {!! Form::label(
                            'featured_image',
                            trans('labels.backend.pages.fields.featured_image') . ' ' . trans('labels.backend.pages.max_file_size'),
                            ['class' => 'control-label'],
                        ) !!}
                        {!! Form::file('featured_image', ['class' => 'form-control']) !!}
                        {!! Form::hidden('featured_image_max_size', 8) !!}
                        {!! Form::hidden('featured_image_max_width', 4000) !!}
                        {!! Form::hidden('featured_image_max_height', 4000) !!}
                    </div>
                @endif

            </div>


            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('content', trans('labels.backend.pages.fields.content'), ['class' => 'control-label']) !!}
                    {!! Form::textarea('content', old('content'), [
                        'class' => 'form-control editor',
                        'placeholder' => '',
                        'id' => 'editor',
                    ]) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('meta_title', trans('labels.backend.pages.fields.meta_title'), ['class' => 'control-label']) !!}
                    {!! Form::text('meta_title', old('meta_title'), [
                        'class' => 'form-control',
                        'placeholder' => trans('labels.backend.pages.fields.meta_title'),
                    ]) !!}

                </div>
                <div class="col-12 form-group">
                    {!! Form::label('meta_description', trans('labels.backend.pages.fields.meta_description'), [
                        'class' => 'control-label',
                    ]) !!}
                    {!! Form::textarea('meta_description', old('meta_description'), [
                        'class' => 'form-control',
                        'placeholder' => trans('labels.backend.pages.fields.meta_description'),
                    ]) !!}
                </div>
                <div class="col-12 form-group">
                    {!! Form::label('meta_keywords', trans('labels.backend.pages.fields.meta_keywords'), [
                        'class' => 'control-label',
                    ]) !!}
                    {!! Form::textarea('meta_keywords', old('meta_keywords'), [
                        'class' => 'form-control',
                        'placeholder' => trans('labels.backend.pages.fields.meta_keywords'),
                    ]) !!}
                </div>
                <div class="col-12 form-group">
                    <div class="checkbox d-inline mr-4">
                        {!! Form::hidden('published', 0) !!}
                        {!! Form::checkbox('published', 1, old('published'), []) !!}
                        {!! Form::label('published', trans('labels.backend.pages.fields.published'), [
                            'class' => 'checkbox control-label font-weight-bold',
                        ]) !!}
                    </div>
                    <div class="checkbox d-inline mr-3">
                        {!! Form::hidden('sidebar', 0) !!}
                        {!! Form::checkbox('sidebar', 1, old('sidebar'), []) !!}
                        {!! Form::label('sidebar', trans('labels.backend.courses.fields.sidebar'), [
                            'class' => 'checkbox control-label font-weight-bold',
                        ]) !!}
                    </div>
                </div>

            </div>

            <div class="row">


                <div class="col-md-12 text-center form-group">
                    <button type="submit" class="btn btn-info waves-effect waves-light ">
                        {{ trans('labels.general.buttons.update') }}
                    </button>
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-danger waves-effect waves-light ">
                        {{ trans('strings.backend.general.app_back_to_list') }}
                    </a>
                </div>

            </div>

        </div>
    </div>
    {!! Form::close() !!}

@endsection


@push('after-scripts')
    <script src="{{ asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/unisharp/laravel-ckeditor/ckeditor.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/unisharp/laravel-ckeditor/adapters/jquery.js') }}"></script>
    <script src="{{ asset('/vendor/laravel-filemanager/js/lfm.js') }}"></script>
    <script>
        $('.editor').each(function() {

            CKEDITOR.replace($(this).attr('id'), {
                filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
                filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token={{ csrf_token() }}',
                filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
                filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token={{ csrf_token() }}',

                extraPlugins: 'smiley,lineutils,widget,codesnippet,prism,flash,colorbutton,colordialog',
            });

        });
        $(document).ready(function() {
            $(document).on('click', '.delete', function(e) {
                e.preventDefault();
                var parent = $(this).parent('.form-group');
                var confirmation = confirm('{{ trans('strings.backend.general.are_you_sure') }}')
                if (confirmation) {
                    var media_id = $(this).data('media-id');
                    $.post('{{ route('admin.media.destroy') }}', {
                            media_id: media_id,
                            _token: '{{ csrf_token() }}'
                        },
                        function(data, status) {
                            if (data.success) {
                                parent.remove();
                            } else {
                                alert('Something Went Wrong')
                            }
                        });
                }
            })
        })

        $('#change-lang').change(function(e) {
            e.preventDefault();
            let params = new URLSearchParams(window.location.search);
            const slug = params.get('slug');
            window.location.href = window.location.origin + window.location.pathname +
                `?slug=${slug}&lang=${$(this).val()}`
        });
    </script>
@endpush
