@extends('frontend.layouts.app' . config('theme_layout'))

@section('title', trans('labels.frontend.home.title') . ' | ' . app_name())
@section('meta_description', '')
@section('meta_keywords', '')
@php
    use Illuminate\Support\Facades\Storage;
@endphp

@push('after-styles')
    <style>
        /*.address-details.ul-li-block{*/
        /*line-height: 60px;*/
        /*}*/
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

        @media screen and (max-width: 767px) {

            .ham-top-space {
                margin-top: 0.7rem;
            }
        }
    </style>
@endpush

@section('content')



    <!-- Start of slider section
                                ============================================= -->
    @if (session()->has('alert'))
        <div class="alert alert-light alert-dismissible fade my-alert show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>{{ session('alert') }}</strong>
        </div>
    @endif
    @include('frontend.layouts.partials.slider')
 <div class="container">
        <div class="outcategory caterow">
            <div> <h4>@lang('labels.frontend.home.our_category')</h4> </div>
            <ul>
                <li><img src="{{ asset('assets/img/icon1.png') }}" alt="@lang('labels.frontend.home.category_1')">   @lang('labels.frontend.home.category_1') </li>
                 <li><img src="{{ asset('assets/img/icon2.png') }}" alt="@lang('labels.frontend.home.category_2')"> @lang('labels.frontend.home.category_2') </li>
                  <li><img src="{{ asset('assets/img/icon3.png') }}" alt="@lang('labels.frontend.home.category_3')"> @lang('labels.frontend.home.category_3') </li>
                   <li><img src="{{ asset('assets/img/icon4.png') }}" alt="@lang('labels.frontend.home.category_4')"> @lang('labels.frontend.home.category_4') </li>
            </ul>
        </div>
    </div>

    <section class="about-section padding-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6 ">
                    {{-- {{ dd($about_us) }} --}}
                    <h2 class="mb-3"><strong>
                            @if (isset($about_us->title))
                                {{ $about_us->title }}
                            @endif
                        </strong></h2>
                    <p class="mb-4">{!! $about_us->content !!}</p>
                </div>
                <div class="col-md-6 d-flex justify-content-md-end justify-content-center">
                    @php
                    $fileExists = Storage::exists('storage/uploads/'.$about_us->image);
                    @endphp
                    @if(1)
                    <img src="{{ asset( $about_us->image) }}" class=""
                        style="height:265px;float:right;">
                    @else
                    <img class=""
                    style="height:265px;float:right;" src="{{ asset('img/slider1.png') }}" alt="About Us" />
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="mission padding-top side-spacings">
        <div class="container">
           
                    {{-- <h2>@lang('What') <br> <strong>
                            @lang('TadreebLMS') </strong> @lang('Aims')</h2> --}}
                    <h2>@lang('What TadreebLMS Aims')</h2>
                
            <div class="row">
                
                <div class="col-lg-6 col-md-12 col-12 d-flex align-items-center order-md-3 my-sm-4">
                    <div class="inner middle w-100 p-4 radius-20">
                        {{-- <h3>@lang('Our') <strong>@lang('Vision')</strong></h3> --}}
                        <h3><strong>@lang('Our Vision')</strong></h3>
                        <p> {{ $our_vision->value }} </p>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12 d-flex align-items-center order-md-2">
                    <div class="">
                        {{-- <h3>@lang('Our') <strong>@lang('Mission')</strong></h3> --}}
                        <h3><strong>@lang('Our Mission')</strong></h3>
                        <p>{{ $our_mission->value }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="news padding-top bottom-space">
        <div class="container">
            <div class="row">
                <div class="w-100 d-flex justify-content-between section-title-3">
                    {{-- <h2>@lang('News') @lang('&') <strong>@lang('Updates')</strong></h2> --}}
                    <h2><strong>@lang('News & Updates')</strong></h2>
                    <a href="" class="btn border btn-all rounded-pill">@lang('Browse All')</a>
                </div>

                <div class="news-slider w-100">
                    @if (count($news) > 0)
                        @foreach ($news as $row)
                            <div class="item">
                                  <div class="newsbg">
                                @php
                                $fileExists = Storage::exists('storage/uploads/'.$row->icon);
                                @endphp
                                @if(1)
                                <img class="d-block w-100" src="{{ asset('storage/uploads/' . $row->icon) }}"
                                    alt="First slide">
                                @else

                                <img class="d-block w-100" src="{{ asset('img/slider1.png') }}"
                                alt="First slide">

                                @endif
                                <div class="content">
                                    <h5>{{ $row->title }}</h5>
                                    <p>{{ $row->content }}</p>
                                </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>




        </div>
    </section>

    <section class="events-section padding-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="w-100 d-flex justify-content-between section-title-3">
                        {{-- <h2>@lang('Latest') <strong>@lang('Events')</strong></h2> --}}
                        <h2><strong>@lang('Latest Events')</strong></h2>
                        <a href="" class="btn border btn-all rounded-pill">@lang('labels.general.view_all')</a>
                    </div>
                    @if (count($events) > 0)
                        @foreach ($events as $evet)
                            <div class="event-item border p-3 radius-10 mb-3">
                                <div class="row">
                                    <div class="col-md-4 col-sm-12">
                                        <div class="event-img">
                                            @php
                                            $fileExists = Storage::exists('storage/uploads/'.$evet->icon);
                                            @endphp
                                            @if(1)
                                            <img src="{{ asset('storage/uploads/' . $evet->icon) }}"
                                                class="radius-10 w-100">
                                            @else
                                                <img class="radius-10 w-100" src="{{ asset('img/slider1.png') }}"
                                                    alt="First slide">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-sm-12 pl-0 mtb-space">
                                        <div class="content">
                                            <div class="d-flex mb-3 w-100 justify-content-between align-items-center">
                                                {{-- <button class="btn btn-light rounded-pill">Politics</button> --}}
                                                <span>{{ $evet->event_date ? date('Y-m-d', strtotime($evet->event_date)) : '--' }}</span>
                                            </div>
                                            <h5>{{ $evet->title }}</h5>
                                            <p>{!! $evet->content !!}</p>
                                            <a href="#"><strong>@lang('Learn More')</strong></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                </div>
                <div class="col-md-6 col-sm-12 event-sm-topspace">
                    <div class="w-100 d-flex justify-content-between section-title-3">
                        {{-- <h2>@lang('Latest') <strong>@lang('Libraries')</strong></h2> --}}
                        <h2><strong>@lang('Latest Libraries')</strong></h2>
                        <a href="" class="btn border btn-all rounded-pill">@lang('labels.general.view_all')</a>
                    </div>
                    <div class="libraries">
                        <div class="row">
                            @if (count($libraries) > 0)
                                @foreach ($libraries as $lib)
                                    <div class="col-sm-6 mb-3">
                                        <video width="100%" controls>
                                            <source src="{{ $lib->content }}" type="video/mp4">
                                            <source src="{{ $lib->content }}" type="video/ogg">
                                            Your browser does not support the video tag.
                                        </video>
                                        <h5>{{ $lib->title }}</h5>
                                    </div>
                                @endforeach
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>


    </section>

    @if (count($announcements) > 0)
        <section class="anounce-section padding-top pb45 my-2">
            <div class="container">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="anounce-item p-4 shadow radius-20">
                            <h3>{{ @$announcements[0]->title }}</h3>
                            <p>{{ @$announcements[0]->content }}</p>
                            <a href="#"><strong>@lang('Learn More')</strong></a>
                        </div>
                    </div>
                    @if (@$announcements[1])
                        <div class="col-sm-6">
                            <div class="w-100 d-flex justify-content-between section-title-3 text-white">
                                <div>
                                    <h2 class=" text-white"><strong>@lang('Announcement')</strong></h2>
                                    {{-- <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor</p> --}}
                                </div>
                            </div>
                            <div class="anounce-item p-4 shadow radius-20">
                                <h3>{{ @$announcements[1]->title }}</h3>
                                <p>{{ @$announcements[1]->content }}</p>
                                <a href="#"><strong>@lang('Learn More')</strong></a>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </section>
    @endif

    {{-- <section class="feedback padding-top">
         <div class="container">
                <div class="w-100 d-flex justify-content-between section-title-3">
                    <h2>Student's  <strong>Feedback</strong></h2>
                    <a href="" class="btn border btn-all rounded-pill">@lang('labels.general.view_all')</a>
                </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="feedback-item text-center border radius-20 py-5 px-4">
                        <img src="{{asset('img/quote-1.png')}}" class="position-absolute quote">
                        <p>It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the race.</p>
                        <img src="{{asset('img/avatar.png')}}" class="mb-3">

                        <h5>Abdul Rahman</h5>
                        <span>Physics</span>

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feedback-item text-center border radius-20 py-5 px-4 bg-light">
                        <img src="{{asset('img/quote-1.png')}}" class="position-absolute quote">
                        <p>It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the race.</p>
                        <img src="{{asset('img/avatar.png')}}" class="mb-3">

                        <h5>Abdul Rahman</h5>
                        <span>Physics</span>

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feedback-item text-center border radius-20 py-5 px-4">
                        <img src="{{asset('img/quote-1.png')}}" class="position-absolute quote">
                        <p>It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the race.</p>
                        <img src="{{asset('img/avatar.png')}}" class="mb-3">

                        <h5>Abdul Rahman</h5>
                        <span>Physics</span>

                    </div>
                </div>
            </div>
        </div>
    </section> --}}

    {{-- <section class="subscribe-section padding-top">
         <div class="container bg-lb radius-20 py-5 px-4">
                <div class="w-100 d-flex justify-content-between align-items-center section-title-3 mb-0">
                    <h2>To Get Latest News & Further Update <br>
                    <strong>Subscribe Our Newsletter</strong></h2>
                    
                </div>

                <div class="input-group mb-3 mt-3">
  <input type="text" class="form-control p-3 rounded-pill mr-2" placeholder="Subscribe to our News Letter" aria-label="Recipient's username" aria-describedby="basic-addon2">
  <div class="input-group-append">
    <span class="input-group-text bg-first text-white rounded-pill px-5" id="basic-addon2">SUBSCRIBE</span>
  </div>
</div>
         </div>
    </section> --}}

    @if ($sections->search_section->status == 1)
        <!-- End of slider section
                                ============================================= -->
        {{-- <section id="search-course" class="search-course-section">
            <div class="container">
                <div class="section-title mb20 headline text-center ">
                    <span class="subtitle text-uppercase">@lang('labels.frontend.home.learn_new_skills')</span>
                    <h2>@lang('labels.frontend.home.search_courses')</h2>
                </div>
                <div class="search-course mb30 relative-position ">
                    <form action="{{route('search')}}" method="get">

                        <div class="input-group search-group">
                            <input class="course" name="q" type="text"
                                   placeholder="@lang('labels.frontend.home.search_course_placeholder')">
                            <select name="category" class="select form-control">
                                @if (count($categories) > 0)
                                    <option value="">@lang('labels.frontend.course.select_category')</option>
                                    @foreach ($categories as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>

                                    @endforeach
                                @else
                                    <option>>@lang('labels.frontend.home.no_data_available')</option>
                                @endif

                            </select>
                            <div class="nws-button position-relative text-center  gradient-bg text-capitalize">
                                <button type="submit"
                                        value="Submit">@lang('labels.frontend.home.search_course')</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="search-counter-up">
                    <div class="row">
                        <div class="col-md-4 col-sm-4">
                            <div class="counter-icon-number ">
                                <div class="counter-icon">
                                    <i class="text-gradiant flaticon-graduation-hat"></i>
                                </div>
                                <div class="counter-number">
                                    <span class=" bold-font">{{$total_students}}</span>
                                    <p>@lang('labels.frontend.home.students_enrolled')</p>
                                </div>
                            </div>
                        </div>
                        <!-- /counter -->

                        <div class="col-md-4 col-sm-4">
                            <div class="counter-icon-number ">
                                <div class="counter-icon">
                                    <i class="text-gradiant flaticon-book"></i>
                                </div>
                                <div class="counter-number">
                                    <span class=" bold-font">{{$total_courses}}</span>
                                    <p>@lang('labels.frontend.home.online_available_courses')</p>
                                </div>
                            </div>
                        </div>
                        <!-- /counter -->


                        <div class="col-md-4 col-sm-4">
                            <div class="counter-icon-number ">
                                <div class="counter-icon">
                                    <i class="text-gradiant flaticon-group"></i>
                                </div>
                                <div class="counter-number">
                                    <span class=" bold-font">{{$total_teachers}}</span>
                                    <p>@lang('labels.frontend.home.teachers')</p>
                                </div>
                            </div>
                        </div>
                        <!-- /counter -->
                    </div>
                </div>
            </div>
        </section> --}}
        <!-- End of Search Courses
                                ============================================= -->
    @endif




    @if ($sections->popular_courses->status == 1)
        @include('frontend.layouts.partials.popular_courses')
    @endif

    @if ($sections->reasons->status != 0 || $sections->testimonial->status != 0)
        <!-- Start of why choose us section
                            ============================================= -->
        {{-- <section id="why-choose-us" class="why-choose-us-section">
            <div class="jarallax  backgroud-style">
                <div class="container">
                    @if ($sections->reasons->status == 1)

                        <div class="section-title mb20 headline text-center ">
                            <span class="subtitle text-uppercase">{{env('APP_NAME')}} @lang('labels.frontend.layouts.partials.advantages')</span>
                            <h2>@lang('labels.frontend.layouts.partials.why_choose') <span>{{app_name()}}</span></h2>
                        </div>
                        @if ($reasons->count() > 0)
                            <div id="service-slide-item" class="service-slide">
                                @foreach ($reasons as $item)
                                    <div class="service-text-icon ">

                                        <div class="service-icon float-left">
                                            <i class="text-gradiant {{$item->icon}}"></i>
                                        </div>
                                        <div class="service-text">
                                            <h3 class="bold-font">{{$item->title}}</h3>
                                            <p>{{$item->content}}.</p>
                                        </div>
                                    </div>

                                @endforeach

                            </div>
                        @endif
                    @endif
                <!-- /service-slide -->
                    @if ($sections->testimonial->status == 1)
                        <div class="testimonial-slide">
                            <div class="section-title-2 mb65 headline text-left ">
                                <h2>@lang('labels.frontend.layouts.partials.students_testimonial')</h2>
                            </div>
                            @if ($testimonials->count() > 0)
                                <div id="testimonial-slide-item" class="testimonial-slide-area">
                                    @foreach ($testimonials as $item)
                                        <div class="student-qoute ">
                                            <p>{{$item->content}}</p>
                                            <div class="student-name-designation">
                                                <span class="st-name bold-font">{{$item->name}} </span>
                                                <span class="st-designation">{{$item->occupation}}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <h4>@lang('labels.general.no_data_available')</h4>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section> --}}
        <!-- End of why choose us section
                                ============================================= -->
    @endif

    @if ($sections->latest_news->status == 1)
        <!-- Start latest section
                            ============================================= -->
        {{-- @include('frontend.layouts.partials.latest_news') --}}
        <!-- End latest section
                                ============================================= -->
    @endif


    @if ($sections->sponsors->status == 1)
        @if (count($sponsors) > 0)
            <!-- Start of sponsor section
                            ============================================= -->
            {{-- <section id="sponsor" class="sponsor-section">
                <div class="container">
                    <div class="section-title-2 mb65 headline text-left ">
                        <h2>{{env('APP_NAME')}} <span>@lang('labels.frontend.layouts.partials.sponsors')</span></h2>
                    </div>

                    <div class="sponsor-item sponsor-1 text-center">
                        @foreach ($sponsors as $sponsor)
                            <div class="sponsor-pic text-center">
                                <a href="{{ ($sponsor->link != "") ? $sponsor->link : '#' }}">
                                    <img src={{asset("storage/uploads/".$sponsor->logo)}} alt="{{$sponsor->name}}">
                                </a>

                            </div>
                        @endforeach

                    </div>
                </div>
            </section> --}}
            <!-- End of sponsor section
                           ============================================= -->
        @endif
    @endif


    @if ($sections->featured_courses->status == 1)
        <!-- Start of best course
                            ============================================= -->
        {{-- @include('frontend.layouts.partials.browse_courses') --}}
        <!-- End of best course
                                ============================================= -->
    @endif


    @if ($sections->teachers->status == 1)
    @endif



    @if ($sections->course_by_category->status == 1)
        <!-- Start Course category
                            ============================================= -->
        {{-- @include('frontend.layouts.partials.course_by_category') --}}
        <!-- End Course category
                                ============================================= -->
    @endif


    @if ($sections->contact_us->status == 1)
        <!-- Start of contact area
                            ============================================= -->
        <!-- @include('frontend.layouts.partials.contact_area') -->
        <!-- End of contact area
                                ============================================= -->
    @endif


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
            responsive: [{
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
