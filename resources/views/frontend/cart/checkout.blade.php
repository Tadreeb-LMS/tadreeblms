@extends('frontend.layouts.app'.config('theme_layout'))
@section('title', trans('labels.frontend.cart.payment_status').' | '.app_name())

@push('after-styles')
    <style>
        input[type="radio"] {
            display: inline-block !important;
        }

        .course-rate li {
            color: #ffc926 !important;
        }

        #applyCoupon {
            box-shadow: none !important;
            color: #fff !important;
            font-weight: bold;
        }

        #coupon.warning {
            border: 1px solid red;
        }

        .purchase-list .in-total {
            font-size: 18px;
        }

        #coupon-error {
            color: red;
        }
        .in-total:not(:first-child):not(:last-child){
            font-size: 15px;
        }

    </style>

    <script src='https://js.stripe.com/v2/' type='text/javascript'></script>
@endpush
@section('content')

    <!-- Start of breadcrumb section
        ============================================= -->
    <section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
        <div class="blakish-overlay"></div>
        <div class="container">
            <div class="page-breadcrumb-content text-center">
                <div class="page-breadcrumb-title">
                    <h2 class="breadcrumb-head black bold"><span>@lang('labels.frontend.cart.checkout')</span></h2>
                </div>
            </div>
        </div>
    </section>
    <!-- End of breadcrumb section
        ============================================= -->


    <!-- Start of Checkout content
        ============================================= -->
    <section id="checkout" class="checkout-section">
        <div class="container">
            <div class="section-title mb45 headline text-center">
                <span class="subtitle text-uppercase">@lang('labels.frontend.cart.your_shopping_cart')</span>
                <h2>@lang('labels.frontend.cart.complete_your_purchases')</h2>
            </div>
            <div class="checkout-content">
                @if(session()->has('danger'))
                    <div class="alert alert-dismissable alert-danger fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {!! session('danger')  !!}
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-9">
                        <div class="order-item mb30 course-page-section">
                            <div class="section-title-2  headline text-left">
                                <h2>@lang('labels.frontend.cart.order_item')</h2>
                            </div>

                            <div class="course-list-view table-responsive">
                                <table class="table">

                                    <thead>
                                    <tr class="list-head text-uppercase">
                                        <th>@lang('labels.frontend.cart.product_name')</th>
                                        <th>@lang('labels.frontend.cart.product_type')</th>
                                        <th>@lang('labels.frontend.cart.starts')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(count($courses) > 0)
                                        @foreach($courses as $course)
                                            <tr class="position-relative">

                                                <td>
                                                    <a style="right: 3%;" class="text-danger position-absolute"
                                                       href="{{route('cart.remove',['course'=>$course])}}"><i
                                                                class="fa fa-times"></i></a>
                                                    <div class="course-list-img-text">
                                                        <div class="course-list-img"
                                                             @if($course->course_image != "") style="background-image: url({{asset('storage/uploads/'.$course->course_image)}})" @endif >

                                                        </div>
                                                        <div class="course-list-text">
                                                            <h3>
                                                                <a href="{{ route('courses.show', [$course->slug]) }}">{{$course->title}}</a>
                                                            </h3>
                                                            <div class="course-meta">
                                                                <span class="course-category bold-font"><a
                                                                            href="#">@if($course->free == 1)
                                                                            <span>{{trans('labels.backend.bundles.fields.free')}}</span>
                                                                        @else
                                                                            <span> {{$appCurrency['symbol'].' '.$course->price}}</span>
                                                                        @endif</a></span>
                                                                <span class="bold-font">{{$course->category->name}}</span>
                                                                <div class="course-rate ul-li">
                                                                    <ul>
                                                                        @for($i=1; $i<=(int)$course->rating; $i++)
                                                                            <li><i class="fas fa-star"></i></li>
                                                                        @endfor
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="course-type-list">
                                                        <span>{{class_basename($course)}}</span>
                                                    </div>
                                                </td>
                                                <td>{{($course->start_date != "") ? $course->start_date : __('labels.general.not_available')}}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4">@lang('labels.frontend.cart.empty_cart')</td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if(count($courses) > 0)
                            @if((config('services.stripe.active') == 0) && (config('paypal.active') == 0) && (config('payment_offline_active') == 0) && (config('services.instamojo.active') == 0) && (config('services.razorpay.active') == 0) && (config('services.cashfree.active') == 0) && (config('services.payu.active') == 0) && (config('flutter.active') == 0))
                                <div class="order-payment">
                                    <div class="section-title-2 headline text-left">
                                        <h2>@lang('labels.frontend.cart.no_payment_method')</h2>
                                    </div>
                                </div>
                            @else
                                <div class="order-payment">
                                    <div class="section-title-2  headline text-left">
                                        <h2>@lang('labels.frontend.cart.order_payment')</h2>
                                    </div>
                                    <div id="accordion">
                                        @if(config('services.stripe.active') == 1)
                                            <div class="payment-method w-100 mb-0">
                                                <div class="payment-method-header">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="method-header-text">
                                                                <div class="radio">
                                                                    <label>
                                                                        <input data-toggle="collapse"
                                                                               href="#collapsePaymentOne"
                                                                               type="radio" name="paymentMethod"
                                                                               value="1"
                                                                               checked>
                                                                        @lang('labels.frontend.cart.payment_cards')
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="payment-img float-right">
                                                                <img src="{{asset('assets/img/banner/p-1.jpg')}}"
                                                                     alt="">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="check-out-form collapse show" id="collapsePaymentOne"
                                                     data-parent="#accordion">


                                                    <form accept-charset="UTF-8"
                                                          action="{{route('cart.stripe.payment')}}"
                                                          class="require-validation" data-cc-on-file="false"
                                                          data-stripe-publishable-key="{{config('services.stripe.key')}}"
                                                          id="payment-form"
                                                          method="POST">

                                                        <div style="margin:0;padding:0;display:inline">
                                                            <input name="utf8" type="hidden"
                                                                   value="✓"/>
                                                            @csrf
                                                        </div>


                                                        <div class="payment-info">
                                                            <label class=" control-label">@lang('labels.frontend.cart.name_on_card')
                                                                :</label>
                                                            <input type="text" autocomplete='off'
                                                                   class="form-control required card-name"
                                                                   placeholder="@lang('labels.frontend.cart.name_on_card_placeholder')"
                                                                   value="">
                                                        </div>
                                                        <div class="payment-info">
                                                            <label class=" control-label">@lang('labels.frontend.cart.card_number')
                                                                :</label>
                                                            <input autocomplete='off' type="text"
                                                                   class="form-control required card-number"
                                                                   placeholder="@lang('labels.frontend.cart.card_number_placeholder')"
                                                                   value="">
                                                        </div>
                                                        <div class="payment-info input-2">
                                                            <label class=" control-label">@lang('labels.frontend.cart.cvv')
                                                                :</label>
                                                            <input type="text" class="form-control card-cvc required"
                                                                   placeholder="@lang('labels.frontend.cart.cvv')"
                                                                   value="">
                                                        </div>
                                                        <div class="payment-info input-2">
                                                            <label class=" control-label">@lang('labels.frontend.cart.expiration_date')
                                                                :</label>
                                                            <input autocomplete='off' type="text"
                                                                   class="form-control required card-expiry-month"
                                                                   placeholder="@lang('labels.frontend.cart.mm')"
                                                                   value="">
                                                            <input autocomplete='off' type="text"
                                                                   class="form-control required card-expiry-year"
                                                                   placeholder="@lang('labels.frontend.cart.yy')"
                                                                   value="">
                                                        </div>
                                                        <button type="submit"
                                                                class="text-white genius-btn mt25 gradient-bg text-center text-uppercase  bold-font">
                                                            @lang('labels.frontend.cart.pay_now') <i
                                                                    class="fas fa-caret-right"></i>
                                                        </button>
                                                        <div class="row mt-3">
                                                            <div class="col-12 error form-group d-none">
                                                                <div class="alert-danger alert">
                                                                    @lang('labels.frontend.cart.stripe_error_message')
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif

                                        @if(config('paypal.active') == 1)
                                            <div class="payment-method w-100 mb-0">
                                                <div class="payment-method-header">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="method-header-text">
                                                                <div class="radio">
                                                                    <label>
                                                                        <input data-toggle="collapse"
                                                                               href="#collapsePaymentTwo"
                                                                               type="radio" name="paymentMethod"
                                                                               value="2">
                                                                        @lang('labels.frontend.cart.paypal')
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="payment-img float-right">
                                                                <img src="{{asset('assets/img/banner/p-2.jpg')}}"
                                                                     alt="">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="check-out-form collapse disabled" id="collapsePaymentTwo"
                                                     data-parent="#accordion">
                                                    <form class="w3-container w3-display-middle w3-card-4 "
                                                          method="POST"
                                                          id="payment-form" action="{{route('cart.paypal.payment')}}">
                                                        {{ csrf_field() }}
                                                        <p> @lang('labels.frontend.cart.pay_securely_paypal')</p>

                                                        <button type="submit"
                                                                class="text-white genius-btn mt25 gradient-bg text-center text-uppercase  bold-font">
                                                            @lang('labels.frontend.cart.pay_now') <i
                                                                    class="fas fa-caret-right"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                        @if(config('flutter.active') == 1)
                                        <div class="payment-method w-100 mb-0">
                                            <div class="payment-method-header">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="method-header-text">
                                                            <div class="radio">
                                                                <label>
                                                                    <input data-toggle="collapse"
                                                                           href="#collapsePaymentTwo"
                                                                           type="radio" name="paymentMethod"
                                                                           value="8">
                                                                    @lang('labels.frontend.cart.flutter')
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="payment-img float-right">

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="check-out-form collapse disabled" id="collapsePaymentTwo"
                                                 data-parent="#accordion">
                                                <form class="w3-container w3-display-middle w3-card-4 "
                                                      method="POST"
                                                      id="paymentForm" action="{{route('cart.flutter.payment')}}">
                                                    {{ csrf_field() }}
                                                    <p> @lang('labels.frontend.cart.pay_securely_flutter')</p>

                                                    <input type="text" autocomplete='off'
                                                           class="form-control"
                                                           name="user_phone"
                                                           placeholder="@lang('labels.frontend.cart.user_phone')"
                                                           value="" required>

                                                    <button type="submit"
                                                            class="text-white genius-btn mt25 gradient-bg text-center text-uppercase  bold-font">
                                                        @lang('labels.frontend.cart.pay_now') <i
                                                                class="fas fa-caret-right"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        @endif

                                        @if(config('services.instamojo.active') == 1)
                                            <div class="payment-method w-100 mb-0">
                                                <div class="payment-method-header">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="method-header-text">
                                                                <div class="radio">
                                                                    <label>
                                                                        <input data-toggle="collapse"
                                                                               href="#collapsePaymentFour"
                                                                               type="radio" name="paymentMethod"
                                                                               value="4">
                                                                        @lang('labels.frontend.cart.instamojo')
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="payment-img float-right">
                                                                <img src="{{asset('assets/img/banner/p-3.png')}}"
                                                                     alt="">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="check-out-form collapse disabled" id="collapsePaymentFour"
                                                     data-parent="#accordion">
                                                    <form class="w3-container w3-display-middle w3-card-4 "
                                                          method="POST"
                                                          action="{{route('cart.instamojo.payment')}}">
                                                        {{ csrf_field() }}
                                                        <p> @lang('labels.frontend.cart.pay_securely_instamojo')</p>
                                                        <div class="payment-info">
                                                            <label class=" control-label">@lang('labels.frontend.cart.user_name')
                                                                :</label>
                                                            <input type="text" autocomplete='off'
                                                                   class="form-control"
                                                                   name="user_name"
                                                                   placeholder="@lang('labels.frontend.cart.user_name')"
                                                                   value="{{ auth()->user()->name }}" required>
                                                        </div>
                                                        <div class="payment-info">
                                                            <label class=" control-label">@lang('labels.frontend.cart.user_email')
                                                                :</label>
                                                            <input type="text" autocomplete='off'
                                                                   class="form-control"
                                                                   name="user_email"
                                                                   placeholder="@lang('labels.frontend.cart.user_email')"
                                                                   value="{{ auth()->user()->email }}" required>
                                                        </div>
                                                        <div class="payment-info">
                                                            <label class=" control-label">@lang('labels.frontend.cart.user_phone')
                                                                :</label>
                                                            <input type="text" autocomplete='off'
                                                                   class="form-control"
                                                                   name="user_phone"
                                                                   placeholder="@lang('labels.frontend.cart.user_phone')"
                                                                   value="" required>
                                                        </div>

                                                        <button type="submit"
                                                                class="text-white genius-btn mt25 gradient-bg text-center text-uppercase  bold-font">
                                                            @lang('labels.frontend.cart.pay_now') <i
                                                                class="fas fa-caret-right"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif

                                        @if(config('services.razorpay.active') == 1)
                                            <div class="payment-method w-100 mb-0">
                                                <div class="payment-method-header">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="method-header-text">
                                                                <div class="radio">
                                                                    <label>
                                                                        <input data-toggle="collapse"
                                                                               href="#collapsePaymentFive"
                                                                               type="radio" name="paymentMethod"
                                                                               value="5">
                                                                        @lang('labels.frontend.cart.razorpay')
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="payment-img float-right">
                                                                <img src="{{asset('assets/img/banner/p-4.png')}}"
                                                                     alt="">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="check-out-form collapse disabled" id="collapsePaymentFive"
                                                     data-parent="#accordion">

                                                    <form class="w3-container w3-display-middle w3-card-4 "
                                                          method="POST"
                                                          action="{{route('cart.razorpay.payment')}}">
                                                        {{ csrf_field() }}
                                                        <p> @lang('labels.frontend.cart.pay_securely_razorpay')</p>

                                                        <button type="submit"
                                                                class="text-white genius-btn mt25 gradient-bg text-center text-uppercase  bold-font">
                                                            @lang('labels.frontend.cart.pay_now') <i
                                                                class="fas fa-caret-right"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif

                                        @if(config('services.cashfree.active') == 1)
                                            <div class="payment-method w-100 mb-0">
                                                <div class="payment-method-header">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="method-header-text">
                                                                <div class="radio">
                                                                    <label>
                                                                        <input data-toggle="collapse"
                                                                               href="#collapsePaymentSix"
                                                                               type="radio" name="paymentMethod"
                                                                               value="6">
                                                                        @lang('labels.frontend.cart.cashfree')
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="payment-img float-right">
                                                                <img src="{{asset('assets/img/banner/p-6.png')}}"
                                                                     alt="">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="check-out-form collapse disabled" id="collapsePaymentSix"
                                                     data-parent="#accordion">

                                                    <form class="w3-container w3-display-middle w3-card-4 "
                                                          method="POST"
                                                          action="{{route('cart.cashfree.payment')}}">
                                                        {{ csrf_field() }}
                                                        <p> @lang('labels.frontend.cart.pay_securely_cashfree')</p>

                                                        <div class="payment-info">
                                                            <label class=" control-label">@lang('labels.frontend.cart.user_name')
                                                                :</label>
                                                            <input type="text" autocomplete='off'
                                                                   class="form-control"
                                                                   name="user_name"
                                                                   placeholder="@lang('labels.frontend.cart.user_name')"
                                                                   value="{{ auth()->user()->name }}" required>
                                                        </div>
                                                        <div class="payment-info">
                                                            <label class=" control-label">@lang('labels.frontend.cart.user_email')
                                                                :</label>
                                                            <input type="text" autocomplete='off'
                                                                   class="form-control"
                                                                   name="user_email"
                                                                   placeholder="@lang('labels.frontend.cart.user_email')"
                                                                   value="{{ auth()->user()->email }}" required>
                                                        </div>
                                                        <div class="payment-info">
                                                            <label class=" control-label">@lang('labels.frontend.cart.user_phone')
                                                                :</label>
                                                            <input type="text" autocomplete='off'
                                                                   class="form-control"
                                                                   name="user_phone"
                                                                   placeholder="@lang('labels.frontend.cart.user_phone')"
                                                                   value="" required>
                                                        </div>
                                                        <button type="submit"
                                                                class="text-white genius-btn mt25 gradient-bg text-center text-uppercase  bold-font">
                                                            @lang('labels.frontend.cart.pay_now') <i
                                                                class="fas fa-caret-right"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif

                                        @if(config('services.payu.active') == 1)
                                            <div class="payment-method w-100 mb-0">
                                                <div class="payment-method-header">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="method-header-text">
                                                                <div class="radio">
                                                                    <label>
                                                                        <input data-toggle="collapse"
                                                                               href="#collapsePaymentPayU"
                                                                               type="radio" name="paymentMethod"
                                                                               value="5">
                                                                        @lang('labels.frontend.cart.payu')
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="payment-img float-right">
                                                                <img src="{{asset('assets/img/banner/p-7.png')}}"
                                                                     alt="">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="check-out-form collapse disabled" id="collapsePaymentPayU"
                                                     data-parent="#accordion">

                                                    <form class="w3-container w3-display-middle w3-card-4 "
                                                          method="POST"
                                                          action="{{route('cart.payu.payment')}}">
                                                        {{ csrf_field() }}
                                                        <p> @lang('labels.frontend.cart.pay_securely_payu')</p>

                                                        <div class="payment-info">
                                                            <label class=" control-label">@lang('labels.frontend.cart.user_name')
                                                                :</label>
                                                            <input type="text" autocomplete='off'
                                                                   class="form-control"
                                                                   name="user_name"
                                                                   placeholder="@lang('labels.frontend.cart.user_name')"
                                                                   value="{{ auth()->user()->name }}" required>
                                                        </div>
                                                        <div class="payment-info">
                                                            <label class=" control-label">@lang('labels.frontend.cart.user_email')
                                                                :</label>
                                                            <input type="text" autocomplete='off'
                                                                   class="form-control"
                                                                   name="user_email"
                                                                   placeholder="@lang('labels.frontend.cart.user_email')"
                                                                   value="{{ auth()->user()->email }}" required>
                                                        </div>
                                                        <div class="payment-info">
                                                            <label class=" control-label">@lang('labels.frontend.cart.user_phone')
                                                                :</label>
                                                            <input type="text" autocomplete='off'
                                                                   class="form-control"
                                                                   name="user_phone"
                                                                   placeholder="@lang('labels.frontend.cart.user_phone')"
                                                                   value="" required>
                                                        </div>
                                                        <button type="submit"
                                                                class="text-white genius-btn mt25 gradient-bg text-center text-uppercase  bold-font">
                                                            @lang('labels.frontend.cart.pay_now') <i
                                                                class="fas fa-caret-right"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif

                                        @if(config('payment_offline_active') == 1)
                                            <div class="payment-method w-100 mb-0">
                                                <div class="payment-method-header">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="method-header-text">
                                                                <div class="radio">
                                                                    <label>
                                                                        <input data-toggle="collapse"
                                                                               href="#collapsePaymentThree" type="radio"
                                                                               name="paymentMethod" value="3">
                                                                        @lang('labels.frontend.cart.offline_payment')
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="check-out-form collapse disabled" id="collapsePaymentThree"
                                                     data-parent="#accordion">
                                                    <p> @lang('labels.frontend.cart.offline_payment_note')</p>
                                                    <p>{{ config('payment_offline_instruction')  }}</p>
                                                    <form method="post" action="{{route('cart.offline.payment')}}">
                                                        @csrf
                                                        <button type="submit"
                                                                class="text-white genius-btn mt25 gradient-bg text-center text-uppercase  bold-font">
                                                            @lang('labels.frontend.cart.request_assistance') <i
                                                                    class="fas fa-caret-right"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="terms-text pb45 mt25">
                                        <p>@lang('labels.frontend.cart.confirmation_note')</p>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="col-md-3">
                        <div class="side-bar-widget first-widget">
                            <h2 class="widget-title text-capitalize">@lang('labels.frontend.cart.order_detail')</h2>
                            <div class="sub-total-item">
                                @if(count($courses) > 0)
                                    <div class="purchase-list py-3 ul-li-block">
                                        @include('frontend.cart.partials.order-stats')
                                    </div>
                                @else
                                    <div class="purchase-list mt15 ul-li-block">

                                        <div class="in-total text-uppercase">@lang('labels.frontend.cart.total') <span>{{$appCurrency['symbol']}}
                                                0.00</span></div>
                                    </div>

                                @endif
                            </div>
                        </div>
                        @if($global_featured_course != "")
                            <div class="side-bar-widget">
                                <h2 class="widget-title text-capitalize">@lang('labels.frontend.blog.featured_course')</h2>
                                <div class="featured-course">
                                    <div class="best-course-pic-text relative-position pt-0">
                                        <div class="best-course-pic relative-position "
                                             style="background-image: url({{asset('storage/uploads/'.$global_featured_course->course_image)}})">

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
    <!-- End  of Checkout content
        ============================================= -->
    @if(session()->get('razorpay'))

        <button id="razor-pay-btn" class="d-none">Pay</button>
        <form id="razorpay-callback-form" method="post" action="{{ route('cart.razorpay.status') }}">
            @csrf
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
            <input type="hidden" name="razorpay_signature" id="razorpay_signature">
        </form>
    @endif
