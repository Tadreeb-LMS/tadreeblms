@extends('frontend.layouts.app'.config('theme_layout'))

@section('title', ($bundle->meta_title) ? $bundle->meta_title : app_name() )
@section('meta_description', $bundle->meta_description)
@section('meta_keywords', $bundle->meta_keywords)

@push('after-styles')
    <style>
        .leanth-course.go {
            right: 0;
        }

    </style>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.5.3/plyr.css"/>

@endpush

@section('content')

    <!-- Start of breadcrumb section
        ============================================= -->
    <section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
        <div class="blakish-overlay"></div>
        <div class="container">
            <div class="page-breadcrumb-content text-center">
                <div class="page-breadcrumb-title">
                    <h2 class="breadcrumb-head black bold"><span>{{$bundle->title}}</span></h2>
                </div>
            </div>
        </div>
    </section>
    <!-- End of breadcrumb section
        ============================================= -->

    <!-- Start of course details section
        ============================================= -->
    <section id="course-details" class="course-details-section">
        <div class="container">
            <div class="row">
                <div class="col-md-9">
                    @if(session()->has('danger'))
                        <div class="alert alert-dismissable alert-danger fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {!! session('danger')  !!}
                        </div>
                    @endif
                    @if(session()->has('success'))
                        <div class="alert alert-dismissable alert-success fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{session('success')}}
                        </div>
                    @endif
                    <div class="course-details-item border-bottom-0 mb-0">
                        <div class="course-single-pic mb30">
                            @if($bundle->course_image != "")
                                <img src="{{asset('storage/uploads/'.$bundle->course_image)}}"
                                     alt="">
                            @endif
                        </div>
                        <div class="course-single-text">
                            <div class="course-title mt10 headline relative-position">
                                <h3><a href="{{ route('courses.show', [$bundle->slug]) }}"><b>{{$bundle->title}}</b></a>
                                    @if($bundle->trending == 1)
                                        <span class="trend-badge text-uppercase bold-font"><i
                                                    class="fas fa-bolt"></i> @lang('labels.frontend.badges.trending')</span>
                                    @endif

                                </h3>
                            </div>
                            <div class="course-details-content">
                                <p>
                                    {!! $bundle->description !!}
                                </p>
                                @if(count($bundle->courses)  > 0)
                                <div class="my-4">
                                    @foreach($bundle->courses as $course)
                                        @if($course->mediaVideo && $course->mediavideo->count() > 0)
                                            <div class="course-single-text">
                                                @if($course->mediavideo != null)
                                                    <h3 class="text-dark">{{$course->title}}</h3>
                                                    <div class="course-details-content mt-3">
                                                        <div class="video-container mb-5" data-id="{{$course->mediavideo->id}}">
                                                            @if($course->mediavideo->type == 'youtube')


                                                                <div id="player" class="js-player" data-plyr-provider="youtube"
                                                                     data-plyr-embed-id="{{$course->mediavideo->file_name}}"></div>
                                                            @elseif($course->mediavideo->type == 'vimeo')
                                                                <div id="player" class="js-player" data-plyr-provider="vimeo"
                                                                     data-plyr-embed-id="{{$course->mediavideo->file_name}}"></div>
                                                            @elseif($course->mediavideo->type == 'upload')
                                                                <video poster="" id="player" class="js-player" playsinline controls>
                                                                    <source src="{{$course->mediavideo->url}}" type="video/mp4"/>
                                                                </video>
                                                            @elseif($course->mediavideo->type == 'embed')
                                                                {!! $course->mediavideo->url !!}
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>

                        @if(count($bundle->courses)  > 0)

                            <div class="course-details-category ul-li">
                                <span class="float-none">@lang('labels.frontend.course.courses')</span>
                            </div>
                            <div class="genius-post-item mb55">
                                <div class="tab-container">
                                    <div id="tab1" class="tab-content-1 pt35">
                                        <div class="best-course-area best-course-v2">
                                            <div class="row">
                                                @foreach($bundle->courses as $course)
                                                    <div class="col-md-4">
                                                        <div class="best-course-pic-text relative-position">
                                                            <div class="best-course-pic relative-position"
                                                                 @if($course->course_image != "") style="background-image: url('{{asset('storage/uploads/'.$course->course_image)}}')" @endif>

                                                                @if($course->trending == 1)
                                                                    <div class="trend-badge-2 text-center text-uppercase">
                                                                        <i class="fas fa-bolt"></i>
                                                                        <span>@lang('labels.frontend.badges.trending')</span>
                                                                    </div>
                                                                @endif
                                                                @if($course->free == 1)
                                                                    <div class="trend-badge-3 text-center text-uppercase">
                                                                        <i class="fas fa-bolt"></i>
                                                                        <span>@lang('labels.backend.courses.fields.free')</span>
                                                                    </div>
                                                                @endif

                                                                <div class="course-rate ul-li">
                                                                    <ul>
                                                                        @for($i=1; $i<=(int)$course->rating; $i++)
                                                                            <li><i class="fas fa-star"></i></li>
                                                                        @endfor
                                                                    </ul>
                                                                </div>
                                                                <div class="course-details-btn">
                                                                    <a href="{{ route('courses.show', [$course->slug]) }}">@lang('labels.frontend.course.course_detail')
                                                                        <i class="fas fa-arrow-right"></i></a>
                                                                </div>
                                                                <div class="blakish-overlay"></div>
                                                            </div>
                                                            <div class="best-course-text">
                                                                <div class="course-title mb20 headline relative-position">
                                                                    <h3>
                                                                        <a href="{{ route('courses.show', [$course->slug]) }}">{{$course->title}}</a>
                                                                    </h3>
                                                                </div>
                                                                <div class="course-meta">
                                                            <span class="course-category"><a
                                                                        href="{{route('courses.category',['category'=>$course->category->slug])}}">{{$course->category->name}}</a></span>
                                                                    <span class="course-author"><a href="#">{{ $course->students()->count() }}
                                                                            @lang('labels.frontend.course.students')</a></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                            @endforeach

                                            <!-- /course -->

                                            </div>
                                        </div>
                                    </div><!-- /tab-1 -->
                                </div>
                            </div>

                         @endif
                    <!-- /course-details -->


                    </div>
                    <!-- /market guide -->

                    <div class="course-review">
                        <div class="section-title-2 mb20 headline text-left">
                            <h2>@lang('labels.frontend.course.bundle_reviews')</h2>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="ratting-preview">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="avrg-rating ul-li">
                                                <b>@lang('labels.frontend.course.average_rating')</b>
                                                <span class="avrg-rate">{{$bundle_rating}}</span>
                                                <ul>
                                                    @for($r=1; $r<=$bundle_rating; $r++)
                                                        <li><i class="fas fa-star"></i></li>
                                                    @endfor
                                                </ul>
                                                <b>{{$total_ratings}} @lang('labels.frontend.course.ratings')</b>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="avrg-rating ul-li">
                                                <span><b>@lang('labels.frontend.course.details')</b></span>
                                                @for($r=5; $r>=1; $r--)
                                                    <div class="rating-overview">
                                                        <span class="start-item">{{$r}} @lang('labels.frontend.course.stars')</span>
                                                        <span class="start-bar"></span>
                                                        <span class="start-count">{{$bundle->reviews()->where('rating','=',$r)->get()->count()}}</span>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /review overview -->

                    <div class="couse-comment">
                        <div class="blog-comment-area ul-li about-teacher-2">
                            @if(count($bundle->reviews) > 0)
                                <ul class="comment-list">
                                    @foreach($bundle->reviews as $item)
                                        <li class="d-block">
                                            <div class="comment-avater">
                                                <img src="{{$item->user->picture}}" alt="">
                                            </div>

                                            <div class="author-name-rate">
                                                <div class="author-name float-left">
                                                    @lang('labels.frontend.course.by'):
                                                    <span>{{$item->user->full_name}}</span>
                                                </div>
                                                <div class="comment-ratting float-right ul-li">
                                                    <ul>
                                                        @for($i=1; $i<=(int)$item->rating; $i++)
                                                            <li><i class="fas fa-star"></i></li>
                                                        @endfor
                                                    </ul>
                                                    @if(auth()->check() && ($item->user_id == auth()->user()->id))
                                                        <div>
                                                            <a href="{{route('bundles.review.edit',['id'=>$item->id])}}"
                                                               class="mr-2">@lang('labels.general.edit')</a>
                                                            <a href="{{route('bundles.review.delete',['id'=>$item->id])}}"
                                                               class="text-danger">@lang('labels.general.delete')</a>
                                                        </div>

                                                    @endif
                                                </div>
                                                <div class="time-comment float-right">{{$item->created_at->diffforhumans()}}</div>
                                            </div>
                                            <div class="author-designation-comment">
                                                <p>{{$item->content}}</p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <h4> @lang('labels.frontend.course.no_reviews_yet')</h4>
                            @endif

                            @if ($purchased_bundle)
                                @if(isset($review) || ($is_reviewed == false))
                                    <div class="reply-comment-box">
                                        <div class="review-option">
                                            <div class="section-title-2  headline text-left float-left">
                                                <h2>@lang('labels.frontend.course.add_reviews')</h2>
                                            </div>
                                            <div class="review-stars-item float-right mt15">
                                                <span>@lang('labels.frontend.course.your_rating'): </span>
                                                <div class="rating">
                                                    <label>
                                                        <input type="radio" name="stars" value="1"/>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="stars" value="2"/>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="stars" value="3"/>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="stars" value="4"/>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="stars" value="5"/>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                        <span class="icon"><i class="fas fa-star"></i></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="teacher-faq-form">
                                            @php
                                                if(isset($review)){
                                                    $route = route('bundles.review.update',['id'=>$review->id]);
                                                }else{
                                                    $route = route('bundles.review',['id'=>$bundle->id]);
                                                }
                                            @endphp
                                            <form method="POST"
                                                  action="{{$route}}"
                                                  data-lead="Residential">
                                                @csrf
                                                <input type="hidden" name="rating" id="rating">
                                                <label for="review">@lang('labels.frontend.course.message')</label>
                                                <textarea name="review" class="mb-2" id="review" rows="2"
                                                          cols="20">@if(isset($review)){{$review->content}} @endif</textarea>
                                                <span class="help-block text-danger">{{ $errors->first('review', ':message') }}</span>
                                                <div class="nws-button text-center  gradient-bg text-uppercase">
                                                    <button type="submit"
                                                            value="Submit">@lang('labels.frontend.course.add_review_now')
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="side-bar">
                        <div class="course-side-bar-widget">
                            @php
                             $matched = checkBundleSubscribeOrNot($checkSubcribePlan,$bundle->id);
                             $courseExpire = courseOrBundleExpire('',$bundle->id);
                            @endphp

                            @if (!$purchased_bundle && $matched==false || $courseExpire==false)
                                <h3>
                                    @if($bundle->free == 1)
                                        <span> {{trans('labels.backend.courses.fields.free')}}</span>
                                    @else
                                        @lang('labels.frontend.course.price')
                                        <span>   {{$appCurrency['symbol'].' '.$bundle->price}}</span>
                                    @endif
                                </h3>

                                @if(auth()->check() && (auth()->user()->hasRole('student')) && (Cart::session(auth()->user()->id)->get( $bundle->id)))
                                    <button class="btn genius-btn btn-block text-center my-2 text-uppercase  btn-success text-white bold-font"
                                            type="submit">@lang('labels.frontend.course.added_to_cart')
                                    </button>
                                @elseif(!auth()->check())
                                    @if($bundle->free == 1)
                                        <a id="openLoginModal"
                                           class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font"
                                           data-target="#myModal" href="#">@lang('labels.frontend.course.get_now') <i
                                                    class="fas fa-caret-right"></i></a>
                                    @else
                                        <a id="openLoginModal"
                                           class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font"
                                           data-target="#myModal" href="#">@lang('labels.frontend.course.buy_now') <i
                                                    class="fas fa-caret-right"></i></a>

                                        <a id="openLoginModal"
                                           class="genius-btn btn-block my-2 bg-dark text-center text-white text-uppercase "
                                           data-target="#myModal" href="#">@lang('labels.frontend.course.add_to_cart')
                                            <i
                                                    class="fa fa-shopping-bag"></i></a>

                                        <a id="openLoginModal"
                                           class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font"
                                           data-target="#myModal" href="#">@lang('labels.frontend.course.subscribe')</a>
                                    @endif
                                @elseif(auth()->check() && (auth()->user()->hasRole('student')))
                                    @if($bundle->free == 1)
                                        <form action="{{ route('cart.getnow') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="bundle_id" value="{{ $bundle->id }}"/>
                                            <input type="hidden" name="amount"
                                                   value="{{($bundle->free == 1) ? 0 : $bundle->price}}"/>
                                            <button class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font"
                                                    href="#">@lang('labels.frontend.course.get_now') <i
                                                        class="fas fa-caret-right"></i></button>
                                        </form>
                                    @else
                                        <form action="{{ route('cart.checkout') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="bundle_id" value="{{ $bundle->id }}"/>
                                            <input type="hidden" name="amount" value="{{ $bundle->price}}"/>
                                            <button class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font"
                                                    href="#">@lang('labels.frontend.course.buy_now') <i
                                                        class="fas fa-caret-right"></i></button>
                                        </form>
                                        <form action="{{ route('cart.addToCart') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="bundle_id" value="{{ $bundle->id }}"/>
                                            <input type="hidden" name="amount" value="{{ $bundle->price}}"/>
                                            <button type="submit"
                                                    class="genius-btn btn-block my-2 bg-dark text-center text-white text-uppercase ">
                                                @lang('labels.frontend.course.add_to_cart') <i
                                                        class="fa fa-shopping-bag"></i>
                                            </button>
                                        </form>
                                    @if($bundleInPlan==true)
                                        <a class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font"
                                           href="{{ route('subscription.plans') }}">@lang('labels.frontend.course.subscribe')</a>
                                    @endif

