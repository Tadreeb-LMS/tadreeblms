@extends('frontend-rtl.layouts.app'.config('theme_layout'))
@push('after-styles')
    <style>
        .couse-pagination li.active {
            color: #333333!important;
            font-weight: 700;
        }
        .page-link {
            position: relative;
            display: block;
            padding: .5rem .75rem;
            margin-left: -1px;
            line-height: 1.25;
            color: #c7c7c7;
            background-color: white;
            border: none;
        }
        .page-item.active .page-link {
            z-index: 1;
            color: #333333;
            background-color:white;
            border:none;

        }
        ul.pagination{
            display: inline;
            text-align: center;
        }
        .cat-item.active{
            background: black;
            color: white;
            font-weight: bold;
        }
    </style>
@endpush
@section('content')

    <!-- Start of breadcrumb section
    ============================================= -->
    <section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
        <div class="blakish-overlay"></div>
        <div class="container">
            <div class="page-breadcrumb-content text-center">
                <div class="page-breadcrumb-title">
                    <h2 class="breadcrumb-head black bold">"{{$q}}" <span>@lang('labels.frontend.search_result.blog')</span></h2>

                </div>
            </div>
        </div>
    </section>
    <!-- End of breadcrumb section
        ============================================= -->

    <!-- Start of blog content
        ============================================= -->
    <section id="blog-item" class="blog-item-post">
        <div class="container">
            <div class="blog-content-details">
                <div class="row">
                    <div class="col-md-9">
                        <div class="blog-post-content">
                            <div class="short-filter-tab">

                                <div class="tab-button blog-button ul-li text-center float-left">
                                    <ul class="product-tab">
                                        <li class="active" rel="tab1"><i class="fas fa-th"></i></li>
                                        <li rel="tab2"><i class="fas fa-list"></i></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="genius-post-item">
                                <div class="tab-container">
                                    @if(count($blogs) > 0)
                                        <div id="tab1" class="tab-content-1 pt35">
                                            <div class="row">
                                                @foreach($blogs as $item)
                                                    <div class="col-md-6">
                                                        <div class="blog-post-img-content">
                                                            <div class="blog-img-date relative-position">
                                                                <div class="blog-thumnile" style="background-image: url({{asset('storage/uploads/'.$item->image)}})">

                                                                </div>
                                                                <div class="course-price text-center gradient-bg">
                                                                    <span>{{$item->created_at->format('d M Y')}}</span>
                                                                </div>
                                                            </div>
                                                            <div class="blog-title-content headline">
                                                                <h3><a href="{{route('blogs.index',['slug'=> $item->slug.'-'.$item->id])}}">{{$item->title}}</a></h3>
                                                                <div class="blog-content">
                                                                    {!!  mb_substr($item->content,0,100).'...'  !!}
                                                                </div>

                                                                <div class="view-all-btn bold-font">
                                                                    <a href="{{route('blogs.index',['slug'=> $item->slug.'-'.$item->id])}}">@lang('labels.general.read_more') <i
                                                                                class="fas fa-chevron-circle-right"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                @endforeach



                                            </div>
                                        </div><!-- 1st tab -->
                                        <div id="tab2" class="tab-content-1 pt35">
                                            <div class="blog-list-view">
                                                @foreach($blogs as $item)
                                                    <div class="list-blog-item">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="blog-post-img-content">
                                                                    <div class="blog-img-date relative-position">
                                                                        <div class="blog-thumnile" style="background-image: url({{asset('storage/uploads/'.$item->image)}})">

                                                                        </div>
                                                                        <div class="course-price text-center gradient-bg">
                                                                            <span>{{$item->created_at->format('d M Y')}}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="blog-title-content headline">
                                                                    <h3><a href="{{route('blogs.index',['slug'=> $item->slug.'-'.$item->id])}}">{{$item->title}}</a>
                                                                    </h3>
                                                                    <div class="blog-content">
                                                                        {!!  mb_substr($item->content,0,100).'...'  !!}
                                                                    </div>

                                                                    <div class="view-all-btn bold-font">
                                                                        <a href="{{route('blogs.index',['slug'=> $item->slug.'-'.$item->id])}}">@lang('labels.general.read_more') <i
                                                                                    class="fas fa-chevron-circle-right"></i></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div><!-- 2nd tab -->
                                    @else
                                        <h4>@lang('labels.general.no_data_available')</h4>

                                    @endif


                                </div>
                            </div>


                            <div class="couse-pagination text-center ul-li">
                                {{ $blogs->links() }}
                            </div>
                        </div>
                    </div>
                    @include('frontend.blogs.partials.sidebar')
                </div>
            </div>
        </div>
    </section>
    <!-- End of blog content
        ============================================= -->

@endsection