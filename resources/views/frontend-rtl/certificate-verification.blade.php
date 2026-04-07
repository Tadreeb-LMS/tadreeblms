@extends('frontend-rtl.layouts.app'.config('theme_layout'))

@section('title', 'Certificate Verification | '.app_name())
@section('meta_description', '')
@section('meta_keywords','')

@push('after-styles')
    <style>
        .my-alert {
            position: absolute;
            z-index: 10;
            left: 0;
            right: 0;
            top: 25%;
            width: 50%;
            margin: auto;
            display: inline-block;
        }
    </style>
@endpush

@section('content')
    @php
        $footer_data = json_decode(config('footer_data'));
    @endphp
    @if(session()->has('alert'))
        <div class="alert alert-light alert-dismissible fade my-alert show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>{{session('alert')}}</strong>
        </div>
    @endif

    <!-- Start of breadcrumb section
        ============================================= -->
    <section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
        <div class="blakish-overlay"></div>
        <div class="container">
            <div class="page-breadcrumb-content text-center">
                <div class="page-breadcrumb-title">
                    <h2 class="breadcrumb-head black bold">{{env('APP_NAME')}}
                        <span> @lang('labels.frontend.certificate_verification.title')</span></h2>
                </div>
            </div>
        </div>
    </section>
    <!-- End of breadcrumb section
        ============================================= -->



    <!-- Start of contact area form
        ============================================= -->
    <section id="contact-form" class="contact-form-area_3 contact-page-version">
        <div class="container">
            @include('includes.partials.messages')

            <div class="row">
                <div class="col-md-6 mx-auto col-12">
                    <div class="contact_third_form" style="padding-bottom: 30px">
                        <form class="contact_form" action="{{route('frontend.certificates.verify')}}" method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="contact-info">
                                        <input class="name" value="{{(session('data')) ? session('data')['name'] : old('name')}}" name="name" type="text"
                                               placeholder="@lang('labels.frontend.certificate_verification.name_on_certificate')">
                                        @if($errors->has('name'))
                                            <span class="help-block text-danger">{{$errors->first('name')}}</span>
                                        @endif
                                    </div>

                                </div>
                                <div class="col-md-12">
                                    <div class="contact-info">
                                        <input class="date" value="{{(session('data')) ? session('data')['date'] : old('date')}}" name="date"
                                               pattern="\d{4}-\d{2}-\d{2}" type="text"
                                               placeholder="@lang('labels.frontend.certificate_verification.date_on_certificate')">
                                        @if($errors->has('date'))
                                            <span class="help-block text-danger">{{$errors->first('date')}}</span>
                                        @endif
                                    </div>
                                </div>

                            </div>

                            <div class="nws-button mt-5 text-center text-uppercase">
                                <button class="text-uppercase" type="submit"
                                        value="Submit">@lang('labels.frontend.certificate_verification.verify_now') <i
                                            class="fas fa-caret-right"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                @if(session()->has('data'))

                    <div class="col-md-10 col-12 mx-auto mt-4">
                        <div class="card">
                            <div class="card-body">
                                @if(count(session('data')['certificates']) > 0)
                                    <div class="table-responsive">
                                    <table class="table">
                                        <tr class="bg-dark text-white">
                                            <th>{{ __('course_pages.certificate.course_name') }}</th>
                                            <th>{{ __('course_pages.certificate.student_name') }}</th>
                                            <th>{{ __('course_pages.certificate.certified_at') }}</th>
                                            <th>{{ __('course_pages.certificate.actions') }}</th>
                                        </tr>
                                        @foreach(session('data')['certificates'] as $certificate)
                                            <tr>
                                                <td>{{$certificate->course->title}}</td>
                                                <td>{{$certificate->user->name}}</td>
                                                <td>{{$certificate->created_at->format('d M, Y')}}</td>
                                                <td><a href="{{asset('storage/certificates/'.$certificate->url)}}"
                                                       class="btn btn-success text-white">
                                                        @lang('labels.backend.certificates.view') </a>

                                                    <a class="btn btn-primary text-white"
                                                       href="{{route('certificates.download',['certificate_id'=>$certificate->id])}}">
                                                        @lang('labels.backend.certificates.download') </a></td>
                                            </tr>
                                        @endforeach
                                    </table>
                                    </div>
                                @else
                                    <h4 class="text-center">@lang('labels.frontend.certificate_verification.not_found')</h4>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
    <!-- End of contact area form
        ============================================= -->
@endsection
