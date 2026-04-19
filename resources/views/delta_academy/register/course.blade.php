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

        .login_heading {
    font-size: 22px;
    color: #494949;
    margin-bottom: 25px;
    font-weight: 600;
}

.login_heading a {
    color: #4fc361;
    font-weight: normal;
    background: #dffff1;
    font-size: 17px;
    padding: 6px 12px;
    border-radius: 6px;
    text-transform: uppercase;
}
    </style>
@endpush

@section('content')

    <section class="about-section padding-top">
        <div class="container course-detail">

            <div class="row">
                <div class="col-md-6">
                    <h3 class="title">{{ $course_list->title }}</h3>
                    <p class="desc">{!! $course_list->short_text !!}</p>
                    <h4 class="title">{{ __('course_pages.course_detail.course_title') }} {{ $course_list->title }}</h4>
                    {{-- <p class="desc">{{ __('attendance_pages.attendance.date') }} {{ date('d/m/Y h:i A',strtotime($course_list->lesson_start_date)) }}</p> --}}
                    @if(!empty($course_list->price))<p class="desc">{{ __('course_pages.course_detail.price_label') }} {{ $course_list->price }}</p>@endif
                    @if(isset($course_list->publishedLessons) && count($course_list->publishedLessons))
                    {{-- {{ dd($course_list->publishedLessons) }} --}}
                    <span>{{ __('attendance_pages.attendance.lessons_included') }}</span>
                    <ul class="course-lesson-list">
                        @foreach($course_list->publishedLessons as $lesson)
                            <li>{{ $lesson->title }}</li>
                        @endforeach
                    </ul>
                    @endif
                    @if ($course_list->course_image)
                        <img width="300" height="80" src="{{ asset('storage/uploads/'.$course_list->course_image) }}" class="mt-1">
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
                    <div class="login_heading">
                        {{ __('delta_academy_pages.register.already_user') }} <a href="http://academy.delta-medlab.com?openModal">{{ __('delta_academy_pages.register.login_here') }}</a>
                    </div>
                    <form action="{{ route('register.save.register.course') }}" method="post">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course_list->id }}" />
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{ __('course_pages.registration.enter_email') }}</label>
                            <input type="email"  class="form-control" name="email" id="email" placeholder="{{ __('course_pages.registration.placeholder_email') }}">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{ __('course_pages.registration.first_name') }}</label>
                            <input type="text"  class="form-control" name="first_name" id="first_name" placeholder="{{ __('course_pages.registration.placeholder_first') }}">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{ __('course_pages.registration.last_name') }}</label>
                            <input type="text"  class="form-control" name="last_name" id="last_name" placeholder="{{ __('course_pages.registration.placeholder_last') }}">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{ __('course_pages.registration.phone') }}</label>
                            <input type="text"  class="form-control" name="phone" id="phone" placeholder="{{ __('course_pages.registration.placeholder_phone') }}">

                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{ __('course_pages.registration.id_number') }}</label>
                            <input type="text"  class="form-control" name="id_no" id="id_no" placeholder="{{ __('course_pages.registration.placeholder_id') }}">

                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{ __('course_pages.registration.classification') }}</label>
                            <input type="text"  class="form-control" name="classification_no" id="classification_no" placeholder="{{ __('course_pages.registration.placeholder_classification') }}">

                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{ __('course_pages.registration.specialization') }}</label>
                            <input type="text"  class="form-control" name="specialization" id="specialization" placeholder="{{ __('course_pages.registration.placeholder_specialization') }}" value="" required>

                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1">{{ __('course_pages.registration.nationality') }}</label>
                            <select name="nationality" class="form-control">
                                <option>{{ __('course_pages.registration.select_country') }}</option>
                                @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{ __('course_pages.registration.gender') }}</label>
                            {{-- <input type="radio" class="form-control" name="gender" value="male"> Male
                            <input type="radio" class="form-control" name="gender" value="female"> Female --}}
                            <select name="gender" class="form-control">
                                <option value="male">{{ __('course_pages.registration.male') }}</option>
                                <option value="female">{{ __('course_pages.registration.female') }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{ __('course_pages.registration.password') }}</label>
                            <input type="password"  class="form-control" name="password" id="password" placeholder="{{ __('course_pages.registration.placeholder_password') }}">

                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{ __('course_pages.registration.confirm_password') }}</label>
                            <input type="password"  class="form-control" name="confirm_password" id="confirm_password" placeholder="{{ __('course_pages.registration.placeholder_confirm') }}">

                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('course_pages.registration.register_button') }}</button>
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
