@extends('frontend.layouts.basic')

@section('title', trans('labels.frontend.home.title').' | '.app_name())
@section('meta_description', '')
@section('meta_keywords','')


@push('after-styles')
    <style>
        .teacher-img-content .teacher-social-name {
            max-width: 67px;
        }

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

        .container.course-detail {
            padding-bottom: 80px;
        }

    </style>
@endpush

@section('content')


    
    

    <section class="about-section padding-top">
        <div class="container course-detail">
           
            <div class="row">
                <div class="col-md-6">
                    <h3 class="title">{{ $lessons_list->title }}</h3>
                    <p class="desc">{!! $lessons_list->short_text !!}</p>
                    <h4 class="title">{{ __('attendance_pages.attendance.course') }} {{ $lessons_list->course->title }}</h4>
                    <p class="desc">{{ __('attendance_pages.attendance.date') }} {{ date('d/m/Y h:i A',strtotime($lessons_list->lesson_start_date)) }}</p>
                    <p class="desc">{{ __('attendance_pages.attendance.duration') }} {{ $lessons_list->duration }}</p>
                    @if ($lessons_list->course->course_image)
                        <img width="300" height="80" src="{{ asset('storage/uploads/'.$lessons_list->course->course_image) }}" class="mt-1">
                    @endif
                    
                    
                </div>
                <div class="col-md-6">
                    @if(session()->has('success'))
                        <div class="alert alert-success">
                            {{ session()->get('success') }}
                        </div>
                    @endif
                    @if(session()->has('error'))
                        <div class="alert alert-danger">
                            {{ session()->get('error') }}
                        </div>
                    @endif
                    <form action="{{ route('attendance.save.attendance.lesson') }}" method="post">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $lessons_list->course_id }}" />
                        <input type="hidden" name="lesson_id" value="{{ $lessons_list->id }}" />
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{ __('attendance_pages.attendance.email_address') }}</label>
                            <input type="email"  class="form-control" name="email" id="email" placeholder="{{ __('attendance_pages.attendance.email_address') }}">
                            <small id="emailHelp" class="form-text text-muted">{{ __('attendance_pages.attendance.help_text') }}</small>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('attendance_pages.attendance.mark_present') }}</button>
                    </form>
                </div>
                
            </div>  
        </div>      
    </section>


@endsection

@push('after-scripts')
    <script>
        $('ul.product-tab').find('li:first').addClass('active');
        $('.news-slider').slick({
          dots: false,
          infinite: true,
          speed: 300,
          slidesToShow: 3,
          slidesToScroll: 1,
          responsive: [
            {
              breakpoint: 1024,
              settings: {
                slidesToShow: 3,
                slidesToScroll: 3,
                infinite: true,
                dots: true
              }
            },
            {
              breakpoint: 600,
              settings: {
                slidesToShow: 2,
                slidesToScroll: 2
              }
            },
            {
              breakpoint: 480,
              settings: {
                slidesToShow: 1,
                slidesToScroll: 1
              }
            }
            ]
         });
    </script>
@endpush
