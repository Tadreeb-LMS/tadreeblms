@extends('backend.layouts.app')
@section('title', 'Add Events' . ' | ' . app_name())

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-iconpicker/css/bootstrap-iconpicker.min.css') }}" />
@endpush
@section('content')

<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4>Add Events</h4>
    <div >
        <a href="{{ route('admin.events.index') }}" class="add-btn">View All</a>

    </div>
</div>
    <div class="card">
        <div class="card-body">
        <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="col-md-3 mb-4 pl-0 mt-2 custom-select-wrapper">
                <select name="lang" id="change-lang" class="form-control custom-select-box">
                    <option value="en" @if (request()->lang == 'en') selected @endif>English</option>
                    <option value="ar" @if (request()->lang == 'ar') selected @endif>Arabic</option>
                </select>
                <span class="custom-select-icon" style="right: 23px;">
        <i class="fa fa-chevron-down"></i>
    </span>
            </div>
            <div class="row">
               


                    
                        <div class="col-md-12 col-lg-4  form-group">
                            <label for="title" class="control-label">{{ trans('labels.backend.reasons.fields.title') }} *</label>
                            <input class="form-control" placeholder="{{ trans('labels.backend.reasons.fields.title') }}" name="title" type="text" value="{{ old('title') }}">

                        </div>

                        <div class="col-md-12 col-lg-4  form-group">
                            <label>Event Date</label>
                            <input type="date" class="form-control" name="event_date">

                        </div>

                        <div class="col-md-12 col-lg-4 form-group">
                            <label for="news_image" class="control-label">{{ trans('labels.backend.pages.fields.featured_image') . ' ' . trans('labels.backend.pages.max_file_size') }}</label>
                            {{-- {!! Form::file('news_image', ['class' => 'form-control','style' => 'padding:3px', 'accept' => 'image/jpeg,image/gif,image/png']) !!}
                            {!! Form::hidden('news_image_max_size', 8) !!}
                            {!! Form::hidden('news_image_max_width', 4000) !!}
                            {!! Form::hidden('news_image_max_height', 4000) !!} --}}
                                                  <div class="custom-file-upload-wrapper">
    <input type="file" name="image" id="customFileInput" class="custom-file-input">
    <label for="customFileInput" class="custom-file-label">
        <i class="fa fa-upload mr-1"></i> Choose a file
    </label>
</div>

                        </div>
                        <div class="col-12 form-group">
                            <label for="content" class="control-label">{{ trans('labels.backend.reasons.fields.content') }} *</label>
                            <textarea class="form-control" placeholder="{{ trans('labels.backend.reasons.fields.content') }}" name="content" cols="50" rows="10">{{ old('content') }}</textarea>

                        </div>

                        <div class="col-12 form-group text-right">

                            <button class="add-btn" type="submit">{{ trans('strings.backend.general.app_save') }}</button>
                        </div>
                   


               


            </div>
            </form>

        </div>
    </div>
@endsection

@push('after-scripts')
    <script src="{{ asset('plugins/bootstrap-iconpicker/js/bootstrap-iconpicker.bundle.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#icon').iconpicker({
                cols: 10,
                icon: 'fas fa-bomb',
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
