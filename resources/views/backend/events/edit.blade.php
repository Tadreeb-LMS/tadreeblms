@extends('backend.layouts.app')
@section('title', 'Event Edit' . ' | ' . app_name())

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-iconpicker/css/bootstrap-iconpicker.min.css') }}" />
@endpush

@section('content')
    <form method="POST" action="{{ route('admin.events.update', $reason->id) }}" enctype="multipart/form-data">
        @csrf
        @method('POST') {{-- if you actually want PUT, use @method('PUT') --}}

        <div class="alert alert-danger d-none" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <div class="error-list"></div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="page-title d-inline">Update Events</h3>
                <div class="float-right">
                    <a href="{{ route('admin.events.index') }}" class="btn btn-success">View All</a>
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
                    <div class="col-12 col-lg-5 form-group">
                        <label for="title" class="control-label">Title *</label>
                        <input type="text" name="title" class="form-control"
                               placeholder="Enter Category Name"
                               value="{{ old('title', $reason->title) }}">
                    </div>

                    <div class="col-12 col-lg-3 form-group">
                        <label for="event_date">Event Date</label>
                        <input type="date" name="event_date" class="form-control"
                               value="{{ old('event_date', date('Y-m-d', strtotime($reason->event_date))) }}">
                    </div>

                    @if ($reason->icon)
                        <div class="col-12 col-lg-4 form-group">
                            <label for="news_image" class="control-label">
                                Featured Image (Max: 8MB)
                            </label>
                            <input type="file" name="news_image" class="form-control"
                                   accept="image/jpeg,image/gif,image/png">
                        </div>
                        <div class="col-lg-1 col-12 form-group">
                            <a href="{{ asset('storage/uploads/' . $reason->icon) }}" target="_blank">
                                <img src="{{ asset('storage/uploads/' . $reason->icon) }}" height="65px" width="65px">
                            </a>
                        </div>
                    @else
                        <div class="col-12 col-lg-4 form-group">
                            <label for="news_image" class="control-label">
                                Featured Image (Max: 8MB)
                            </label>
                            <input type="file" name="news_image" class="form-control">
                        </div>
                    @endif

                    <div class="col-12 form-group">
                        <label for="content" class="control-label">Content *</label>
                        <textarea name="content" class="form-control" placeholder="Enter content">{{ old('content', $reason->content) }}</textarea>
                    </div>

                    <div class="col-12 form-group text-center">
                        <button type="submit" class="btn mt-auto btn-danger">Save</button>
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