@endsection

@push('after-scripts')
    @if(config('services.stripe.active') == 1)
        <script type="text/javascript" src="{{asset('js/stripe-form.js')}}"></script>
    @endif
    <script>
        $(document).ready(function () {
            $(document).on('click', 'input[type="radio"]:checked', function () {
                $('#accordion .check-out-form').addClass('disabled')
                $(this).closest('.payment-method').find('.check-out-form').removeClass('disabled')
            })

            $(document).on('click', '#applyCoupon', function () {
                var coupon = $('#coupon');
                if (!coupon.val() || (coupon.val() == "")) {
                    coupon.addClass('warning');
                    $('#coupon-error').html("<small>{{trans('labels.frontend.cart.empty_input')}}</small>").removeClass('d-none')
                    setTimeout(function () {
                        $('#coupon-error').empty().addClass('d-none')
                        coupon.removeClass('warning');

                    }, 5000);
                } else {
                    $('#coupon-error').empty().addClass('d-none')
                    $.ajax({
                        method: 'POST',
                        url: "{{route('cart.applyCoupon')}}",
                        data: {
                            _token: '{{csrf_token()}}',
                            coupon: coupon.val()
                        }
                    }).done(function (response) {
                        if (response.status === 'fail') {
                            coupon.addClass('warning');
                            $('#coupon-error').removeClass('d-none').html("<small>" + response.message + "</small>");
                            setTimeout(function () {
                                $('#coupon-error').empty().addClass('d-none');
                                coupon.removeClass('warning');

                            }, 5000);
                        } else {
                            $('.purchase-list').empty().html(response.html)
                            $('#applyCoupon').removeClass('btn-dark').addClass('btn-success')
                            $('#coupon-error').empty().addClass('d-none');
                            coupon.removeClass('warning');
                        }
                    });

                }
            });


            $(document).on('click','#removeCoupon',function () {
                $.ajax({
                    method: 'POST',
                    url: "{{route('cart.removeCoupon')}}",
                    data: {
                        _token: '{{csrf_token()}}',
                    }
                }).done(function (response) {
                    $('.purchase-list').empty().html(response.html)
                });
            })

        })
    </script>

    @if(session()->get('razorpay'))
        @php
            $cart = session()->get('razorpay');
        @endphp

        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            var options = {
                "key": "{{ config('services.razrorpay.key') }}", // Enter the Key ID generated from the Dashboard
                "amount": "{{ $cart['amount'] }}", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
                "currency": "{{ $cart['currency'] }}",
                "name": "{{ config('app.name') }}",
                "description": "{{ $cart['description'] }}",
                "image": "{{asset("storage/logos/".config('logo_b_image'))}}",
                "order_id": "{{ $cart['order_id'] }}", //This is a sample Order ID. Pass the `id` obtained in the response of Step 1
                "handler": function (response){
                    $('#razorpay_payment_id').val(response.razorpay_payment_id);
                    $('#razorpay_order_id').val(response.razorpay_order_id);
                    $('#razorpay_signature').val(response.razorpay_signature)
                    $("#razorpay-callback-form").submit();
                },
                "prefill": {
                    "name": "{{ $cart['name'] }}",
                    "email": "{{ $cart['email'] }}",
                }
            };
            var rzp1 = new Razorpay(options);
            document.getElementById('razor-pay-btn').onclick = function(e){
                rzp1.open();
                e.preventDefault();
            }
            window.onload = function () {
                document.getElementById('razor-pay-btn').click();
            }
        </script>
    @endif
@endpush
