@extends('backend.layouts.app')
@section('title', __('labels.backend.categories.title').' | '.app_name())

@push('after-styles')
    <link rel="stylesheet" href="{{asset('plugins/bootstrap-iconpicker/css/bootstrap-iconpicker.min.css')}}"/>
@endpush
@section('content')

<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4>
        @lang('labels.backend.categories.create')
    </h4>
    <div class="">
        <a href="{{ route('admin.categories.index') }}"
            class="btn add-btn">@lang('labels.backend.categories.view')</a>
    </div>
</div>
    <div class="card">
        <div class="card-body">

            <div class="row">
                <div class="col-12">

                    <form method="POST" id="addcategory" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-12 col-lg-4 form-group">
                            <label for="title" class="control-label">{{ trans('labels.backend.categories.fields.name') }} *</label>
                            <input class="form-control" placeholder="{{ trans('labels.backend.categories.fields.name') }}" required name="name" type="text" value="{{ old('name') }}">

                        </div>
                        <div class="col-lg-3 col-md-3 form-group mt-4 d-flex align-items-center">

                            <button class="add-btn" type="submit">{{ trans('strings.backend.general.app_save') }}</button>
                        </div>
                    </div>

                    </form>


                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
    <script src="{{asset('plugins/bootstrap-iconpicker/js/bootstrap-iconpicker.bundle.min.js')}}"></script>

    <script>
        $(document).ready(function () {
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

@endpush
