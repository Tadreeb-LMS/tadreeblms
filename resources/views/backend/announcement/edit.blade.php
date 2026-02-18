@extends('backend.layouts.app')
@section('title', 'Announcement Edit' . ' | ' . app_name())

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-iconpicker/css/bootstrap-iconpicker.min.css') }}" />
@endpush
@section('content')
    <form method="POST" action="{{ route('admin.announcement.update', $reason->id) }}" enctype="multipart/form-data">
        @csrf
        {{-- @method('PATCH') --}} 
        {{-- The original code used POST for update in routes, so we stick to POST or check if PUT/PATCH is needed. 
             Checking routes: Route::post('announcement-update/{page}', ...) 
             So it is POST, no @method('PATCH') needed. --}}

    <div class="alert alert-danger d-none" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
        <div class="error-list">
        </div>
    </div>
    <div class="pb-3 d-flex justify-content-between align-items-center">
        <h4>Update Announcement</h4>
        <div>
            <a href="{{ route('admin.announcement.index') }}" class="add-btn">View All</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="col-md-3 mb-4 mt-2 custom-select-wrapper">
                <select name="lang" id="change-lang" class="form-control custom-select-box">
                    <option value="en" @if (request()->lang == 'en') selected @endif>English</option>
                    <option value="ar" @if (request()->lang == 'ar') selected @endif>Arabic</option>
                </select>
                <span class="custom-select-icon" style="right: 23px;">
        <i class="fa fa-chevron-down"></i>
    </span>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-12 col-lg-6 form-group">
                    <label for="title" class="control-label">{{ trans('labels.backend.reasons.fields.title') }} *</label>
                    <input class="form-control" placeholder="Enter Category Name" name="title" type="text" value="{{ old('title', $reason->title) }}">

                </div>
                <div class="col-md-12 col-lg-6 form-group">
                    <label>Date</label>
                    <input type="date" value="{{ date('Y-m-d', strtotime($reason->event_date)) }}" class="form-control"
                        name="event_date">

                </div>


                {{-- @if ($reason->icon)
                    <div class="col-12 col-lg-4 form-group">
                        {!! Form::label('news_image', trans('labels.backend.pages.fields.featured_image').' '.trans('labels.backend.pages.max_file_size'), ['class' => 'control-label']) !!}
                        {!! Form::file('news_image', ['class' => 'form-control', 'accept' => 'image/jpeg,image/gif,image/png']) !!}
                        {!! Form::hidden('news_image_max_size', 8) !!}
                        {!! Form::hidden('news_image_max_width', 4000) !!}
                        {!! Form::hidden('news_image_max_height', 4000) !!}
                    </div>
                    <div class="col-lg-1 col-12 form-group">
                        <a href="{{ asset('storage/uploads/'.$reason->icon) }}" target="_blank"><img
                                    src="{{ asset('storage/uploads/'.$reason->icon) }}" height="65px"
                                    width="65px"></a>
                    </div>
                @else
                    <div class="col-12 col-lg-4 form-group">
                        {!! Form::label('news_image', trans('labels.backend.pages.fields.featured_image').' '.trans('labels.backend.pages.max_file_size'), ['class' => 'control-label']) !!}
                        {!! Form::file('news_image', ['class' => 'form-control']) !!}
                        {!! Form::hidden('news_image_max_size', 8) !!}
                        {!! Form::hidden('news_image_max_width', 4000) !!}
                        {!! Form::hidden('news_image_max_height', 4000) !!}
                    </div>
                @endif --}}
                <div class="col-12 form-group">
                    <label for="content" class="control-label">{{ trans('labels.backend.reasons.fields.content') }} *</label>
                    <textarea class="form-control" placeholder="{{ trans('labels.backend.reasons.fields.content') }}" name="content" cols="50" rows="10">{{ old('content', $reason->content) }}</textarea>

                </div>

                <div class="col-12 form-group text-right">

                    <button class="add-btn" type="submit">{{ trans('strings.backend.general.app_save') }}</button>
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
            placement: 'bottom', // Only in button tag
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