{{--                                        @if(auth()->user()->subscription('default'))--}}
{{--                                            <form action="{{ route('subscription.course_subscribe') }}" method="POST">--}}
{{--                                                @csrf--}}
{{--                                                <input type="hidden" name="bundle_id" value="{{ $bundle->id }}"/>--}}
{{--                                                <input type="hidden" name="amount" value="{{($bundle->free == 1) ? 0 : $bundle->price}}"/>--}}
{{--                                                <button type="submit"--}}
{{--                                                        class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font">--}}
{{--                                                    @lang('labels.frontend.course.subscribe')</button>--}}
{{--                                            </form>--}}
{{--                                        @else--}}
{{--                                            <a class="genius-btn btn-block text-white  gradient-bg text-center text-uppercase  bold-font"--}}
{{--                                               href="{{ route('subscription.plans') }}">@lang('labels.frontend.course.subscribe')</a>--}}
{{--                                        @endif--}}

                                    @endif
                                @else
                                    <h6 class="alert alert-danger"> @lang('labels.frontend.course.buy_note')</h6>
                                @endif
                                <div class="enrolled-student mb-3">
                                    <div class="comment-ratting float-left ul-li">
                                        <ul>
                                            @for($i=1; $i<=(int)$bundle->rating; $i++)
                                                <li><i class="fas fa-star"></i></li>
                                            @endfor
                                        </ul>
                                    </div>
                                    <div class="student-number bold-font">
                                        {{ $bundle->students()->count() }}  @lang('labels.frontend.course.enrolled')
                                    </div>
                                </div>
                            @endif


                        </div>


                        @if($recent_news->count() > 0)
                            <div class="side-bar-widget mt-0">
                                <h2 class="widget-title text-capitalize">@lang('labels.frontend.course.recent_news')</h2>
                                <div class="latest-news-posts">
                                    @foreach($recent_news as $item)
                                        <div class="latest-news-area">
                                            @if($item->image != "")
                                                <div class="latest-news-thumbnile relative-position"
                                                     style="background-image: url({{asset('storage/uploads/'.$item->image)}})">
                                                    <div class="blakish-overlay"></div>
                                                </div>
                                            @endif


                                            <div class="date-meta">
                                                <i class="fas fa-calendar-alt"></i> {{$item->created_at->format('d M Y')}}
                                            </div>
                                            <h3 class="latest-title bold-font"><a
                                                        href="{{route('blogs.index',['slug'=>$item->slug.'-'.$item->id])}}">{{$item->title}}</a>
                                            </h3>
                                        </div>
                                        <!-- /post -->
                                    @endforeach


                                    <div class="view-all-btn bold-font">
                                        <a href="{{route('blogs.index')}}">@lang('labels.frontend.course.view_all_news')
                                            <i
                                                    class="fas fa-chevron-circle-right"></i></a>
                                    </div>
                                </div>
                            </div>

                        @endif

                        @if($global_featured_course != "")
                            <div class="side-bar-widget">
                                <h2 class="widget-title text-capitalize">@lang('labels.frontend.course.featured_course')</h2>
                                <div class="featured-course">
                                    <div class="best-course-pic-text relative-position pt-0">
                                        <div class="best-course-pic relative-position "
                                             @if($global_featured_course->course_image != "") style="background-image: url({{asset('storage/uploads/'.$global_featured_course->course_image)}})" @endif>

                                            @if($global_featured_course->trending == 1)
                                                <div class="trend-badge-2 text-center text-uppercase">
                                                    <i class="fas fa-bolt"></i>
                                                    <span>@lang('labels.frontend.badges.trending')</span>
                                                </div>
                                            @endif

                                            @if($global_featured_course->free == 1)
                                                <div class="trend-badge-3 text-center text-uppercase">
                                                    <i class="fas fa-bolt"></i>
                                                    <span>@lang('labels.backend.courses.fields.free')</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="best-course-text" style="left: 0;right: 0;">
                                            <div class="course-title mb20 headline relative-position">
                                                <h3>
                                                    <a href="{{ route('courses.show', [$global_featured_course->slug]) }}">{{$global_featured_course->title}}</a>
                                                </h3>
                                            </div>
                                            <div class="course-meta">
                                                <span class="course-category"><a
                                                            href="{{route('courses.category',['category'=>$global_featured_course->category->slug])}}">{{$global_featured_course->category->name}}</a></span>
                                                <span class="course-author">{{ $global_featured_course->students()->count() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End of course details section
        ============================================= -->

@endsection

@push('after-scripts')
    <script src="https://cdn.plyr.io/3.5.3/plyr.polyfilled.js"></script>
    @include('frontend.courses.partials.pause-inactive-media')

    <script>
        const player = new Plyr('#player');
        $(document).on('change', 'input[name="stars"]', function () {
            $('#rating').val($(this).val());
        })
                @if(isset($review))
        var rating = "{{$review->rating}}";
        $('input[value="' + rating + '"]').prop("checked", true);
        $('#rating').val(rating);
        @endif
    </script>
@endpush
