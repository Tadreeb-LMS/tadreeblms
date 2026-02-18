@extends('backend.layouts.app')
@section('title', __('labels.backend.pages.title').' | '.app_name())

@push('after-styles')
    <style>
        .blog-detail-content p img{
            margin: 2px;
        }
        .label{
            margin-bottom: 5px;
            display: inline-block;
            border-radius: 0!important;
            font-size: 0.9em;
        }
    </style>
@endpush

@section('content')


    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">@lang('labels.backend.pages.view')</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered table-striped">

                        <tr>
                            <th>@lang('labels.backend.pages.fields.title')</th>
                            <td>{{ $page->title }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.pages.fields.slug')</th>
                            <td>{{ $page->slug }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.pages.fields.featured_image')</th>
                            <td>@if($page->image)<a href="{{ asset('storage/uploads/' . $page->image) }}" target="_blank"><img src="{{ asset('storage/uploads/' . $page->image) }}" height="100px"/></a>@endif</td>
                        </tr>

                        <tr>
                            <th>@lang('labels.backend.pages.fields.content')</th>
                            <td>{!! $page->content !!}</td>
                        </tr>
                       
                        <tr>
                            <th>@lang('labels.backend.pages.fields.created_at')</th>
                            <td>{{ $page->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div><!-- Nav tabs -->

            <!-- Tab panes -->


            <a href="{{ url('/user/position') }}"
               class="btn btn-default border">@lang('strings.backend.general.app_back_to_list')</a>
        </div>
    </div>

@endsection

@push('after-scripts')
@endpush