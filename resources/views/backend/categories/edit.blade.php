@extends('backend.layouts.app')
@section('title', __('labels.backend.categories.title').' | '.app_name())

@push('after-styles')
    <link rel="stylesheet" href="{{asset('plugins/bootstrap-iconpicker/css/bootstrap-iconpicker.min.css')}}"/>
@endpush


@section('content')

<form method="POST" action="{{ route('admin.categories.update', $category->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="alert alert-danger d-none" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
        <div class="error-list"></div>
    </div>

    <div class="pb-3 d-flex justify-content-between align-items-center">
        <h4>@lang('labels.backend.categories.edit')</h4>
        <div>
            <a href="{{ route('admin.categories.index') }}" class="btn add-btn">
                @lang('labels.backend.categories.view')
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <div class="row">

                <div class="col-6 col-lg-4 form-group">
                    <label for="name" class="control-label">
                        @lang('labels.backend.categories.fields.name') *
                    </label>

                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $category->name) }}"
                           class="form-control"
                           placeholder="Enter Category Name">
                </div>

                <!-- Submit button -->
                <div class="col-lg-3 col-md-3 form-group text-center mt-4">
                    <button type="submit" class="add-btn">
                        @lang('strings.backend.general.app_update')
                    </button>
                </div>

            </div>

        </div>
    </div>

</form>

@endsection


@push('after-scripts')
    <script src="{{asset('plugins/bootstrap-iconpicker/js/bootstrap-iconpicker.bundle.min.js')}}"></script>

    <script>
        var icon = 'fas fa-bomb';
        @if($category->icon != "")
                icon = "{{$category->icon}}";
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

        $(document).on('change', '#icon_type', function () {

            if ($(this).val() == 1) {
                $('.upload-image-wrapper').parent('.col-12').removeClass('d-none')

                $('.upload-image-wrapper').removeClass('d-none');
                $('.select-icon-wrapper').addClass('d-none')
            } else if ($(this).val() == 2) {
                $('.upload-image-wrapper').parent('.col-12').removeClass('d-none')

                $('.upload-image-wrapper').addClass('d-none');
                $('.select-icon-wrapper').removeClass('d-none')
            } else {
                $('.upload-image-wrapper').parent('.col-12').addClass('d-none')
                $('.upload-image-wrapper').addClass('d-none');
                $('.select-icon-wrapper').addClass('d-none');


            }
        })

    </script>
@endpush


