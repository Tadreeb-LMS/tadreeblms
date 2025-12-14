@php
    // Always safe, always array
    $contact_data = contact_data();

    // Helper for cleaner access
    $get = fn($key, $default = null) => data_get($contact_data, $key, $default);
@endphp

<section id="contact-area" class="contact-area-section backgroud-style">
    <div class="container">
        <div class="contact-area-content">
            <div class="row">

                @if(!empty($contact_data))
                    <div class="col-md-6">
                        <div class="contact-left-content">
                            <div class="section-title mb45 headline text-left">
                                <span class="subtitle ml42 text-uppercase">
                                    @lang('labels.frontend.layouts.partials.contact_us')
                                </span>
                                <h2>
                                    <span>@lang('labels.frontend.layouts.partials.get_in_touch')</span>
                                </h2>

                                <p>{{ $get('short_text.value', '') }}</p>
                            </div>

                            <div class="contact-address">

                                {{-- ADDRESS --}}
                                @if($get('primary_address.status') || $get('secondary_address.status'))
                                    <div class="contact-address-details">
                                        <div class="address-icon relative-position text-center float-left">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="address-details ul-li-block">
                                            <ul>
                                                @if($get('primary_address.status'))
                                                    <li>
                                                        <span>@lang('labels.frontend.layouts.partials.primary'):</span>
                                                        {{ $get('primary_address.value') }}
                                                    </li>
                                                @endif

                                                @if($get('secondary_address.status'))
                                                    <li>
                                                        <span>@lang('labels.frontend.layouts.partials.second'):</span>
                                                        {{ $get('secondary_address.value') }}
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                @endif

                                {{-- PHONE --}}
                                @if($get('primary_phone.status') || $get('secondary_phone.status'))
                                    <div class="contact-address-details">
                                        <div class="address-icon relative-position text-center float-left">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                        <div class="address-details ul-li-block">
                                            <ul>
                                                @if($get('primary_phone.status'))
                                                    <li>
                                                        <span>@lang('labels.frontend.layouts.partials.primary'):</span>
                                                        {{ $get('primary_phone.value') }}
                                                    </li>
                                                @endif

                                                @if($get('secondary_phone.status'))
                                                    <li>
                                                        <span>@lang('labels.frontend.layouts.partials.second'):</span>
                                                        {{ $get('secondary_phone.value') }}
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                @endif

                                {{-- EMAIL --}}
                                @if($get('primary_email.status') || $get('secondary_email.status'))
                                    <div class="contact-address-details">
                                        <div class="address-icon relative-position text-center float-left">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="address-details ul-li-block">
                                            <ul>
                                                @if($get('primary_email.status'))
                                                    <li>
                                                        <span>@lang('labels.frontend.layouts.partials.primary'):</span>
                                                        {{ $get('primary_email.value') }}
                                                    </li>
                                                @endif

                                                @if($get('secondary_email.status'))
                                                    <li>
                                                        <span>@lang('labels.frontend.layouts.partials.second'):</span>
                                                        {{ $get('secondary_email.value') }}
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="genius-btn mt60 gradient-bg text-center text-uppercase ul-li-block bold-font">
                            <a href="{{ route('contact') }}">
                                @lang('labels.frontend.layouts.partials.contact_us')
                                <i class="fas fa-caret-right"></i>
                            </a>
                        </div>
                    </div>

                    {{-- MAP --}}
                    @if($get('location_on_map.status'))
                        <div class="col-md-6">
                            <div id="contact-map" class="contact-map-section">
                                {!! $get('location_on_map.value') !!}
                            </div>
                        </div>
                    @endif
                @else
                    <h4 class="text-center w-100">
                        @lang('labels.general.no_data_available')
                    </h4>
                @endif

            </div>
        </div>
    </div>
</section>
