<!-- Start of footer area
    ============================================= -->
@php
    $footer_data = json_decode(config('footer_data'));
@endphp

@if($footer_data != "")
<footer>
    <section id="footer-area" class="footer-area-section">
        <div class="container">
            <div class="footer-content pb10">
                <div class="row">
                @if(config('contact_data') != "")
                    @php
                        $contact_data = contact_data(config('contact_data'));
                    @endphp

                    <div class="col-3" style="display: none !important;">



                            <div class="footer-widget ">
                                <div class="footer-logo">
                                    <img src="{{ asset('img/logo.png') }}" alt="logo">
                                </div>
                                @if($footer_data->short_description->status == 1)
                                    <div class="footer-about-text">
                                        <p>{!! $footer_data->short_description->text !!} </p>
                                    </div>
                                @endif
                            </div>
                        <div class="dload">

                            <h5 class="mb-4">Download App</h5>
                            <ul class="footer-list">
                                <li><a href=""><img src="{{asset('img/google-play.png')}}" class="mb-3"></a></li>
                                <li><a href=""><img src="{{asset('img/apple.png')}}"></a></li>
                            </ul>
                        </div> 
                    
                    </div> 

                    <div class="col-6 col-lg-3">
                        <div class="ftlogo">
                        <img src="{{asset('assets/img/logo.png')}}"> 
                        </div>
                        </div>

                    <div class="col-6 col-lg-4">
                        <div class="contact-left-content ">
                            <div class="w-100">
                               
                                <h5 class="mb-4">@lang('Get In Touch')</h5>
                            </div>
                            <div class="contact-address">
                                @if(($contact_data["primary_address"]["status"] == 1) || ($contact_data["secondary_address"]["status"] == 1))
                                    <div class="contact-address-details">   

                                        <div class="address-icon relative-position text-center float-left">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="address-details ul-li-block">
                                            <ul>
                                                @if($contact_data["primary_address"]["status"] == 1)
                                                    <li>
                                                        {{$contact_data["primary_address"]["value"]}}
                                                    </li>
                                                @endif
 
                                            </ul>
                                        </div>
                                    </div>
                                @endif

                                @if(($contact_data["primary_phone"]["status"] == 1) || ($contact_data["secondary_phone"]["status"] == 1))
                                    <div class="contact-address-details">
                                        <div class="address-icon relative-position text-center float-left">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                        <div class="address-details ul-li-block">
                                            <ul>
                                                @if($contact_data["primary_phone"]["status"] == 1)
                                                    <li>
                                                        {{$contact_data["primary_phone"]["value"]}}
                                                    </li>
                                                @endif

                                                @if($contact_data["secondary_phone"]["status"] == 1)
                                                    <li>
                                                        {{$contact_data["secondary_phone"]["value"]}}
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                @endif

                                @if(($contact_data["primary_email"]["status"] == 1) || ($contact_data["secondary_email"]["status"] == 1))

                                    <div class="contact-address-details">
                                        <div class="address-icon relative-position text-center float-left">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="address-details ul-li-block">
                                            <ul>
                                                @if($contact_data["primary_email"]["status"] == 1)
                                                    <li>
                                                        {{$contact_data["primary_email"]["value"]}}
                                                    </li>
                                                @endif

                                                @if($contact_data["secondary_email"]["status"] == 1)
                                                    <li>
                                                        {{$contact_data["secondary_email"]["value"]}}
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="footer-social ul-li" style="display: none !important;">
                            <h5 class=""> Social Network</h5>
                            <ul>
                                @foreach($footer_data->social_links->links as $item)
                                    <li><a href="{{$item->link}}"><i class="{{$item->icon}}"></i></a></li>
                                @endforeach

                            </ul>
                        </div>


                    </div>
                    <div class="col-6 col-lg-3" >
                        <h5 class="mb-4">@lang('Quick Links')</h5>
                        <ul class="footer-list">
                        <li><a href="">@lang('Create Account')</a></li>
                            <li><a href="">@lang('Go To Premium')</a></li>
                            <li><a href="">@lang('Terms & Conditions')</a></li>
                            <li><a href="">@lang('Privacy Policy')</a></li>
                            <li><a href="">@lang('Get Help')</a></li>
                            <li><a href="">@lang('Become a Teacher')</a></li>
                            <li><a href="">@lang('Become a Trainee')</a></li>
                        </ul>
                    </div>

                    <div class="col-6 col-lg-2" >
                         <div class="space1"></div>
                        <ul class="footer-list">
                        <li><a href="">@lang('Home')</a></li>
                            <li><a href="">@lang('About Us')</a></li>
                            <li><a href="">@lang('Courses')</a></li>
                            <li><a href="">@lang('Blog')</a></li>
                            <li><a href="">@lang('Privacy Policy')</a></li>
                            <li><a href="">@lang('Terms & Conditions')</a></li>
                            <li><a href="">@lang('Contact Us')</a></li>
                        </ul>
                    </div>
                    
                   
    <!--
                    <div class="col-lg-5 col-sm-12 mtb-space" >
                        
                  
                         
                        @if($contact_data["location_on_map"]["status"] == 1)
                            
                                <div id="contact-map" class="contact-map-section">
                                    {!! $contact_data["location_on_map"]["value"] !!}
                                </div>
                            
                        @endif
                        @else
                            <h4>@lang('labels.general.no_data_available')</h4>
                        @endif
                    

                

                         
                    </div>
                     
                    </div> -->
                    
                 
            </div>
            <!-- /footer-widget-content -->
            <div class="footer-social-subscribe mb65 bg-first">
                <div class="row">
                    {{-- @if(($footer_data->social_links->status == 1) && (count($footer_data->social_links->links) > 0))
                        <div class="col-md-4">
                            <div class="footer-social ul-li ">
                                <h2 class="widget-title">@lang('labels.frontend.layouts.partials.social_network')</h2>
                                <ul>
                                    @foreach($footer_data->social_links->links as $item)
                                        <li><a href="{{$item->link}}"><i class="{{$item->icon}}"></i></a></li>
                                    @endforeach

                                </ul>
                            </div>
                        </div>
                    @endif --}}

                    {{-- @if($footer_data->newsletter_form->status == 1)
                        <div class="col-md-8">
                            <div class="subscribe-form ml-0 ">
                                <h2 class="widget-title">@lang('labels.frontend.layouts.partials.subscribe_newsletter')</h2>

                                <div class="subs-form relative-position">
                                    <form action="{{route("subscribe")}}" method="post">
                                        @csrf
                                        <input class="email" required name="subs_email" type="email" placeholder="@lang('labels.frontend.layouts.partials.email_address').">
                                        <div class="nws-button text-center  gradient-bg text-uppercase">
                                            <button type="submit" value="Submit">@lang('labels.frontend.layouts.partials.subscribe_now')</button>
                                        </div>
                                        @if($errors->has('email'))
                                            <p class="text-danger text-left">{{$errors->first('email')}}</p>
                                        @endif
                                    </form>

                                </div>
                            </div>
                        </div>
                    @endif --}}
                </div>
            </div>

            
        </div>
    </section>

    @if($footer_data->bottom_footer->status == 1)
            <div class="copy-right-menu">
            <div class="container">
                <div class="row foot-sm-center">
                    @if($footer_data->copyright_text->status == 1)
                    <div class="col-md-6">
                        <div class="copy-right-text">
                            {{-- <p>Powered By <a href="https://www.neonlms.com/" target="_blank" class="mr-4"> NeonLMS</a>  --}}
                                 <p>{!!  $footer_data->copyright_text->text !!}</p>
                        </div>
                    </div>
                    @endif
                    @if(($footer_data->bottom_footer_links->status == 1) && (count($footer_data->bottom_footer_links->links) > 0))
                    <div class="col-md-6">
                        <div class="copy-right-menu-item float-right ul-li">
                            <ul>
                                @foreach($footer_data->bottom_footer_links->links as $item)
                                <li><a href="{{$item->link}}">{{$item->label}}</a></li>
                                @endforeach
                                @if(config('show_offers'))
                                    <li><a href="{{route('frontend.offers')}}">@lang('labels.frontend.layouts.partials.offers')</a> </li>
                                @endif
                                <li><a href="{{route('frontend.certificates.getVerificationForm')}}">@lang('labels.frontend.layouts.partials.certificate_verification')</a></li>
                            </ul>
                        </div>
                    </div>
                     @endif
                </div>
            </div></div>
            @endif


</footer>
@endif
<!-- End of footer area
============================================= -->

@push('after-scripts')
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <script>
        window.addEventListener('load', function () {
            alertify.set('notifier', 'position', 'top-right');
        });

        function showNotice(type, message) {
            var alertifyFunctions = {
                'success': alertify.success,
                'error': alertify.error,
                'info': alertify.message,
                'warning': alertify.warning
            };

            alertifyFunctions[type](message, 10);
        }
    </script>
    <script src="{{ asset('js/wishlist.js') }}"></script>
    <style>
        .alertify-notifier .ajs-message{
            color: #ffffff;
        }
    </style>
@endpush
