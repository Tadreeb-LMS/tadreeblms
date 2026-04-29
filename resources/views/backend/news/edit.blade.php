@extends('backend.layouts.app')
@section('title', 'News Edit' . ' | ' . app_name())

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-iconpicker/css/bootstrap-iconpicker.min.css') }}" />
@endpush

@section('content')
    <form method="POST" action="{{ route('admin.news.update', $reason->id) }}" enctype="multipart/form-data">
        @csrf
        @method('POST') {{-- if your route expects PUT/PATCH, change to @method('PUT') --}}

        <div class="alert alert-danger d-none" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <div class="error-list"></div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="page-title d-inline">Update News</h3>
                <div class="float-right">
                    <a href="{{ route('admin.news.index') }}" class="btn btn-success">@lang('labels.general.view_all')</a>
                </div>
            </div>
            <div class="card-body">
                <div class="col-md-3 mb-4">
                    <select name="lang" id="change-lang" class="form-control">
                        <option value="en" @if (request()->lang == 'en') selected @endif>{{ locale_label('en') }}</option>
                        <option value="ar" @if (request()->lang == 'ar') selected @endif>{{ locale_label('ar') }}</option>
                    </select>
                </div>

                <div class="row justify-content-center">
                    <div class="col-12 col-lg-8 form-group">
                        <label for="title" class="control-label">
                            {{ trans('labels.backend.reasons.fields.title') }} *
                        </label>
                        <input type="text" name="title" id="title"
                               value="{{ old('title', $reason->title) }}"
                               class="form-control"
                               placeholder="Enter Category Name">
                    </div>

                    @if ($reason->icon)
                        <div class="col-12 col-lg-4 form-group">
                            <label for="news_image" class="control-label">
                                {{ trans('labels.backend.pages.fields.featured_image') }}
                                {{ trans('labels.backend.pages.max_file_size') }}
                            </label>
                            <input type="file" name="news_image" id="news_image"
                                   class="form-control"
                                   accept="image/jpeg,image/gif,image/png">
                            <input type="hidden" name="news_image_max_size" value="8">
                            <input type="hidden" name="news_image_max_width" value="4000">
                            <input type="hidden" name="news_image_max_height" value="4000">
                        </div>
                        <div class="col-lg-1 col-12 form-group">
                            <a href="{{ asset('storage/uploads/' . $reason->icon) }}" target="_blank">
                                <img src="{{ asset('storage/uploads/' . $reason->icon) }}" height="65px" width="65px">
                            </a>
                        </div>
                    @else
                        <div class="col-12 col-lg-4 form-group">
                            <label for="news_image" class="control-label">
                                {{ trans('labels.backend.pages.fields.featured_image') }}
                                {{ trans('labels.backend.pages.max_file_size') }}
                            </label>
                            <input type="file" name="news_image" id="news_image" class="form-control">
                            <input type="hidden" name="news_image_max_size" value="8">
                            <input type="hidden" name="news_image_max_width" value="4000">
                            <input type="hidden" name="news_image_max_height" value="4000">
                        </div>
                    @endif

                    <div class="col-12 form-group">
                        <label for="content" class="control-label">
                            {{ trans('labels.backend.reasons.fields.content') }} *
                        </label>
                        <textarea name="content" id="content" class="form-control"
                                  placeholder="{{ trans('labels.backend.reasons.fields.content') }}">{{ old('content', $reason->content) }}</textarea>
                    </div>

                    <div class="col-12 form-group text-center">
                        <button type="submit" class="btn mt-auto btn-danger">
                            {{ trans('strings.backend.general.app_save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('after-scripts')
    <script src="{{ asset('plugins/bootstrap-iconpicker/js/bootstrap-iconpicker.bundle.min.js') }}"></script>
    <script>
        var icon = 'fas fa-bomb';
        @if ($reason->icon != '')
            icon = "{{ $reason->icon }}";
        @endif
        $('#icon').iconpicker({
            cols: 10,
            icon: icon,
            iconset: 'fontawesome5',
            labelHeader: '{0} of {1} pages',
            labelFooter: '{0} - {1} of {2} icons',
            placement: 'bottom',
            rows: 5,
            search: true,
            searchText: 'Search',
            selectedClass: 'btn-success',
            unselectedClass: ''
        });

        $('#change-lang').change(function(e) {
            e.preventDefault();
            let params = new URLSearchParams(window.location.search);
            const slug = params.get('slug');
            window.location.href = window.location.origin + window.location.pathname +
                `?slug=${slug}&lang=${$(this).val()}`
        });
    </script>
@endpush
